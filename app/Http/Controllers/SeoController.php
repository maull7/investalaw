<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $regulations = Regulation::query()
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->limit(49_998)
            ->get();

        return response()
            ->view('seo.sitemap', compact('regulations'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $contents = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /landing-page',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /profile',
            'Disallow: /settings',
            'Disallow: /review-documents',
            'Disallow: /reviews',
            'Disallow: /users',
            'Disallow: /*?search=',
            'Disallow: /*?category_id=',
            'Disallow: /*?year=',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ]);

        return response($contents, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
