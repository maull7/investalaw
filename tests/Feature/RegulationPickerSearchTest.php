<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationDocument;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegulationPickerSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_picker_renders_normalized_search_key_with_year(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $category = RegulationCategory::create(['name' => 'Pasar Modal']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $regulation = Regulation::create([
            'regulation_number' => 'Undang-Undang Nomor 8 Tahun 1995 tentang Pasar Modal',
            'title' => 'Pasar Modal',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 1995,
            'file_path' => 'regulations/uu8.pdf',
        ]);

        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'UU-8.pdf',
            'document_type' => 'pdf',
            'file_path' => 'regulation-documents/uu8.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('consultations.index'))
            ->assertOk()
            ->assertSee('data-search="undangundangnomor8tahun1995tentangpasarmodalpasarmodal1995"', false)
            ->assertSee('Pasar Modal');
    }
}
