<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use App\Support\TextNormalizer;
use Illuminate\Database\Eloquent\Model;

/** Small shared helpers for admin controllers. */
trait Concerns
{
    protected function syncTagNames(Model $model, ?string $input): void
    {
        $ids = [];
        foreach (array_unique(array_filter(array_map('trim', preg_split('/[,،]/u', (string) $input)))) as $name) {
            $slug = TextNormalizer::slug($name);
            $tag = Tag::whereHas('translations', fn ($q) => $q->where('locale', 'fa')->where('slug', $slug))->first();
            if (! $tag) {
                $tag = Tag::create(['code' => substr(md5($slug), 0, 12)]);
                $tag->saveTranslations(['fa' => ['name' => $name]], 'name');
            }
            $ids[] = $tag->id;
        }
        $model->tags()->sync($ids);
    }

    protected function publishDate(Model $model): void
    {
        if ($model->status?->value === 'published' && ! $model->published_at) {
            $model->published_at = now();
        }
    }
}
