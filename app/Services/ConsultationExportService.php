<?php

namespace App\Services;

use App\Models\ConsultationChatMessage;
use App\Models\ConsultationSession;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\Element\Paragraph;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class ConsultationExportService
{
    private const WORD_FONT = 'Calibri';

    private const WORD_FONT_SIZE = 11;

    public function exportSessionPdf(ConsultationSession $session): string
    {
        $data = $this->prepareSessionData($session);

        $pdf = Pdf::loadView('consultations.exports.pdf', $data);

        return $pdf->output();
    }

    public function exportSessionWord(ConsultationSession $session): string
    {
        $data = $this->prepareSessionData($session);
        $phpWord = $this->buildWordDocument($data);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        $tempFile = tempnam(sys_get_temp_dir(), 'consultation_export_').'.docx';
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    public function exportMessagePdf(ConsultationChatMessage $message): string
    {
        $data = $this->prepareMessageData($message);

        $pdf = Pdf::loadView('consultations.exports.pdf', $data);

        return $pdf->output();
    }

    public function exportMessageWord(ConsultationChatMessage $message): string
    {
        $data = $this->prepareMessageData($message);
        $phpWord = $this->buildWordDocument($data);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        $tempFile = tempnam(sys_get_temp_dir(), 'consultation_export_').'.docx';
        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    /** @return array<string, mixed> */
    private function prepareSessionData(ConsultationSession $session): array
    {
        $session->load(['messages', 'regulations', 'user']);

        $regulationsList = $session->regulations->map(fn ($r) => "{$r->regulation_number} - {$r->title}")->toArray();

        $messagesData = $session->messages->map(function ($msg) {
            $attachments = '';
            if (! empty($msg->attachments)) {
                $files = array_map(fn ($a) => $a['filename'], $msg->attachments);
                $attachments = "\n[Lampiran: ".implode(', ', $files).']';
            }

            return [
                'role' => $msg->role,
                'content' => $this->contentWithCitations($msg->content, $msg->citations).$attachments,
                'created_at' => $msg->created_at->format('d M Y, H:i'),
            ];
        })->toArray();

        return [
            'session' => $session,
            'regulationsList' => $regulationsList,
            'messagesData' => $messagesData,
            'user' => $session->user,
            'isSingleMessage' => false,
        ];
    }

    /** @return array<string, mixed> */
    private function prepareMessageData(ConsultationChatMessage $message): array
    {
        $message->load('session.user');

        $attachments = '';
        if (! empty($message->attachments)) {
            $files = array_map(fn ($a) => $a['filename'], $message->attachments);
            $attachments = "\n[Lampiran: ".implode(', ', $files).']';
        }

        return [
            'session' => $message->session,
            'regulationsList' => $message->session->regulations->map(fn ($r) => "{$r->regulation_number} - {$r->title}")->toArray(),
            'messagesData' => [
                [
                    'role' => 'assistant',
                    'content' => $this->contentWithCitations($message->content, $message->citations).$attachments,
                    'created_at' => $message->created_at->format('d M Y, H:i'),
                ],
            ],
            'user' => $message->session->user,
            'isSingleMessage' => true,
        ];
    }

    /** @param array<string, mixed> $data */
    private function buildWordDocument(array $data): PhpWord
    {
        $phpWord = new PhpWord;

        $section = $phpWord->addSection();

        $this->addWordHeader($section, $data['session']);

        $section->addText('', ['size' => 12]);

        if (! empty($data['regulationsList'])) {
            $regParagraph = new Paragraph;
            $regParagraph->addText('Regulasi:', ['bold' => true, 'size' => self::WORD_FONT_SIZE, 'name' => self::WORD_FONT]);
            $section->addText($regParagraph);

            foreach ($data['regulationsList'] as $reg) {
                $section->addListItem(' '.$reg, 0, ['name' => self::WORD_FONT, 'size' => self::WORD_FONT_SIZE]);
            }
            $section->addText('', ['size' => 12]);
        }

        foreach ($data['messagesData'] as $msg) {
            $isUser = $msg['role'] === 'user';
            $roleLabel = $isUser ? 'Anda' : 'Kak Vesta';
            $roleStyle = $isUser ? ['bold' => true, 'color' => '071833', 'name' => self::WORD_FONT, 'size' => self::WORD_FONT_SIZE] : ['bold' => true, 'color' => 'c99a3e', 'name' => self::WORD_FONT, 'size' => self::WORD_FONT_SIZE];

            $section->addText($roleLabel.' - '.$msg['created_at'], $roleStyle);
            $section->addText($msg['content'], ['name' => self::WORD_FONT, 'size' => self::WORD_FONT_SIZE]);
            $section->addText('', ['size' => 12]);
        }

        $this->addWordFooter($section);

        return $phpWord;
    }

    private function contentWithCitations(string $content, ?array $citations): string
    {
        if (empty($citations)) {
            return $content;
        }

        $sources = collect($citations)->map(function (array $citation): string {
            $label = $citation['source_label'] ?? 'Sumber regulasi';
            $page = ! empty($citation['page']) ? " (halaman {$citation['page']})" : '';
            $quote = ! empty($citation['quote']) ? "\n\"{$citation['quote']}\"" : '';

            return "- {$label}{$page}{$quote}";
        })->implode("\n");

        return $content."\n\nSumber:\n{$sources}";
    }

    private function addWordHeader($section, ConsultationSession $session): void
    {
        $headerTable = new Table(['borderSize' => 0, 'borderColor' => 'ffffff']);
        $headerTable->addRow();
        $cell1 = $headerTable->addCell(5000);
        $cell1->addText('InvestaLawCo', ['bold' => true, 'color' => '071833', 'size' => 16, 'name' => self::WORD_FONT]);
        $cell1->addText('Legal · Strategic · Trusted', ['color' => 'c99a3e', 'size' => 9, 'name' => self::WORD_FONT]);
        $cell2 = $headerTable->addCell(5000, ['align' => 'right']);
        $cell2->addText('Konsultasi Kak Vesta', ['bold' => true, 'color' => '071833', 'size' => 12, 'name' => self::WORD_FONT]);
        $cell2->addText($session->title, ['color' => '667085', 'size' => 9, 'name' => self::WORD_FONT]);

        $section->addElement($headerTable);

        $borderPara = new Paragraph;
        $borderPara->addBorder('bottom', ['style' => 'single', 'size' => 12, 'color' => 'c99a3e']);
        $section->addElement($borderPara);

        $section->addText('', ['size' => 12]);
    }

    private function addWordFooter($section): void
    {
        $section->addText('', ['size' => 12]);

        $footerTable = new Table(['borderSize' => 0, 'borderColor' => 'ffffff']);
        $footerTable->addRow();
        $footerTable->addCell(10000)->addText('InvestaLawCo - Legal · Strategic · Trusted', ['color' => '667085', 'size' => 8, 'name' => self::WORD_FONT]);
        $footerTable->addCell(10000, ['align' => 'right'])->addText('Generated: '.date('d M Y, H:i'), ['color' => '667085', 'size' => 8, 'name' => self::WORD_FONT]);

        $section->addElement($footerTable);
    }
}
