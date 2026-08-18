<?php

namespace App\Http\Controllers;

use App\Models\ConsultationChatMessage;
use App\Models\ConsultationGeneratedFile;
use App\Models\ConsultationSession;
use App\Models\RegulationCategory;
use App\Models\Setting;
use App\Models\TokenUsage;
use App\Models\UserPackage;
use App\Services\AiService;
use App\Services\ConsultationExportService;
use App\Services\DocumentExtractorService;
use App\Services\TokenLimitService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly AiService $aiService,
        private readonly TokenLimitService $tokenLimit,
        private readonly DocumentExtractorService $documentExtractor,
        private readonly ConsultationExportService $exportService,
    ) {}

    private function requireKakVestaAccess(): ?RedirectResponse
    {
        if (auth()->user()->isAdmin() || auth()->user()->isSubAdmin()) {
            return null;
        }

        $active = UserPackage::where('user_id', auth()->id())
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $active) {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur Konsultasi Kak Vesta hanya tersedia untuk pengguna dengan paket aktif.');
        }

        $quota = (int) ($active->package?->kak_vesta_tokens ?: 0);
        if ($quota > 0 && TokenUsage::where('user_id', auth()->id())
            ->where('source', 'consultation_chat')
            ->sum('tokens_used') >= $quota) {
            return redirect()->route('dashboard')
                ->with('error', 'Kuota token AI Kak Vesta Anda telah habis. Silakan upgrade paket atau hubungi admin.');
        }

        if ($active->type === 'paid') {
            return null;
        }

        if (! $active->kak_vesta_started_at) {
            $active->update(['kak_vesta_started_at' => now()]);

            return null;
        }

        $cap = (int) Setting::get('trial_max_hours', 48);
        $hours = (int) $active->package?->duration_hours ?: $cap;
        $allowedUntil = $active->kak_vesta_started_at->addHours(min($hours, $cap));

        if ($allowedUntil->lte(now())) {
            return redirect()->route('dashboard')
                ->with('error', 'Masa aktif trial konsultasi Kak Vesta Anda telah berakhir. Upgrade ke paket berbayar untuk melanjutkan.');
        }

        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $sessions = ConsultationSession::where('user_id', auth()->id())
            ->withCount('regulations')
            ->latest()
            ->get();

        $categories = RegulationCategory::with(['regulations' => function ($q) {
            $q->whereHas('documents');
        }])->orderBy('name')->get();

        return view('consultations.index', compact('sessions', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'regulation_ids' => ['required', 'array', 'min:1', 'max:10'],
            'regulation_ids.*' => ['integer', 'exists:regulations,id'],
        ]);

        $session = ConsultationSession::create([
            'user_id' => $request->user()->id,
            'title' => 'Konsultasi '.Carbon::now()->format('d M Y, H:i'),
        ]);

        $session->regulations()->attach($validated['regulation_ids']);

        return redirect()->route('consultations.show', $session)
            ->with('success', 'Sesi konsultasi dibuat. Silakan mulai bertanya.');
    }

    public function show(ConsultationSession $session): View|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation show 403: session_user={$session->user_id} auth_user=".auth()->id()." session_id={$session->id}");
            abort(403);
        }

        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $session->load(['messages', 'regulations']);

        $categories = RegulationCategory::with(['regulations' => function ($q) {
            $q->whereHas('documents');
        }])->orderBy('name')->get();

        $selectedIds = $session->regulations->pluck('id')->all();

        return view('consultations.show', compact('session', 'categories', 'selectedIds'));
    }

    public function ask(Request $request, ConsultationSession $session): JsonResponse|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation ask 403: session_user={$session->user_id} auth_user=".auth()->id());
            abort(403);
        }

        if ($redirect = $this->requireKakVestaAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:4000'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        $attachmentsData = [];

        if (! empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $file) {
                $attachment = $this->storeAttachment($session, $file);
                if ($attachment) {
                    $attachmentsData[] = $attachment;
                }
            }
        }

        ConsultationChatMessage::create([
            'consultation_session_id' => $session->id,
            'user_id' => $request->user()->id,
            'role' => 'user',
            'content' => $validated['question'],
            'attachments' => ! empty($attachmentsData) ? $attachmentsData : null,
        ]);

        $history = ConsultationChatMessage::where('consultation_session_id', $session->id)
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(6)
            ->get(['role', 'content', 'attachments'])
            ->reverse()
            ->map(fn ($m) => [
                'role' => $m->role,
                'content' => $m->content,
                'attachments' => $m->attachments,
            ])
            ->values()
            ->all();

        $isAdmin = auth()->user()->isAdmin() || auth()->user()->isSubAdmin();

        if (! $isAdmin && ! $this->tokenLimit->canSend($request->user()->id)) {
            $remaining = $this->tokenLimit->remaining($request->user()->id);
            $daily = $this->tokenLimit->dailyLimit();

            if ($request->wantsJson()) {
                return response()->json(['message' => "Batas token harian ({$daily}) tercapai. Tersisa {$remaining} token. Coba lagi besok."], 429);
            }

            return redirect()->route('consultations.show', $session)
                ->with('error', "Batas token harian ({$daily}) tercapai. Coba lagi besok.");
        }

        try {
            $extractedTexts = [];
            foreach ($attachmentsData as $att) {
                $text = '';

                if (! empty($att['extracted_text'])) {
                    $text = $att['extracted_text'];
                }

                if (in_array($att['type'] ?? '', ['image', 'jpg', 'jpeg', 'png']) && ! empty($att['path'])) {
                    Log::info('Calling Vision API for attachment', [
                        'type' => $att['type'],
                        'path' => $att['path'],
                        'filename' => $att['filename'],
                    ]);
                    try {
                        $userQuestion = trim($validated['question']);
                        $visionPrompt = $userQuestion
                            ? "Pertanyaan pengguna: {$userQuestion}\n\nJawab pertanyaan tersebut berdasarkan gambar ini. Jelaskan isi gambar secara detail dalam Bahasa Indonesia."
                            : 'Analisis gambar ini secara detail. Jika ada teks, diagram, flowchart, atau informasi penting, jelaskan semuanya dalam Bahasa Indonesia.';

                        $visionResult = $this->aiService->analyzeImageWithVision($att['path'], $visionPrompt);
                        $text = "[Analisis Gambar]\n".$visionResult.($text ? "\n[OCR Text]\n".$text : '');
                        Log::info('Vision API success', ['result_length' => strlen($visionResult)]);
                    } catch (\Exception $e) {
                        Log::warning('Vision API failed for attachment', ['error' => $e->getMessage()]);
                    }
                }

                if (! empty($text)) {
                    $extractedTexts[] = [
                        'filename' => $att['filename'],
                        'text' => $text,
                    ];
                }
            }

            $result = $this->aiService->askConsultation(
                $session,
                $validated['question'],
                $history,
                $request->user(),
                $extractedTexts
            );
            $reply = $result['content'];

            if (! $isAdmin) {
                $this->tokenLimit->record(
                    $request->user()->id,
                    $result['total_tokens'] ?? 0,
                    'consultation_chat',
                    $session->id
                );
            }

            $generatedFile = null;
            $imageUrl = null;
            $parsedGeneration = $this->parseGenerationRequest($reply, $session, $request->user(), $validated['question']);

            if ($parsedGeneration) {
                $reply = $parsedGeneration['message'];
                $generatedFile = $parsedGeneration['file'];
                $imageUrl = $parsedGeneration['image_url'] ?? null;
            }

            $message = ConsultationChatMessage::create([
                'consultation_session_id' => $session->id,
                'user_id' => $request->user()->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            if ($generatedFile) {
                $generatedFile->update(['chat_message_id' => $message->id]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'reply' => $reply,
                    'generated_file' => $generatedFile ? [
                        'id' => $generatedFile->id,
                        'type' => $generatedFile->type,
                        'format' => $generatedFile->format,
                        'filename' => $generatedFile->filename,
                        'url' => route('consultations.generated.download', $generatedFile),
                    ] : null,
                    'image_url' => $imageUrl,
                ]);
            }

            return redirect()->route('consultations.show', $session)
                ->with('success', 'Jawaban Kak Vesa berhasil diproses.');
        } catch (\Throwable $e) {
            Log::error('Consultation ask failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $session->id,
                'user_id' => $request->user()->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Maaf, Kak Vesta sedang tidak dapat dihubungi.'], 500);
            }

            return redirect()->route('consultations.show', $session)
                ->with('error', 'Maaf, Kak Vesta sedang tidak dapat dihubungi.');
        }
    }

    public function downloadGeneratedFile(ConsultationGeneratedFile $file): StreamedResponse|RedirectResponse
    {
        if ((int) $file->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $file->path,
            $file->filename
        );
    }

    public function viewGeneratedFile(ConsultationGeneratedFile $file)
    {
        if ((int) $file->user_id !== auth()->id()) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($file->path)) {
            abort(404);
        }

        $mime = Storage::disk('local')->mimeType($file->path);

        return response(Storage::disk('local')->get($file->path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    private function parseGenerationRequest(string $content, ConsultationSession $session, $user, string $userQuestion = ''): ?array
    {
        $decoded = $this->extractGenerationJson($content);

        if (! $decoded || ! isset($decoded['type'])) {
            return null;
        }

        $decoded['format'] = $this->resolveRequestedFormat($userQuestion, $decoded['format'] ?? null);

        try {
            if ($decoded['type'] === 'image' && isset($decoded['prompt'])) {
                Log::info('Starting image generation', ['prompt' => mb_substr($decoded['prompt'], 0, 200)]);

                $imageResult = $this->aiService->generateImage($decoded['prompt'], $user->id);

                Log::info('Image generation result', [
                    'has_local_path' => ! empty($imageResult['local_path']),
                    'has_url' => ! empty($imageResult['url']),
                    'file_size' => $imageResult['file_size'] ?? null,
                ]);

                $localPath = $imageResult['local_path'] ?? null;
                $imageUrl = $imageResult['url'] ?? null;

                if (! $localPath && ! empty($imageUrl)) {
                    $localPath = $this->aiService->generateImageFromUrl($imageUrl, $user->id);
                }

                if (! $localPath && ! $imageUrl) {
                    throw new \Exception('Image generation did not produce a usable file or URL');
                }

                $imgDesc = $decoded['description'] ?? 'Gambar';
                $message = "Berikut gambar yang saya hasilkan:\n\n{$imgDesc}";
                $generatedFile = null;

                if ($localPath) {
                    $fileSize = Storage::disk('local')->size($localPath);

                    $generatedFile = ConsultationGeneratedFile::create([
                        'consultation_session_id' => $session->id,
                        'user_id' => $user->id,
                        'type' => ConsultationGeneratedFile::TYPE_IMAGE,
                        'format' => ConsultationGeneratedFile::FORMAT_PNG,
                        'filename' => str_ends_with(mb_strtolower($imgDesc), '.png') ? $imgDesc : $imgDesc.'.png',
                        'path' => $localPath,
                        'original_prompt' => mb_substr($decoded['prompt'], 0, 1000),
                        'file_size' => $fileSize,
                        'ai_response' => mb_substr($content, 0, 5000),
                    ]);
                }

                $viewUrl = $generatedFile ? route('consultations.generated.view', $generatedFile) : $imageUrl;

                return [
                    'message' => $message,
                    'file' => $generatedFile,
                    'image_url' => $viewUrl,
                ];
            }

            if ($decoded['type'] === 'document' && isset($decoded['content'])) {
                Log::info('Starting document generation', ['title' => $decoded['title'] ?? 'Untitled']);
                $format = in_array($decoded['format'] ?? '', ['docx', 'xlsx', 'pdf']) ? $decoded['format'] : 'pdf';
                $title = $decoded['title'] ?? 'Generated Document';

                $contentForExport = $decoded['content'];
                $session->load(['user', 'regulations']);

                $format = match ($format) {
                    'docx' => 'docx',
                    'xlsx' => 'xlsx',
                    default => 'pdf',
                };

                if ($format === 'docx') {
                    $filename = $title.'.docx';
                    $tempFile = sys_get_temp_dir().'/'.$filename;
                    $phpWord = new PhpWord;
                    $section = $phpWord->addSection();
                    $section->addText($contentForExport, ['name' => 'Calibri', 'size' => 11]);
                    $writer = IOFactory::createWriter($phpWord, 'Word2007');
                    $writer->save($tempFile);
                    $fileContent = file_get_contents($tempFile);
                    unlink($tempFile);
                } elseif ($format === 'xlsx') {
                    $filename = $title.'.xlsx';
                    $tempFile = sys_get_temp_dir().'/'.$filename;
                    $spreadsheet = $this->buildExcelFromContent($title, $contentForExport);
                    $writer = new XlsxWriter($spreadsheet);
                    $writer->save($tempFile);
                    $fileContent = file_get_contents($tempFile);
                    unlink($tempFile);
                } else {
                    $filename = $title.'.pdf';
                    $html = $this->buildDocumentHtml($title, $contentForExport, $session->user);
                    $pdf = Pdf::loadHTML($html);
                    $fileContent = $pdf->output();
                }

                $path = 'consultation_generated/'.$user->id.'/'.$filename;
                Storage::disk('local')->put($path, $fileContent);

                $generatedFile = ConsultationGeneratedFile::create([
                    'consultation_session_id' => $session->id,
                    'user_id' => $user->id,
                    'type' => ConsultationGeneratedFile::TYPE_DOCUMENT,
                    'format' => match ($format) {
                        'docx' => ConsultationGeneratedFile::FORMAT_DOCX,
                        'xlsx' => ConsultationGeneratedFile::FORMAT_XLSX,
                        default => ConsultationGeneratedFile::FORMAT_PDF,
                    },
                    'filename' => $filename,
                    'path' => $path,
                    'original_prompt' => mb_substr($title, 0, 1000),
                    'file_size' => strlen($fileContent),
                    'ai_response' => mb_substr($content, 0, 5000),
                ]);

                $docTitle = $decoded['title'] ?? 'Generated Document';
                $message = "Dokumen {$docTitle} berhasil dibuat!\n\nFormat: ".strtoupper($format)."\n\nSilakan download menggunakan tombol di bawah ini.";

                return [
                    'message' => $message,
                    'file' => $generatedFile,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate content', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    private function extractGenerationJson(string $content): ?array
    {
        $clean = preg_replace('/```json\s*/i', '', $content);
        $clean = preg_replace('/```\s*$/i', '', $clean);
        $clean = trim($clean);

        $decoded = json_decode($clean, true);

        if (is_array($decoded) && isset($decoded['type'])) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*?"type"\s*:\s*"(image|document)"[\s\S]*?\}/', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded) && isset($decoded['type'])) {
                return $decoded;
            }
        }

        Log::warning('Could not extract generation JSON from AI response', ['content_preview' => mb_substr($content, 0, 500)]);

        return null;
    }

    private function resolveRequestedFormat(string $question, ?string $aiFormat): string
    {
        $lower = mb_strtolower($question);

        if (str_contains($lower, 'excel') || str_contains($lower, 'xlsx')) {
            return 'xlsx';
        }

        if (str_contains($lower, 'word') || str_contains($lower, 'docx')) {
            return 'docx';
        }

        if (str_contains($lower, 'pdf')) {
            return 'pdf';
        }

        return in_array($aiFormat, ['pdf', 'docx', 'xlsx']) ? $aiFormat : 'pdf';
    }

    private function buildDocumentHtml(string $title, string $content, $user): string
    {
        $date = Carbon::now()->format('d M Y');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #071833; line-height: 1.5; margin: 40px; }
        .header { border-bottom: 2px solid #c99a3e; padding-bottom: 15px; margin-bottom: 25px; }
        .brand { font-size: 18px; font-weight: bold; color: #071833; }
        .brand span { color: #c99a3e; }
        .title { font-size: 20px; font-weight: bold; margin: 20px 0 10px; }
        .meta { font-size: 10px; color: #667085; }
        .content { white-space: pre-wrap; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e7eaf0; font-size: 9px; color: #667085; text-align: center; }
        .footer span { color: #c99a3e; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Investa<span>Law</span>Co</div>
    </div>
    <div class="title">{$title}</div>
    <div class="meta">Generated: {$date} | User: {$user->name}</div>
    <div class="content">{$content}</div>
    <div class="footer"><span>InvestaLawCo</span> — Legal · Strategic · Trusted</div>
</body>
</html>
HTML;
    }

    private function buildExcelFromContent(string $title, string $content): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $rows = preg_split('/\r\n|\r|\n/', $content);
        $rowIndex = 3;

        foreach ($rows as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cells = preg_split('/\t/', $line);
            $colIndex = 1;

            foreach ($cells as $cell) {
                $sheet->setCellValue([$colIndex, $rowIndex], trim($cell));
                $colIndex++;
            }

            $rowIndex++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function downloadAttachment(ConsultationSession $session, ConsultationChatMessage $message, int $index): StreamedResponse|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $message->consultation_session_id !== $session->id) {
            abort(404);
        }

        $attachments = $message->attachments ?? [];

        if (! isset($attachments[$index])) {
            abort(404);
        }

        $attachment = $attachments[$index];
        $path = $attachment['path'];

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $path,
            $attachment['filename']
        );
    }

    public function exportSessionPdf(ConsultationSession $session): Response|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            abort(403);
        }

        $session->load(['messages', 'regulations', 'user']);

        $filename = 'Konsultasi_Kak_Vesta_'.Carbon::now()->format('Y-m-d_Hi').'.pdf';
        $pdfContent = $this->exportService->exportSessionPdf($session);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportSessionWord(ConsultationSession $session): Response|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            abort(403);
        }

        $session->load(['messages', 'regulations', 'user']);

        $filename = 'Konsultasi_Kak_Vesta_'.Carbon::now()->format('Y-m-d_Hi').'.docx';
        $wordContent = $this->exportService->exportSessionWord($session);

        return response($wordContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportMessagePdf(ConsultationSession $session, ConsultationChatMessage $message): Response|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $message->consultation_session_id !== $session->id) {
            abort(404);
        }

        if ($message->role !== 'assistant') {
            abort(400);
        }

        $session->load(['user']);
        $message->load('session');

        $filename = 'Konsultasi_Kak_Vesta_respon_'.$message->id.'.pdf';
        $pdfContent = $this->exportService->exportMessagePdf($message);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportMessageWord(ConsultationSession $session, ConsultationChatMessage $message): Response|RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            abort(403);
        }

        if ((int) $message->consultation_session_id !== $session->id) {
            abort(404);
        }

        if ($message->role !== 'assistant') {
            abort(400);
        }

        $session->load(['user']);
        $message->load('session');

        $filename = 'Konsultasi_Kak_Vesta_respon_'.$message->id.'.docx';
        $wordContent = $this->exportService->exportMessageWord($message);

        return response($wordContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function storeAttachment(ConsultationSession $session, $file): ?array
    {
        try {
            $filename = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $uniqueName = md5($session->id.'_'.time().'_'.rand(1, 9999)).'.'.$extension;
            $path = 'consultation_attachments/'.$session->id.'/'.$uniqueName;

            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

            $extractedText = $this->documentExtractor->extractText($file);

            return [
                'filename' => $filename,
                'path' => $path,
                'type' => $this->documentExtractor->getFileType($file),
                'size' => $file->getSize(),
                'extracted_text' => $extractedText,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to store consultation attachment: {$e->getMessage()}");

            return null;
        }
    }

    public function addRegulations(Request $request, ConsultationSession $session): RedirectResponse
    {
        if ((int) $session->user_id !== auth()->id()) {
            Log::warning("Consultation addReg 403: session_user={$session->user_id} auth_user=".auth()->id());
            abort(403);
        }

        $currentCount = $session->regulations()->count();

        $validated = $request->validate([
            'regulation_ids' => ['required', 'array', 'min:1'],
            'regulation_ids.*' => ['integer', 'exists:regulations,id'],
        ]);

        $newTotal = $currentCount + count($validated['regulation_ids']);

        if ($newTotal > 10) {
            return redirect()->route('consultations.show', $session)
                ->with('error', "Maksimal 10 regulasi per sesi. Saat ini sudah ada {$currentCount} regulasi.");
        }

        $session->regulations()->syncWithoutDetaching($validated['regulation_ids']);

        return redirect()->route('consultations.show', $session)
            ->with('success', 'Regulasi berhasil ditambahkan ke sesi konsultasi.');
    }
}
