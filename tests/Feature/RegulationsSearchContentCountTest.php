<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationDocument;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegulationsSearchContentCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_count_occurrences_in_matching_documents(): void
    {
        $regulation = $this->makeRegulation('Kewajiban pajak diatur dalam bab pajak. Kewajiban wajib dipatuhi.');
        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'lampiran',
            'document_type' => 'lampiran',
            'file_path' => 'regulation-documents/x.pdf',
            'parsed_text' => 'Ketentuan kewajiban dan sanksi kewajiban. Tidak ada kata kunci di sini.',
            'parsed_at' => now(),
        ]);
        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'tidak cocok',
            'document_type' => 'lampiran',
            'file_path' => 'regulation-documents/y.pdf',
            'parsed_text' => 'Dokumen tanpa kata kunci sama sekali.',
            'parsed_at' => now(),
        ]);

        $this->actingAs($this->user())->get(route('regulations.index', ['search_content' => 'kewajiban']))
            ->assertOk()
            ->assertSee('Jumlah Temuan')
            ->assertSee('4');

        $this->assertSame(4, $regulation->fresh()->searchOccurrenceCount('kewajiban'));
    }

    public function test_column_hidden_without_content_filter(): void
    {
        $this->makeRegulation('Kewajiban di sini.');
        $this->actingAs($this->user())->get(route('regulations.index'))
            ->assertOk()
            ->assertDontSee('Jumlah Temuan');
    }

    public function test_column_hidden_when_no_document_matches(): void
    {
        $this->makeRegulation('Tidak mengandung kata kunci.');

        $this->actingAs($this->user())->get(route('regulations.index', ['search_content' => 'kewajiban']))
            ->assertOk()
            ->assertDontSee('Jumlah Temuan');
    }

    public function test_quoted_term_matches_word_not_substring(): void
    {
        $this->makeRegulation('Bank wajib menyusun laporan secara berkala. Perbankan dilarang bertindak tanpa izin.');

        $this->actingAs($this->user())->get(route('regulations.index', ['search_content' => '"Bank"']))
            ->assertOk()
            ->assertSee('Jumlah Temuan')
            ->assertSee('1');
    }

    public function test_quoted_phrase_matches_whole_words_in_sequence(): void
    {
        $this->makeRegulation('Produk reksa dana wajib didaftarkan ke OJK.', 'Regulasi Cocok');
        Regulation::create([
            'regulation_number' => 'POJK/02/2026',
            'title' => 'Regulasi Tidak Cocok',
            'regulation_type_id' => RegulationType::create(['name' => 'POJK', 'level' => 1])->id,
            'category_id' => RegulationCategory::create(['name' => 'Kontrak Investasi Kolektif'])->id,
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
            'parsed_text' => 'Produk reksa danai wajib diawasi.',
            'parsed_at' => now(),
        ]);

        $this->actingAs($this->user())->get(route('regulations.index', ['search_content' => '"reksa dana"']))
            ->assertOk()
            ->assertSee('Regulasi Cocok')
            ->assertDontSee('Regulasi Tidak Cocok');
    }

    private function makeRegulation(string $parsedText, string $title = 'Regulasi Test'): Regulation
    {
        $type = RegulationType::create(['name' => 'POJK', 'level' => 1]);
        $category = RegulationCategory::create(['name' => 'Kontrak Investasi Kolektif']);

        return Regulation::create([
            'regulation_number' => 'POJK/01/2026',
            'title' => $title,
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
            'parsed_text' => $parsedText,
            'parsed_at' => now(),
        ]);
    }

    private function user(): User
    {
        return User::factory()->create();
    }
}
