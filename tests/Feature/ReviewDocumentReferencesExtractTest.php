<?php

namespace Tests\Feature;

use App\Models\DocumentPage;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\ReviewDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewDocumentReferencesExtractTest extends TestCase
{
    use RefreshDatabase;

    public function test_extract_requires_parsed_document(): void
    {
        $document = $this->makeDocument(parsed: false);

        $this->actingAs($this->admin())
            ->post(route('partitions.extract-regulations', $document))
            ->assertRedirect(route('partitions.index', $document))
            ->assertSessionHas('info');

        $this->assertSame(0, $document->relatedReferences()->count());
    }

    public function test_extract_stores_and_renders_related_regulations(): void
    {
        $this->fakeAi([
            ['name' => 'Peraturan OJK Nomor 3 Tahun 2015', 'number' => '3/POJK.05/2015', 'year' => 2015, 'relationship' => 'dirujuk'],
            ['name' => 'Peraturan OJK Nomor 29 Tahun 2018', 'number' => '29/POJK.05/2018', 'year' => 2018, 'relationship' => 'dicabut'],
        ]);

        $document = $this->makeDocument(parsed: true);

        $this->actingAs($this->admin())
            ->post(route('partitions.extract-regulations', $document))
            ->assertRedirect(route('partitions.index', $document))
            ->assertSessionHas('info');

        $this->assertSame(2, $document->relatedReferences()->count());
        $this->assertSame(1, $document->relatedReferences()->where('relationship', 'dicabut')->count());

        $this->get(route('partitions.index', $document))
            ->assertOk()
            ->assertSee('Regulasi Terkait')
            ->assertSee('Tarik Regulasi')
            ->assertSee('Peraturan OJK Nomor 3 Tahun 2015')
            ->assertSee('Peraturan OJK Nomor 29 Tahun 2018');
    }

    public function test_re_extract_replaces_not_duplicates(): void
    {
        $this->fakeAi([
            ['name' => 'POJK X', 'number' => '1', 'year' => 2020, 'relationship' => 'dirujuk'],
        ]);

        $document = $this->makeDocument(parsed: true);

        $this->actingAs($this->admin())->post(route('partitions.extract-regulations', $document));
        $this->actingAs($this->admin())->post(route('partitions.extract-regulations', $document));

        $this->assertSame(1, $document->relatedReferences()->count());
    }

    public function test_regulasi_terkait_shows_dokumentasi_ada_and_belum_ada(): void
    {
        $category = RegulationCategory::create(['name' => 'Pasar Modal']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $catalog = Regulation::create([
            'regulation_number' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal',
            'title' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 1995,
            'file_path' => 'regulations/uu8.pdf',
        ]);

        $document = $this->makeDocument(parsed: true);
        $document->relatedReferences()->createMany([
            ['name' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal', 'number' => 'UU.8', 'year' => 1995, 'relationship' => 'dirujuk'],
            ['name' => 'Peraturan OJK Nomor 3 Tahun 2015', 'number' => '3/POJK.05/2015', 'year' => 2015, 'relationship' => 'dirujuk'],
        ]);

        $this->actingAs($this->admin())
            ->get(route('partitions.index', $document))
            ->assertOk()
            ->assertSee('Dokumentasi')
            ->assertSee('/regulations/'.$catalog->id, false)
            ->assertSee('Belum Ada');
    }

    private function fakeAi(array $references): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode(['referenced_regulations' => $references])]],
                ],
            ], 200),
        ]);
    }

    private function makeDocument(bool $parsed): ReviewDocument
    {
        $document = ReviewDocument::create([
            'user_id' => $this->admin()->id,
            'title' => 'Dokumen Test',
            'file_path' => 'review-documents/fixture.pdf',
            'parsed_at' => $parsed ? now() : null,
        ]);

        if ($parsed) {
            DocumentPage::create([
                'review_document_id' => $document->id,
                'page_number' => 1,
                'content' => 'Konten dokumen yang sudah diparse.',
                'char_count' => 33,
            ]);
        }

        return $document;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
