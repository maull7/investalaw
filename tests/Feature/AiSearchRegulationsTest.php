<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSearchRegulationsTest extends TestCase
{
    use RefreshDatabase;

    private function fakeAiSearch(string $json): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => $json]]],
            ]),
        ]);
    }

    private function makeRegulation(string $number, string $title, ?string $parsedText): Regulation
    {
        return Regulation::create([
            'regulation_number' => $number,
            'title' => $title,
            'regulation_type_id' => RegulationType::create(['name' => 'UU', 'level' => 1])->id,
            'category_id' => RegulationCategory::create(['name' => 'Kategori'])->id,
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
            ->get(route('regulations.ai-search', ['q' => 'sanksi emiten']))
            ->assertOk()
            ->assertSee('Hasil Pencarian AI')
            ->assertSee('mengatur sanksi bagi emiten')
            ->assertSee('UU/8/1995')
            ->assertDontSee('UU/13/2003');
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
