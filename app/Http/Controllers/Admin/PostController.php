<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Support\TextNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    use Concerns;

    public function index(Request $request)
    {
        $posts = Post::with(['translations', 'cover', 'category.translations'])
            ->when($request->filled('q'), fn ($q) => $q->whereHas('translations', fn ($t) => $t->where('search_text', 'like', '%'.TextNormalizer::normalize($request->query('q')).'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.form', $this->formData(new Post(['status' => ContentStatus::Draft])));
    }

    public function store(PostRequest $request)
    {
        $post = $this->save(new Post(['author_id' => $request->user()->id]), $request);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'مقاله ذخیره شد.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', $this->formData($post->load(['translations', 'gallery', 'products', 'tags.translations'])));
    }

    public function update(PostRequest $request, Post $post)
    {
        $this->save($post, $request);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'مقاله حذف شد.');
    }

    private function save(Post $post, PostRequest $request): Post
    {
        return DB::transaction(function () use ($post, $request) {
            $data = $request->validated();
            $post->fill(collect($data)->only(['post_category_id', 'cover_media_id', 'author_name', 'status', 'is_featured', 'reading_minutes', 'published_at'])->all());
            $this->publishDate($post);
            $post->save();
            $post->saveTranslations($request->translations());
            $post->gallery()->delete();
            foreach (array_values($data['gallery'] ?? []) as $i => $mid) {
                $post->gallery()->create(['media_id' => $mid, 'sort_order' => $i]);
            }
            $post->products()->sync($data['products'] ?? []);
            $this->syncTagNames($post, $data['tags'] ?? '');

            return $post;
        });
    }

    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => PostCategory::with('translations')->orderBy('sort_order')->get(),
            'products' => Product::with('translations')->orderByDesc('id')->get(),
        ];
    }
}
