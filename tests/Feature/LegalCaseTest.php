<?php

namespace Tests\Feature;

use App\Jobs\GenerateCaseAnalysis;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalCaseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function textPdf(): UploadedFile
    {
        $stream = 'BT /F1 14 Tf 72 720 Td (Ini adalah materi gugatan wanprestasi yang cukup panjang agar melebihi lima puluh karakter teks.) Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= count($objects); $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i." 0 obj\n".$objects[$i - 1]."\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        $path = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($path, $pdf);

        return new UploadedFile($path, 'gugatan.pdf', 'application/pdf', null, true);
    }

    public function test_admin_can_create_case(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('legal-cases.store'), [
                'title' => 'Gugatan Wanprestasi',
                'case_number' => '12/Pdt.G/2026/PN.JKT',
                'court' => 'PN Jakarta',
                'status_case' => 'ongoing',
                'file' => $this->textPdf(),
            ])->assertRedirect();

        $case = LegalCase::firstOrFail();
        $this->assertSame('Gugatan Wanprestasi', $case->title);
        $this->assertTrue($case->isParsed(), 'PDF harus otomatis diparse saat kasus disimpan.');
        Storage::disk('public')->assertExists($case->file_path);
    }

    public function test_non_admin_cannot_manage_cases(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('legal-cases.index'))
            ->assertForbidden();
    }

    public function test_parse_extracts_text(): void
    {
        Storage::fake('public');
        $file = $this->textPdf();

        $case = LegalCase::create([
            'user_id' => $this->admin()->id,
            'title' => 'Kasus 1',
            'status_case' => 'ongoing',
            'file_path' => Storage::disk('public')->putFile('legal-cases', $file),
        ]);

        $this->actingAs($this->admin())
            ->post(route('legal-cases.parse', $case))
            ->assertRedirect();

        $fresh = $case->fresh();
        $this->assertTrue($fresh->isParsed());
        $this->assertNotNull($fresh->parsed_text);
    }

    public function test_generate_dispatches_analysis_job(): void
    {
        Queue::fake();

        $case = LegalCase::create([
            'user_id' => $this->admin()->id,
            'title' => 'Kasus 1',
            'status_case' => 'ongoing',
            'parsed_text' => 'Materi gugatan.',
            'parsed_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->post(route('legal-cases.generate', $case))
            ->assertRedirect();

        Queue::assertPushed(GenerateCaseAnalysis::class);
        $this->assertTrue($case->isAiProcessing('analysis'));
    }

    public function test_generate_requires_parsed_document(): void
    {
        $case = LegalCase::create([
            'user_id' => $this->admin()->id,
            'title' => 'Kasus',
            'status_case' => 'ongoing',
        ]);

        $this->actingAs($this->admin())
            ->post(route('legal-cases.generate', $case))
            ->assertSessionHas('error');

        $this->assertFalse($case->isAiProcessing('analysis'));
    }
}
