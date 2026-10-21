<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AiSearchRegulationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The sector_id column comes from lawco's migrations on the shared database,
     * so it is added here for the in-memory test schema.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasColumn('regulation_categories', 'sector_id')) {
            Schema::table('regulation_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('sector_id')->nullable();
            });
        }
    }

    private function fakeAiSearch(string $json): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => $json]]],
            ]),
        ]);
    }

    private function makeRegulation(string $number, string $title, ?string $parsedText, int $sectorId = 1): Regulation
    {
        return Regulation::create([
            'regulation_number' => $number,
            'title' => $title,
            'regulation_type_id' => RegulationType::create(['name' => 'UU', 'level' => 1])->id,
            'category_id' => RegulationCategory::create(['name' => 'Kategori', 'sector_id' => $sectorId])->id,
            'year' => 2024,
            'file_path' => 'regulations/fixture.pdf',
            'parsed_text' => $parsedText,
            'parsed_at' => $parsedText ? now() : null,
        ]);
    }

    public function test_ai_search_returns_relevant_regulations_lines(): void
    {
        $matched = $this->makeRegulation('UU/8/1995', 'Pasar Modal', 'Pasal tentang sanksi emiten.');
        $this->makeRegulation('UU/13/2003', 'Ketenagakerjaan', 'Pasal tentang hubungan kerja.');

        $this->fakeAiSearch('[{"id": '.$matched->id.', "alasan": "mengatur sanksi bagi emiten"}]');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('regulations.ai-search', ['q' => 'pasar modal']))
            ->assertOk()
            ->assertSee('Hasil Pencarian AI')
            ->assertSee('mengatur sanksi bagi emiten')
            ->assertSee('UU/8/1995')
            ->assertDontSee('UU/13/2003');
    }

    public function test_ai_search_excludes_regulations_outside_sector_one(): void
    {
        $allowed = $this->makeRegulation('UU/8/1995', 'Pasar Modal', 'Pasal tentang sanksi emiten.');
        $otherSector = $this->makeRegulation('UU/40/2007', 'Perseroan Terbatas', 'Pasal tentang sanksi direksi.', 2);

        $this->fakeAiSearch('[{"id": '.$allowed->id.', "alasan": "sektor satu"}, {"id": '.$otherSector->id.', "alasan": "sektor lain"}]');

        $this->actingAs(User::factory()->create())
            ->get(route('regulations.ai-search', ['q' => 'pasar perseroan']))
            ->assertOk()
            ->assertSee('UU/8/1995')
            ->assertDontSee('UU/40/2007');

        Http::assertSent(fn ($request) => ! str_contains(json_encode($request->data()), 'UU/40/2007'));
    }

    public function test_ai_search_sends_unparsed_regulations_matching_title(): void
    {
        $unparsed = $this->makeRegulation('POJK 2 Tahun 2023', 'Penerapan Tata Kelola Manajer Investasi', null);
        $this->makeRegulation('UU/13/2003', 'Ketenagakerjaan', null);

        $this->fakeAiSearch('{"result": [{"id": '.$unparsed->id.', "alasan": "tata kelola manajer investasi"}]}');

        $this->actingAs(User::factory()->create())
            ->get(route('regulations.ai-search', ['q' => 'manajer investasi']))
            ->assertOk()
            ->assertSee('POJK 2 Tahun 2023');

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), 'Manajer Investasi')
            && ! str_contains(json_encode($request->data()), 'Ketenagakerjaan'));
    }

    public function test_ai_search_requires_login(): void
    {
        $this->get(route('regulations.ai-search', ['q' => 'sanksi emiten']))
            ->assertRedirect(route('login'));
    }

    public function test_ai_search_requires_min_three_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('regulations.ai-search', ['q' => 'ab']))
            ->assertSessionHasErrors('q');
    }

    public function test_ai_search_shows_empty_state_when_no_match(): void
    {
        $this->makeRegulation('UU/8/1995', 'Pasar Modal', 'Pasal tentang sanksi emiten.');
        $this->fakeAiSearch('[]');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('regulations.ai-search', ['q' => 'xyz abc']))
            ->assertOk()
            ->assertSee('tidak menemukan regulasi yang relevan');
    }
}
