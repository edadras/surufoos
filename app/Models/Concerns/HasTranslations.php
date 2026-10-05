<?php

namespace App\Models\Concerns;

use App\Support\Locales;
use App\Support\TextNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Row-per-locale translations stored in `{model}_translations`.
 *
 * Models using this trait may declare:
 *  - protected string $translationModel  (defaults to "{Model}Translation")
 *  - protected array  $searchableTranslationFields (fields copied into search_text)
 */
trait HasTranslations
{
    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModelClass(), $this->translationForeignKey());
    }

    public function translationModelClass(): string
    {
        return property_exists($this, 'translationModel') ? $this->translationModel : static::class.'Translation';
    }

    public function translationForeignKey(): string
    {
        return Str::snake(class_basename($this)).'_id';
    }

    public function translationTable(): string
    {
        return (new ($this->translationModelClass()))->getTable();
    }

    /** The translation row for a locale, optionally falling back to the source locale. */
    public function translation(?string $locale = null, ?bool $fallback = null): ?Model
    {
        $locale ??= app()->getLocale();
        $fallback ??= (bool) config('suru.content_fallback');
        $translations = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        $row = $translations->firstWhere('locale', $locale);

        if (! $row && $fallback) {
            $row = $translations->firstWhere('locale', Locales::source());
        }

        return $row;
    }

    public function tr(string $field, ?string $locale = null, ?bool $fallback = null): mixed
    {
        return $this->translation($locale, $fallback)?->{$field};
    }

    public function hasTranslation(string $locale): bool
    {
        $translations = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();

        return $translations->contains('locale', $locale);
    }

    /** @return array<string, bool> locale => is translated */
    public function translationStatus(): array
    {
        $status = [];
        foreach (Locales::codes() as $locale) {
            $status[$locale] = $this->hasTranslation($locale);
        }

        return $status;
    }

    public function scopeTranslatedIn(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();
        if (config('suru.content_fallback')) {
            return $query;
        }

        return $query->whereHas('translations', fn ($q) => $q->where('locale', $locale));
    }

    public function scopeWithTranslation(Builder $query): Builder
    {
        $locales = array_unique([app()->getLocale(), Locales::source()]);

        return $query->with(['translations' => fn ($q) => $q->whereIn('locale', $locales)]);
    }

    public function scopeWhereSlug(Builder $query, string $slug, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug));
    }

    /**
     * Saves translations from ['fa' => [...], 'en' => [...]]. A locale whose
     * primary field is empty is removed so the translation manager reports it as missing.
     */
    public function saveTranslations(array $data, string $primaryField = 'title'): void
    {
        $model = $this->translationModelClass();
        $table = (new $model)->getTable();
        $columns = Schema::getColumnListing($table);
        $hasSlug = in_array('slug', $columns, true);
        $hasSearch = in_array('search_text', $columns, true);

        foreach (Locales::codes() as $locale) {
            if (! array_key_exists($locale, $data)) {
                continue;
            }
            $values = array_intersect_key($data[$locale] ?? [], array_flip($columns));
            unset($values['id'], $values[$this->translationForeignKey()], $values['locale']);

            if (blank($values[$primaryField] ?? null)) {
                $this->translations()->where('locale', $locale)->delete();

                continue;
            }

            if ($hasSlug) {
                $values['slug'] = $this->uniqueSlug($table, $locale, filled($values['slug'] ?? null) ? $values['slug'] : $values[$primaryField]);
            }
            if ($hasSearch) {
                $values['search_text'] = TextNormalizer::normalize(implode(' ', array_map(
                    fn ($f) => $values[$f] ?? '',
                    $this->searchableTranslationFields ?? [$primaryField, 'short_description']
                )));
            }

            $this->translations()->updateOrCreate(['locale' => $locale], $values);
        }

        $this->unsetRelation('translations');
    }

    protected function uniqueSlug(string $table, string $locale, string $source): string
    {
        $base = TextNormalizer::slug($source) ?: Str::random(8);
        $slug = $base;
        $i = 2;
        $fk = $this->translationForeignKey();
        while (DB::table($table)->where('locale', $locale)->where('slug', $slug)->where($fk, '!=', $this->getKey())->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
