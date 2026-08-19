<?php

namespace Tests\Feature;

use App\Jobs\ParseRegulationDocument;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationDocument;
use App\Models\RegulationType;
use App\Models\User;
use App\Services\RegulationParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ParseJobStatusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeRegulation(): Regulation
    {
        $type = RegulationType::create(['name' => 'POJK', 'level' => 1]);
        $category = RegulationCategory::create(['name' => 'Kategori']);

        return Regulation::create([
            'regulation_number' => 'POJK/01/2026',
            'title' => 'Regulasi Test',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
        ]);
    }

    public function test_document_parse_failure_sets_failed_status_and_error(): void
    {
        $reg = $this->makeRegulation();
        $doc = RegulationDocument::create([
            'regulation_id' => $reg->id,

            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/not-exist.pdf',
        ]);

        (new ParseRegulationDocument($doc))->handle(app(RegulationParserService::class));

        $fresh = $doc->fresh();
        $this->assertSame('failed', $fresh->parse_status);
        $this->assertStringContainsString('File tidak ditemukan', $fresh->parse_error);
    }

    public function test_document_failed_callback_records_error(): void
    {
        $reg = $this->makeRegulation();
        $doc = RegulationDocument::create([
            'regulation_id' => $reg->id,

            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
        ]);

        (new ParseRegulationDocument($doc))->failed(new \Exception('Boom error'));

        $fresh = $doc->fresh();
        $this->assertSame('failed', $fresh->parse_status);
        $this->assertSame('Boom error', $fresh->parse_error);
    }

    public function test_cancel_flag_marks_incomplete_and_keeps_resume(): void
    {
        $reg = $this->makeRegulation();
        $doc = RegulationDocument::create([
            'regulation_id' => $reg->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_stats' => ['resume_page' => 12, 'pdf_type' => 'image'],
        ]);

        Cache::put("parse_cancel:document:{$doc->id}", true, now()->addHour());

        (new ParseRegulationDocument($doc))->handle(app(RegulationParserService::class));

        $this->assertSame('incomplete', $doc->fresh()->parse_status);
        $this->assertFalse(Cache::has("parse_cancel:document:{$doc->id}"));
    }

    public function test_cancel_endpoint_resets_status_and_sets_flags(): void
    {
        $reg = $this->makeRegulation();
        $reg->update(['parse_status' => 'parsing', 'parse_progress' => 40]);
        $doc = RegulationDocument::create([
            'regulation_id' => $reg->id,

            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'parsing',
            'parse_progress' => 20,
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.parse-cancel', $reg))
            ->assertRedirect(route('regulations.show', $reg));

        $this->assertSame('incomplete', $reg->fresh()->parse_status);
        $this->assertSame('incomplete', $doc->fresh()->parse_status);
        $this->assertTrue(Cache::get("parse_cancel:regulation:{$reg->id}"));
        $this->assertTrue(Cache::get("parse_cancel:document:{$doc->id}"));
    }

    public function test_cancel_requires_permission(): void
    {
        $reg = $this->makeRegulation();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('regulations.parse-cancel', $reg))
            ->assertForbidden();
    }

    public function test_parse_progress_returns_error(): void
    {
        $reg = $this->makeRegulation();
        $reg->update(['parse_status' => 'failed', 'parse_error' => 'Gagal diparse']);

        $this->actingAs($this->admin())
            ->get(route('regulations.parse-progress', $reg))
            ->assertJsonFragment(['status' => 'failed', 'error' => 'Gagal diparse']);
    }
}
