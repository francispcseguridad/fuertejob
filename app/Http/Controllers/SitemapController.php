<?php

namespace App\Http\Controllers;

use App\Models\CmsContent;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $now = now();

        $staticUrls = [
            [
                'loc' => url('/'),
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('login'),
                'lastmod' => $now->copy()->subDays(2),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('worker.register.form'),
                'lastmod' => $now->copy()->subDays(2),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('verification.altausuario'),
                'lastmod' => $now->copy()->subDays(2),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('company.register.create'),
                'lastmod' => $now->copy()->subDays(2),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ],
            [
                'loc' => route('company.register.success'),
                'lastmod' => $now->copy()->subDays(2),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('public.jobs.index'),
                'lastmod' => $now->copy()->subDay(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => $now->copy()->subDay(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        $infoPages = CmsContent::query()
            ->where('type', CmsContent::TYPE_PAGE)
            ->published()
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (CmsContent $page) {
                return [
                    'loc' => route('info.show', ['slug' => $page->slug]),
                    'lastmod' => $this->resolveLastmod($page),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            });

        $blogPosts = CmsContent::query()
            ->where('type', CmsContent::TYPE_BLOG)
            ->published()
            ->whereNotNull('slug')
            ->orderByDesc('published_at')
            ->get()
            ->map(function (CmsContent $post) {
                return [
                    'loc' => route('blog.show', ['slug' => $post->slug]),
                    'lastmod' => $this->resolveLastmod($post),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            });

        $urls = collect($staticUrls)
            ->merge($infoPages)
            ->merge($blogPosts);

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    private function resolveLastmod(CmsContent $content): Carbon
    {
        return $content->updated_at ?? $content->published_at ?? $content->created_at ?? now();
    }
}
