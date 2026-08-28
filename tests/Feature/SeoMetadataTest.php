<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        return ['--schema-path' => '/dev/null'];
    }

    public function test_homepage_contains_indexable_brand_metadata_and_structured_data(): void
    {
        $this->get(route('index-dash'))
            ->assertOk()
            ->assertSee('<title>InvestaLawCo — Platform Informasi Regulasi &amp; Kepatuhan Hukum Indonesia</title>', false)
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('<link rel="canonical" href="'.route('index-dash').'">', false)
            ->assertSee('Investalawco: Informasi Regulasi & Kepatuhan Hukum Indonesia', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"alternateName":"Investalawco"', false);
    }

    public function test_profile_page_remains_available_but_canonicalizes_to_homepage(): void
    {
        $this->get(route('index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('index-dash').'">', false);
    }

    public function test_filtered_regulation_database_is_not_indexed(): void
    {
        $this->get(route('index-dash', ['search' => 'pasar modal']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('index-dash').'">', false);
    }

    public function test_public_regulation_has_canonical_metadata_and_legislation_schema(): void
    {
        $regulation = $this->makeRegulation();

        $this->get(route('regulations.show', $regulation))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('<link rel="canonical" href="'.route('regulations.show', $regulation).'">', false)
            ->assertSee('"@type":"Legislation"', false)
            ->assertSee('<h1', false);
    }

    public function test_sitemap_and_robots_list_public_urls(): void
    {
        $regulation = $this->makeRegulation();

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('index-dash'), false)
            ->assertSee(route('regulations.show', $regulation), false);

        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.route('seo.sitemap'), false);
    }

    public function test_authentication_page_is_not_indexed(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    private function makeRegulation(): Regulation
    {
        $type = RegulationType::create(['name' => 'POJK', 'level' => 1]);
        $category = RegulationCategory::create(['name' => 'Pasar Modal']);

        return Regulation::create([
            'regulation_number' => 'POJK/10/2026',
            'title' => 'Pasar Modal Digital',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
        ]);
    }
}
