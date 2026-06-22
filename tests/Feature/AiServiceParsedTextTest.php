<?php

namespace Tests\Feature;

use App\Models\DocumentParsedText;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\ReviewDocument;
use App\Models\User;
use App\Services\AiService;
use App\Services\DocumentParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiServiceParsedTextTest extends TestCase
{
    use RefreshDatabase;

    private AiService $aiService;

    private DocumentParser $mockParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockParser = Mockery::mock(DocumentParser::class);
        $this->aiService = new AiService($this->mockParser);
    }

    public function test_save_parsed_texts_stores_regulation_text_to_database(): void
    {
        $user = User::factory()->create();
        $category = RegulationCategory::create(['name' => 'Test Category']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $document = ReviewDocument::create([
            'user_id' => $user->id,
            'title' => 'Test Document',
            'description' => 'Test',
            'file_path' => 'documents/test.pdf',
            'status' => 'draft',
        ]);

        $regulation = Regulation::create([
            'regulation_number' => 'UU/1/2024',
            'title' => 'Test Regulation',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2024,
            'file_path' => 'regulations/test-reg.pdf',
        ]);

        $document->regulations()->attach($regulation->id);
        $document->loadMissing(['regulations.documents', 'partitions']);

        $this->mockParser->shouldReceive('extractFromStoragePath')
            ->with('documents/test.pdf')
            ->once()
            ->andReturn('Document text content');

        $this->mockParser->shouldReceive('extractFromStoragePath')
            ->with('regulations/test-reg.pdf')
            ->once()
            ->andReturn('Regulation text via OCR');

        // Use reflection to call private method
        $method = new \ReflectionMethod($this->aiService, 'saveParsedTexts');
        $method->invoke($this->aiService, $document);

        $this->assertDatabaseHas('document_parsed_texts', [
            'review_document_id' => $document->id,
            'source_type' => 'regulation',
            'source_id' => $regulation->id,
            'parsed_text' => 'Regulation text via OCR',
        ]);

        $this->assertDatabaseHas('document_parsed_texts', [
            'review_document_id' => $document->id,
            'source_type' => 'document',
            'parsed_text' => 'Document text content',
        ]);
    }

    public function test_save_parsed_texts_uses_cache_for_existing_regulation(): void
    {
        $user = User::factory()->create();
        $category = RegulationCategory::create(['name' => 'Test Category']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $document = ReviewDocument::create([
            'user_id' => $user->id,
            'title' => 'Test Document',
            'description' => 'Test',
            'file_path' => 'documents/test.pdf',
            'status' => 'draft',
        ]);

        $regulation = Regulation::create([
            'regulation_number' => 'UU/1/2024',
            'title' => 'Test Regulation',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2024,
            'file_path' => 'regulations/test-reg.pdf',
        ]);

        $document->regulations()->attach($regulation->id);

        // Pre-populate cache
        DocumentParsedText::create([
            'review_document_id' => $document->id,
            'source_type' => 'regulation',
            'source_id' => $regulation->id,
            'parsed_text' => 'Cached regulation text',
            'char_count' => 22,
        ]);

        $document->loadMissing(['regulations.documents', 'partitions']);

        // Parser should NOT be called for regulation (cached)
        $this->mockParser->shouldReceive('extractFromStoragePath')
            ->with('documents/test.pdf')
            ->once()
            ->andReturn('Document text');

        $this->mockParser->shouldNotReceive('extractFromStoragePath')
            ->with('regulations/test-reg.pdf');

        $method = new \ReflectionMethod($this->aiService, 'saveParsedTexts');
        $method->invoke($this->aiService, $document);

        // Regulation cache should remain untouched
        $this->assertDatabaseHas('document_parsed_texts', [
            'review_document_id' => $document->id,
            'source_type' => 'regulation',
            'source_id' => $regulation->id,
            'parsed_text' => 'Cached regulation text',
        ]);

        $this->assertEquals(1, DocumentParsedText::forRegulation($document->id, $regulation->id)->count());
    }

    public function test_get_or_parse_regulation_text_returns_cached_text(): void
    {
        $user = User::factory()->create();
        $category = RegulationCategory::create(['name' => 'Test Category']);
        $type = RegulationType::create(['name' => 'UU', 'level' => 1]);

        $document = ReviewDocument::create([
            'user_id' => $user->id,
            'title' => 'Test Document',
            'description' => 'Test',
            'file_path' => 'documents/test.pdf',
            'status' => 'draft',
        ]);

        $regulation = Regulation::create([
            'regulation_number' => 'UU/2/2024',
            'title' => 'Another Regulation',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2024,
            'file_path' => 'regulations/another.pdf',
        ]);

        $document->regulations()->attach($regulation->id);

        DocumentParsedText::create([
            'review_document_id' => $document->id,
            'source_type' => 'regulation',
            'source_id' => $regulation->id,
            'parsed_text' => 'Previously parsed regulation text',
            'char_count' => 35,
        ]);

        // Parser should NOT be called
        $this->mockParser->shouldNotReceive('extractFromStoragePath');

        $method = new \ReflectionMethod($this->aiService, 'getOrParseRegulationText');
        $result = $method->invoke($this->aiService, $document, $regulation);

        $this->assertEquals('Previously parsed regulation text', $result);
    }
}
