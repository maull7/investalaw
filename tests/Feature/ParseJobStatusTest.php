<?php

namespace Tests\Feature;

use App\Jobs\ParseRegulation;
use App\Jobs\ParseRegulationDocument;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationDocument;
use App\Models\RegulationType;
use App\Models\User;
use App\Services\RegulationParserService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
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

    public function test_corrupted_doc_sets_failed_status_and_error(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('regulation-documents/corrupted.doc', 'not a Word document');

        $document = RegulationDocument::create([
            'regulation_id' => $this->makeRegulation()->id,
            'name' => 'Corrupted DOC',
            'document_type' => 'lampiran',
            'file_path' => 'regulation-documents/corrupted.doc',
        ]);

        (new ParseRegulationDocument($document))->handle(app(RegulationParserService::class));

        $document->refresh();
        $this->assertSame('failed', $document->parse_status);
        $this->assertNull($document->parse_progress);
        $this->assertSame('Gagal membaca teks dokumen Word.', $document->parse_error);
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

    public function test_regulation_job_uses_text_extraction_before_ocr(): void
    {
        $regulation = $this->makeRegulation();
        $parser = $this->mock(RegulationParserService::class);

        $parser->shouldReceive('extractTextPages')
            ->once()
            ->withArgs(fn (Regulation $model, string $pdfType): bool => $model->is($regulation) && $pdfType === 'text')
            ->andReturn(true);
        $parser->shouldNotReceive('parseRegulationChunk');

        (new ParseRegulation($regulation))->handle($parser);
    }

    public function test_document_job_uses_text_extraction_before_ocr(): void
    {
        $document = RegulationDocument::create([
            'regulation_id' => $this->makeRegulation()->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
        ]);
        $parser = $this->mock(RegulationParserService::class);

        $parser->shouldReceive('extractTextPages')
            ->once()
            ->withArgs(fn (RegulationDocument $model, string $pdfType): bool => $model->is($document) && $pdfType === 'text')
            ->andReturn(true);
        $parser->shouldNotReceive('parseDocumentChunk');

        (new ParseRegulationDocument($document))->handle($parser);
    }

    public function test_timed_out_first_chunk_still_uses_text_extraction_on_retry(): void
    {
        $regulation = $this->makeRegulation();
        $regulation->update([
            'parse_status' => 'failed',
            'parse_stats' => [
                'total_pages' => 100,
                'chunk_size' => 10,
                'resume_page' => 1,
                'completed_pages' => 0,
            ],
        ]);
        $parser = $this->mock(RegulationParserService::class);

        $parser->shouldReceive('extractTextPages')->once()->andReturn(true);
        $parser->shouldNotReceive('parseRegulationChunk');

        (new ParseRegulation($regulation))->handle($parser);
    }

    public function test_parsing_job_timeouts_are_below_redis_retry_after(): void
    {
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
        ]);
        $retryAfter = config('queue.connections.redis.retry_after');

        $this->assertSame(1700, (new ParseRegulation($regulation))->timeout);
        $this->assertSame(1700, (new ParseRegulationDocument($document))->timeout);
        $this->assertLessThan($retryAfter, (new ParseRegulation($regulation))->timeout);
    }

    public function test_regulation_parser_processes_five_pages_per_chunk(): void
    {
        $this->assertSame(5, RegulationParserService::CHUNK_SIZE);
    }

    public function test_partial_document_ocr_is_marked_complete(): void
    {
        $document = RegulationDocument::create([
            'regulation_id' => $this->makeRegulation()->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_stats' => [
                'total_pages' => 5,
                'page_counts' => [
                    1 => 120,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
            ],
        ]);

        app(RegulationParserService::class)->finalizeOcrParsed($document);

        $document->refresh();
        $this->assertSame('complete', $document->parse_status);
        $this->assertNull($document->parse_error);
    }

    public function test_legacy_partial_document_ocr_error_is_treated_as_complete(): void
    {
        $document = RegulationDocument::create([
            'regulation_id' => $this->makeRegulation()->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'failed',
            'parse_error' => 'OCR hanya berhasil membaca 3 dari 4 halaman. Silakan unggah PDF dengan kualitas scan lebih jelas lalu parse ulang.',
            'parsed_at' => now(),
            'parsed_text' => 'partial text',
            'parse_progress' => 100,
        ]);

        $this->assertSame('complete', $document->effectiveParseStatus());
        $this->assertNull($document->effectiveParseError());
        $this->assertSame('Complete', $document->parseStatusLabel());
        $this->assertSame('emerald', $document->parseStatusBadgeColor());
    }

    public function test_parse_progress_treats_legacy_partial_document_ocr_error_as_complete(): void
    {
        $regulation = $this->makeRegulation();
        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'failed',
            'parse_error' => 'OCR hanya berhasil membaca 3 dari 4 halaman. Silakan unggah PDF dengan kualitas scan lebih jelas lalu parse ulang.',
            'parsed_at' => now(),
            'parsed_text' => 'partial text',
            'parse_progress' => 100,
        ]);

        $this->actingAs($this->admin())
            ->get(route('regulations.parse-progress', $regulation))
            ->assertJsonFragment([
                'status' => 'complete',
                'error' => null,
            ]);
    }

    public function test_parse_all_documents_skips_legacy_partial_document_ocr_error(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'failed',
            'parse_error' => 'OCR hanya berhasil membaca 3 dari 4 halaman. Silakan unggah PDF dengan kualitas scan lebih jelas lalu parse ulang.',
            'parsed_at' => now(),
            'parsed_text' => 'partial text',
            'parse_progress' => 100,
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.documents.parse-all', $regulation))
            ->assertRedirect(route('regulations.show', $regulation));

        Queue::assertNothingPushed();
    }

    public function test_parse_all_documents_includes_failed_partial_documents(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'failed',
            'parsed_at' => now(),
            'parse_error' => 'File tidak ditemukan.',
            'parsed_text' => 'partial old text',
            'parse_stats' => ['pdf_type' => 'image', 'resume_page' => null],
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.documents.parse-all', $regulation))
            ->assertRedirect(route('regulations.show', $regulation));

        $document->refresh();
        $this->assertSame('parsing', $document->parse_status);
        $this->assertNull($document->parsed_text);
        $this->assertNull($document->parse_stats);
        Queue::assertPushed(ParseRegulationDocument::class, 1);
    }

    public function test_reparse_failed_document_queues_fresh_parse_that_can_check_text_pdf_first(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'failed',
            'parse_progress' => 100,
            'parsed_at' => now(),
            'parsed_text' => 'partial old text',
            'parse_stats' => ['pdf_type' => 'image', 'resume_page' => null],
            'parse_error' => 'File tidak ditemukan.',
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.documents.parse', [$regulation, $document]))
            ->assertRedirect(route('regulations.show', $regulation));

        $document->refresh();
        $this->assertSame('parsing', $document->parse_status);
        $this->assertSame(0, $document->parse_progress);
        $this->assertNull($document->parsed_text);
        $this->assertNull($document->parse_stats);
        Queue::assertPushed(ParseRegulationDocument::class, 1);
    }

    public function test_reparse_complete_document_with_reset_queues_fresh_parse(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'complete',
            'parse_progress' => 100,
            'parsed_at' => now(),
            'parsed_text' => 'old text',
            'parse_stats' => ['total_pages' => 5],
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.documents.parse', [$regulation, $document]), ['reset' => '1'])
            ->assertRedirect(route('regulations.show', $regulation));

        $document->refresh();
        $this->assertSame('parsing', $document->parse_status);
        $this->assertSame(0, $document->parse_progress);
        $this->assertNull($document->parsed_text);
        $this->assertNull($document->parse_stats);
        Queue::assertPushed(ParseRegulationDocument::class, 1);
    }

    public function test_duplicate_regulation_job_does_not_run_parser(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $lock = Cache::lock("parse_lock:regulation:{$regulation->id}", 1760);
        $this->assertTrue($lock->get());
        $parser = $this->mock(RegulationParserService::class);
        $parser->shouldNotReceive('extractTextPages');
        $parser->shouldNotReceive('parseRegulationChunk');

        try {
            (new ParseRegulation($regulation))->handle($parser);
        } finally {
            $lock->release();
        }

        Queue::assertNothingPushed();
    }

    public function test_continuation_is_dispatched_after_regulation_lock_is_released(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $regulation->update(['parse_stats' => ['pdf_type' => 'image', 'resume_page' => 1]]);
        $parser = $this->mock(RegulationParserService::class);
        $parser->shouldReceive('parseRegulationChunk')
            ->once()
            ->andReturn(['success' => true, 'done' => false, 'next_page' => 6, 'total' => 100]);

        (new ParseRegulation($regulation))->handle($parser);

        Queue::assertPushed(ParseRegulation::class, 1);
        $lock = Cache::lock("parse_lock:regulation:{$regulation->id}", 1760);
        $this->assertTrue($lock->get());
        $lock->release();
    }

    public function test_duplicate_document_job_does_not_run_parser(): void
    {
        Queue::fake();
        $document = RegulationDocument::create([
            'regulation_id' => $this->makeRegulation()->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
        ]);
        $lock = Cache::lock("parse_lock:document:{$document->id}", 1760);
        $this->assertTrue($lock->get());
        $parser = $this->mock(RegulationParserService::class);
        $parser->shouldNotReceive('extractTextPages');
        $parser->shouldNotReceive('parseDocumentChunk');

        try {
            (new ParseRegulationDocument($document))->handle($parser);
        } finally {
            $lock->release();
        }

        Queue::assertNothingPushed();
    }

    public function test_parse_jobs_are_unique_per_model(): void
    {
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
        ]);
        $regulationJob = new ParseRegulation($regulation);
        $documentJob = new ParseRegulationDocument($document);

        $this->assertInstanceOf(ShouldBeUniqueUntilProcessing::class, $regulationJob);
        $this->assertInstanceOf(ShouldBeUniqueUntilProcessing::class, $documentJob);
        $this->assertSame((string) $regulation->id, $regulationJob->uniqueId());
        $this->assertSame((string) $document->id, $documentJob->uniqueId());
        $this->assertSame(3600, $regulationJob->uniqueFor());
        $this->assertSame(3600, $documentJob->uniqueFor());
    }

    public function test_parse_click_does_not_queue_duplicate_while_regulation_is_parsing(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $regulation->update(['parse_status' => 'parsing']);

        $this->actingAs($this->admin())
            ->post(route('regulations.parse', $regulation))
            ->assertRedirect(route('regulations.show', $regulation));

        Queue::assertNothingPushed();
    }

    public function test_parse_click_does_not_queue_duplicate_while_document_is_parsing(): void
    {
        Queue::fake();
        $regulation = $this->makeRegulation();
        $document = RegulationDocument::create([
            'regulation_id' => $regulation->id,
            'name' => 'Doc',
            'document_type' => 'lampiran',
            'file_path' => 'regulations/fixture.pdf',
            'parse_status' => 'parsing',
        ]);

        $this->actingAs($this->admin())
            ->post(route('regulations.documents.parse', [$regulation, $document]))
            ->assertRedirect(route('regulations.show', $regulation));

        Queue::assertNothingPushed();
    }
}
