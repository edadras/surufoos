<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the standard `{entity}_translations` table used by every translatable entity.
 * Each row holds one locale of one record: locale, title, slug, descriptions and SEO fields.
 */
class TranslationSchema
{
    public static function create(string $table, string $foreignKey, string $parentTable, ?callable $extra = null, bool $withSlug = true): void
    {
        Schema::create($table, function (Blueprint $t) use ($foreignKey, $parentTable, $extra, $withSlug) {
            $t->id();
            $t->foreignId($foreignKey)->constrained($parentTable)->cascadeOnDelete();
            $t->string('locale', 5)->index();
            $t->string('title');
            if ($withSlug) {
                $t->string('slug', 191);
            }
            $t->text('short_description')->nullable();
            $t->longText('description')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 500)->nullable();
            $t->text('search_text')->nullable();
            if ($extra) {
                $extra($t);
            }
            $t->timestamps();

            $t->unique([$foreignKey, 'locale']);
            if ($withSlug) {
                $t->unique(['locale', 'slug']);
            }
        });
    }
}
