<?php

namespace Tests\Feature;

use App\Models\AiJobStatus;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegulationExtractionStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        return ['--schema-path' => '/dev/null'];
    }

    #[DataProvider('extractionStatuses')]
    public function test_admin_sees_extraction_status(?string $status, bool $hasReferences, string $label): void
    {
        $regulation = $this->makeRegulation();
        if ($hasReferences) {
            $regulation->relatedReferences()->create(['name' => 'Peraturan terkait', 'relationship' => 'terkait']);
        }
        if ($status !== null) {
            AiJobStatus::begin($regulation, 'extract')->update(['status' => $status]);
        }

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('regulations.index', ['search_content' => 'pajak']))
            ->assertOk()
            ->assertSee('Status Ekstrak Peraturan Terkait')
            ->assertSee('Status Short Review')
            ->assertSee($label)
            ->assertSee('Belum Dibuat');
    }

    public static function extractionStatuses(): array
    {
        return [
            'not started' => [null, false, 'Belum Diekstrak'],
            'legacy references' => [null, true, 'Sudah Diekstrak'],
            'completed without references' => ['done', false, 'Sudah Diekstrak'],
            'processing' => ['processing', false, 'Sedang Diproses'],
            'failed' => ['error', false, 'Gagal'],
            'retry with existing references' => ['processing', true, 'Sedang Diproses'],
            'failed retry' => ['error', true, 'Gagal'],
        ];
    }

    #[DataProvider('shortReviews')]
    public function test_short_review_status_requires_a_short_review_result(string $title, string $result, string $label): void
    {
        $regulation = $this->makeRegulation();
        $regulation->aiResults()->create([
            'type' => 'review',
            'prompt_title' => $title,
            'prompt_text' => 'Review regulasi',
            'result' => $result,
        ]);
        AiJobStatus::begin($regulation, 'regulation-ai')->markDone();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('regulations.index'))
            ->assertOk()
            ->assertSee($label)
            ->assertSee(route('regulations.show', [$regulation, 'tab' => 'short-review']));
    }

    public static function shortReviews(): array
    {
        return [
            'short review' => ['Short Review', 'Hasil ulasan', 'Sudah Dibuat'],
            'other AI result' => ['Analisis Lain', 'Hasil analisis', 'Belum Dibuat'],
            'empty short review' => ['Short Review', '   ', 'Belum Dibuat'],
        ];
    }

    public function test_regular_user_does_not_see_admin_status_columns(): void
    {
        $this->makeRegulation();
        $this->actingAs(User::factory()->create())
            ->get(route('regulations.index'))
            ->assertOk()
            ->assertDontSee('Status Ekstrak Peraturan Terkait')
            ->assertDontSee('Status Short Review');
    }

    private function makeRegulation(): Regulation
    {
        return Regulation::create([
            'regulation_number' => 'REG/01/2026',
            'title' => 'Regulasi Pajak',
            'regulation_type_id' => RegulationType::create(['name' => 'UU', 'level' => 1])->id,
            'category_id' => RegulationCategory::create(['name' => 'Pajak'])->id,
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
            'parsed_text' => 'Ketentuan pajak.',
        ]);
    }
}
