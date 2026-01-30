<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HomeHero;
use App\Models\HomeSearchSection;
use App\Models\HomeLocation;
use App\Models\HomeParallaxImage;
use App\Models\HomeSector;
use App\Models\HomeLoopText;
use App\Models\CmsContent;
use App\Models\CompanyProfile;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;

class PortalController extends Controller
{
    use ProvidesPortalLayoutData;

    public function index()
    {
        $layoutData = $this->getSharedLayoutData();

        $companyCta = CompanyProfile::inRandomOrder()->first();

        // 2. Banner Principal (Hero)
        $hero = HomeHero::where('is_active', true)->latest()->first();

        // 3. Sección de Búsqueda
        $searchSection = HomeSearchSection::where('is_active', true)->latest()->first();

        // 5. Ubicaciones
        $locations = HomeLocation::where('is_active', true)->orderBy('order')->get();

        // 6. Imagen Parallax
        $parallaxImage = HomeParallaxImage::where('is_active', true)->latest()->first();

        // 7. Sectores
        $sectors = HomeSector::where('is_active', true)->orderBy('order')->get();

        // 8. Texto Loop
        $loopTexts = HomeLoopText::where('is_active', true)->get();

        // 9. Noticias (CMS)
        $news = [];
        if (class_exists('App\Models\CmsContent')) {
            $news = CmsContent::where('type', 'blog')
                ->where('is_published', true)
                ->latest()
                ->take(4)
                ->get();
        }

        return view('index', array_merge($layoutData, compact(
            'companyCta',
            'hero',
            'searchSection',
            'locations',
            'parallaxImage',
            'sectors',
            'loopTexts',
            'news'
        )));
    }

    public function blog(Request $request)
    {
        $layoutData = $this->getSharedLayoutData();

        $search = trim((string) $request->input('search', '')) ?: null;
        $tag = trim((string) $request->input('tag', '')) ?: null;
        $authorId = $request->filled('author') ? (int) $request->input('author') : null;
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        $year = $request->filled('year') ? (int) $request->input('year') : null;

        $postsQuery = CmsContent::blogPosts()
            ->published()
            ->whereNotNull('published_at')
            ->with(['author:id,name']);

        if ($search) {
            $postsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('meta_description', 'like', "%{$search}%");
            });
        }

        if ($tag) {
            $postsQuery->whereRaw('LOWER(meta_keywords) LIKE ?', ['%' . strtolower($tag) . '%']);
        }

        if ($authorId) {
            $postsQuery->where('user_id', $authorId);
        }

        if ($month) {
            $postsQuery->whereMonth('published_at', $month);
        }

        if ($year) {
            $postsQuery->whereYear('published_at', $year);
        }

        $posts = $postsQuery
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $posts->getCollection()->transform(function ($post) {
            $post->tag_list = $this->parseTags($post->meta_keywords);
            return $post;
        });

        $recentPosts = CmsContent::blogPosts()
            ->published()
            ->whereNotNull('published_at')
            ->with(['author:id,name'])
            ->orderByDesc('published_at')
            ->take(5)
            ->get()
            ->map(function ($post) {
                $post->tag_list = $this->parseTags($post->meta_keywords);
                return $post;
            });

        $tagCloud = $this->buildTagCloud();

        $archives = CmsContent::blogPosts()
            ->published()
            ->whereNotNull('published_at')
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $authors = CmsContent::blogPosts()
            ->published()
            ->whereNotNull('user_id')
            ->with('author:id,name')
            ->get()
            ->pluck('author')
            ->filter()
            ->unique('id')
            ->values();

        $months = collect(range(1, 12))->mapWithKeys(function ($monthNumber) {
            $name = Carbon::create()->month($monthNumber)->locale(app()->getLocale())->translatedFormat('F');
            return [$monthNumber => ucfirst($name)];
        })->toArray();

        return view('blog.index', array_merge($layoutData, [
            'posts' => $posts,
            'recentPosts' => $recentPosts,
            'tagCloud' => $tagCloud,
            'archives' => $archives,
            'authors' => $authors,
            'months' => $months,
            'search' => $search,
            'selectedTag' => $tag,
            'selectedAuthor' => $authorId,
            'selectedMonth' => $month,
            'selectedYear' => $year,
        ]));
    }

    public function blogShow(string $slug)
    {
        $layoutData = $this->getSharedLayoutData();

        $post = CmsContent::blogPosts()
            ->published()
            ->where('slug', $slug)
            ->with(['author:id,name'])
            ->firstOrFail();

        $postTags = $this->parseTags($post->meta_keywords);

        $relatedPostsQuery = CmsContent::blogPosts()
            ->published()
            ->where('id', '<>', $post->id)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->with(['author:id,name'])
            ->take(3);

        if (!empty($postTags)) {
            $firstTag = $postTags[0];
            $relatedPostsQuery->whereRaw('LOWER(meta_keywords) LIKE ?', ['%' . strtolower($firstTag) . '%']);
        }

        $relatedPosts = $relatedPostsQuery->get()->map(function ($related) {
            $related->tag_list = $this->parseTags($related->meta_keywords);
            return $related;
        });

        return view('blog.show', array_merge($layoutData, [
            'post' => $post,
            'postTags' => $postTags,
            'relatedPosts' => $relatedPosts,
        ]));
    }

    public function info(string $slug)
    {
        $layoutData = $this->getSharedLayoutData();

        $page = CmsContent::where('type', 'page')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('info.show', array_merge($layoutData, [
            'page' => $page,
        ]));
    }

    protected function buildTagCloud()
    {
        return CmsContent::blogPosts()
            ->published()
            ->pluck('meta_keywords')
            ->filter()
            ->flatMap(function ($keywords) {
                return $this->parseTags($keywords);
            })
            ->countBy()
            ->map(function ($count, $tag) {
                return [
                    'tag' => $tag,
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    protected function parseTags(?string $keywords): array
    {
        if (empty($keywords)) {
            return [];
        }

        return collect(explode(',', $keywords))
            ->map(function ($tag) {
                return trim($tag);
            })
            ->filter()
            ->unique(function ($tag) {
                return strtolower($tag);
            })
            ->values()
            ->all();
    }
}
