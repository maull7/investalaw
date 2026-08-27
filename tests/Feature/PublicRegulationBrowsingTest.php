<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationDocument;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRegulationBrowsingTest extends TestCase
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        return ['--schema-path' => '/dev/null'];
    }

    public function test_landing_search_returns_matching_regulations(): void
    {
        $match = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');
        $other = $this->makeRegulation('UU/1/2020', 'Ketenagakerjaan');

        $this->get(route('index-dash', ['search' => 'digital']))
            ->assertOk()
            ->assertSee('Hasil Pencarian Regulasi')
            ->assertSee($match->regulation_number)
            ->assertDontSee($other->regulation_number);
    }

    public function test_landing_category_and_year_filters_return_matching_regulations(): void
    {
        $match = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital', 'Pasar Modal', 2026);
        $other = $this->makeRegulation('POJK/11/2025', 'Pasar Modal Lama', 'Pasar Modal', 2025);

        $this->get(route('index-dash', [
            'category_id' => $match->category_id,
            'year' => $match->year,
        ]))
            ->assertOk()
            ->assertSee('Hasil Pencarian Regulasi')
            ->assertSee($match->regulation_number)
            ->assertDontSee($other->regulation_number);
    }

    public function test_guest_can_open_regulation_detail_but_must_login_to_use_kak_vesta(): void
    {
        $regulation = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');

        $this->get(route('regulations.show', $regulation))
            ->assertOk()
            ->assertSee('Info')
            ->assertSee('Short Review')
            ->assertSee('Tanya Kak Vesta')
            ->assertSee('Silakan login terlebih dahulu untuk menggunakan fitur Tanya Kak Vesta.')
            ->assertSee(route('login'));
    }

    public function test_authenticated_user_can_open_regulation_detail(): void
    {
        $regulation = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');

        $this->actingAs(User::factory()->create())
            ->get(route('regulations.show', $regulation))
            ->assertOk()
            ->assertSee('Info')
            ->assertSee('Short Review')
            ->assertSee('Tanya Kak Vesta');
    }

    public function test_admin_sees_parse_all_documents_button_on_regulation_detail(): void
    {
        $regulation = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');
        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Lampiran',
            'document_type' => 'lampiran',
            'file_path' => 'regulation-documents/lampiran.pdf',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('regulations.show', $regulation))
            ->assertOk()
            ->assertSee('Parse Semua Dokumen');
    }

    public function test_all_regulations_stay_on_landing_page_and_are_paginated(): void
    {
        for ($day = 1; $day <= 16; $day++) {
            $this->makeRegulation(
                "REG/{$day}/2026",
                "Regulasi {$day}",
                date: sprintf('2026-01-%02d', $day),
            );
        }

        $this->get(route('index-dash'))
            ->assertOk()
            ->assertSee('REG/16/2026')
            ->assertDontSee('REG/11/2026');

        $this->get(route('index-dash', ['all' => 1]))
            ->assertOk()
            ->assertSee('Semua Regulasi')
            ->assertSee('REG/16/2026')
            ->assertDontSee('REG/1/2026')
            ->assertSee('page=2');

        $this->get(route('index-dash', ['all' => 1, 'page' => 2]))
            ->assertOk()
            ->assertSee('REG/1/2026')
            ->assertDontSee('REG/16/2026');
    }

    public function test_submitting_empty_search_form_returns_default_latest_regulations(): void
    {
        $regulation = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');

        $this->get(route('index-dash', [
            'search' => '',
            'category_id' => '',
            'year' => '',
        ]))
            ->assertOk()
            ->assertSee('Peraturan Terkini')
            ->assertSee($regulation->regulation_number);
    }

    public function test_guest_chat_request_is_redirected_to_login(): void
    {
        $regulation = $this->makeRegulation('POJK/10/2026', 'Pasar Modal Digital');

        $this->post(route('regulations.chat.ask', $regulation), [
            'question' => 'Apa isi regulasi ini?',
        ])->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_regulation_create(): void
    {
        $this->get(route('regulations.create'))
            ->assertRedirect(route('login'));
    }

    private function makeRegulation(
        string $number,
        string $title,
        string $categoryName = 'Pasar Modal',
        int $year = 2026,
        ?string $date = null,
    ): Regulation {
        $type = RegulationType::create(['name' => 'POJK', 'level' => 1]);
        $category = RegulationCategory::create(['name' => $categoryName]);

        return Regulation::create([
            'regulation_number' => $number,
            'title' => $title,
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => $year,
            'file_path' => 'regulations/fixture.pdf',
            'tanggal_tetapkan' => $date,
        ]);
    }
}
