<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Seo;
use Illuminate\Http\Request;

class MagazineController extends Controller
{
    public function __construct(private Seo $seo) {}

    public function index(Request $request)
    {
        $this->seo->set(__('ui.magazine.title'), __('ui.magazine.intro'))->forRoute('magazine.index')->routeAlternates('magazine.index');
        $this->seo->breadcrumb(__('ui.magazine.title'));

        return view('site.magazine.index', $this->listing(null));
    }

    public function category(string $slug)
    {
        $category = PostCategory::whereSlug($slug)->with('translations')->firstOrFail();
        $this->seo->fromTranslation($category->translation())->alternatesFrom(fn ($l) => $category->url($l));
        $this->seo->breadcrumb(__('ui.magazine.title'), lroute('magazine.index'))->breadcrumb((string) $category->tr('title'));

        return view('site.magazine.index', $this->listing($category));
    }

    private function listing(?PostCategory $category): array
    {
        $posts = Post::published()->translatedIn()->withTranslation()->with(['cover.translations', 'category.translations'])
            ->when($category, fn ($q) => $q->where('post_category_id', $category->id))
            ->orderByDesc('published_at')->paginate(9);

        return [
            'posts' => $posts,
            'category' => $category,
            'featured' => ! $category && $posts->currentPage() === 1 ? $posts->first() : null,
            'categories' => PostCategory::translatedIn()->withTranslation()->whereHas('posts', fn ($q) => $q->published())->orderBy('sort_order')->get(),
        ];
    }

    public function show(Request $request, string $slug)
    {
        $post = Post::published()->whereSlug($slug)->with([
            'translations', 'cover.translations', 'category.translations', 'author', 'gallery.media.translations', 'tags.translations',
            'products' => fn ($q) => $q->published()->forCard(),
            'comments' => fn ($q) => $q->where('status', 'approved')->latest(),
        ])->first();
        abort_unless($post && $post->translation(), 404);
        $request->attributes->set('viewable', $post);

        $t = $post->translation();
        $this->seo->fromTranslation($t, $post->cover, 'article')->alternatesFrom(fn ($l) => $post->translation($l, false) ? $post->url($l) : null);
        $this->seo->breadcrumb(__('ui.magazine.title'), lroute('magazine.index'));
        if ($post->category) {
            $this->seo->breadcrumb((string) $post->category->tr('title'), $post->category->url());
        }
        $this->seo->breadcrumb((string) $t->title);
        $this->seo->schema(array_filter([
            '@type' => 'Article',
            'headline' => $t->title,
            'description' => strip_tags((string) $t->short_description),
            'image' => $post->cover?->variantUrl('large'),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => $post->authorName() ? ['@type' => 'Person', 'name' => $post->authorName()] : ['@type' => 'Organization', 'name' => settings()->siteName()],
            'publisher' => ['@type' => 'Organization', 'name' => settings()->siteName(), 'logo' => settings()->media('logo_media_id')?->url()],
            'mainEntityOfPage' => $post->url(),
        ]));

        $related = Post::published()->translatedIn()->withTranslation()->with('cover.translations')->whereKeyNot($post->id)
            ->when($post->post_category_id, fn ($q) => $q->where('post_category_id', $post->post_category_id))
            ->latest('published_at')->limit(3)->get();

        return view('site.magazine.show', compact('post', 'related'));
    }

    public function comment(Request $request, Post $post)
    {
        abort_unless($post->status->value === 'published', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:191'],
            'body' => ['required', 'string', 'min:3', 'max:2000'],
            'website' => ['prohibited'],
        ]);

        $post->comments()->create($data + [
            'user_id' => $request->user()?->id,
            'locale' => app()->getLocale(),
            'status' => 'pending',
            'ip' => $request->ip(),
        ]);

        return back()->with('status', __('ui.comments.pending'));
    }
}
