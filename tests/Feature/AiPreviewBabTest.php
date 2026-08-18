<?php

namespace Tests\Feature;

use App\Models\DocumentBabAnalysis;
use App\Models\DocumentPage;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\ReviewDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiPreviewBabTest extends TestCase
{
    use RefreshDatabase;

    private function parsedDocument(): ReviewDocument
    {
        $user = User::factory()->create();
        $category = RegulationCategory::create(['name' => 'Test Category']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $document = ReviewDocument::create([
            'user_id' => $user->id,
            'title' => 'Dokumen Uji',
            'description' => 'Test',
            'file_path' => 'documents/test.pdf',
            'status' => 'submitted',
            'parsed_at' => now(),
        ]);

        DocumentPage::create([
            'review_document_id' => $document->id,
            'page_number' => 1,
            'content' => "BAB I KETENTUAN UMUM\n\nPasal 1. Ketentuan diatur berdasarkan Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal Pasal 5 dan Pasal 7 ayat (2).",
            'char_count' => 100,
        ]);

        $pembanding = Regulation::create([
            'regulation_number' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal',
            'title' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 1995,
            'file_path' => 'regulations/uu8.pdf',
            'parsed_text' => 'Pasal 5 ...\nPasal 7 ayat (2) ...',
            'parsed_at' => now(),
        ]);

        $document->regulations()->attach($pembanding->id);

        return $document;
    }

    private function fakeOpenAi(array $aiResult): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode($aiResult)],
                ]],
            ]),
        ]);
    }

    public function test_analyze_single_bab_persists_and_filters_references(): void
    {
        $document = $this->parsedDocument();

        $this->fakeOpenAi([
            'pasal_structure' => [['pasal' => 'Pasal 1', 'content' => 'Ketentuan umum', 'type' => 'existing', 'changes' => null]],
            'referenced_regulations' => [
                ['name' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal', 'number' => 'UU.8', 'year' => 1995, 'relationship' => 'dirujuk', 'pasal' => ['Pasal 5', 'Pasal 7 ayat (2)']],
                ['name' => 'Peraturan OJK Nomor 1 Tahun 2016', 'number' => 'POJK.1', 'year' => 2016, 'relationship' => 'dirujuk', 'pasal' => ['Pasal 3']],
            ],
            'insights' => 'Analisa bab ini',
            'compliance_assessment' => 'Sesuai',
            'key_findings' => ['Temuan 1'],
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->post(route('ai-preview.bab', [$document, 0]))
            ->assertOk();

        $json = $response->json();
        $this->assertSame(1, $json['ref_count']);
        $this->assertSame('Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal', $json['references'][0]['name']);
        $this->assertSame(['Pasal 5', 'Pasal 7 ayat (2)'], $json['references'][0]['pasal']);
        $this->assertCount(1, $json['references']);

        $this->assertDatabaseHas('document_bab_analyses', [
            'review_document_id' => $document->id,
            'bab_index' => 0,
            'label' => 'BAB I KETENTUAN UMUM',
        ]);
    }

    public function test_bab_list_returns_saved_analysis(): void
    {
        $document = $this->parsedDocument();

        DocumentBabAnalysis::create([
            'review_document_id' => $document->id,
            'bab_index' => 0,
            'label' => 'BAB I KETENTUAN UMUM',
            'result' => [
                'label' => 'BAB I KETENTUAN UMUM',
                'pasal' => [['pasal' => 'Pasal 1', 'content' => 'Ketentuan umum']],
                'references' => [['name' => 'Undang-Undang Nomor 8 Tahun 1995', 'pasal' => ['Pasal 5']]],
                'insights' => 'Analisa bab ini',
                'compliance_assessment' => 'Sesuai',
                'key_findings' => [],
                'pasal_count' => 1,
                'ref_count' => 1,
            ],
        ]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('ai-preview.babs-list', $document))
            ->assertOk();

        $babs = $response->json('babs');
        $this->assertCount(1, $babs);
        $this->assertArrayHasKey('result', $babs[0]);
        $this->assertSame('Sesuai', $babs[0]['result']['compliance_assessment']);
    }
}
