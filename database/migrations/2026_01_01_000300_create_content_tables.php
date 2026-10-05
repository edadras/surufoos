<?php

use App\Support\TranslationSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Magazine
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        TranslationSchema::create('post_category_translations', 'post_category_id', 'post_categories');

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('status', 16)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('reading_minutes')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });
        TranslationSchema::create('post_translations', 'post_id', 'posts');

        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::create('post_product', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'product_id']);
        });

        // Recipes
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('video_url', 500)->nullable();
            $table->foreignId('video_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('prep_minutes')->nullable();
            $table->unsignedSmallInteger('cook_minutes')->nullable();
            $table->unsignedSmallInteger('servings')->nullable();
            $table->string('difficulty', 16)->nullable();
            $table->json('nutrition')->nullable();
            $table->string('status', 16)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
        TranslationSchema::create('recipe_translations', 'recipe_id', 'recipes', function (Blueprint $t) {
            $t->text('tips')->nullable();
        });
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quantity', 64)->nullable();
            $table->string('name');
            $table->string('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('body');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('recipe_products', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['recipe_id', 'product_id']);
        });
        Schema::create('recipe_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
        });

        // Pages + page builder
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->nullable()->unique();
            $table->string('template', 32)->default('builder');
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 16)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        TranslationSchema::create('page_translations', 'page_id', 'pages');

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 48);
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
        Schema::create('page_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->longText('body')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
            $table->unique(['page_section_id', 'locale']);
        });

        // Hero slides
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 32)->default('home_hero')->index();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->json('layers')->nullable();
            $table->string('background_color', 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('slider_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->string('cta2_label')->nullable();
            $table->string('cta2_url', 500)->nullable();
            $table->timestamps();
            $table->unique(['slider_id', 'locale']);
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5)->nullable()->index();
            $table->string('placement', 32)->default('home')->index();
            $table->string('layout', 16)->default('full');
            $table->foreignId('desktop_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('text_color', 7)->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });

        // Menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('type', 16)->default('url');
            $table->nullableMorphs('linkable');
            $table->string('route_name', 100)->nullable();
            $table->boolean('is_mega')->default(false);
            $table->json('mega')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('menu_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('label');
            $table->string('url', 500)->nullable();
            $table->timestamps();
            $table->unique(['menu_item_id', 'locale']);
        });

        // Brand content
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('group', 32)->default('general')->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('question', 500);
            $table->text('answer');
            $table->timestamps();
            $table->unique(['faq_id', 'locale']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('testimonial_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testimonial_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->text('body');
            $table->timestamps();
            $table->unique(['testimonial_id', 'locale']);
        });

        // Brand story timeline (about page) and product journey (homepage)
        Schema::create('timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->string('group', 32)->default('history')->index();
            $table->string('label', 32)->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('icon', 48)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('timeline_entry_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timeline_entry_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
            $table->unique(['timeline_entry_id', 'locale']);
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('certificate_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['certificate_id', 'locale']);
        });
    }

    public function down(): void
    {
        foreach (['certificate_translations', 'certificates', 'timeline_entry_translations', 'timeline_entries', 'testimonial_translations',
            'testimonials', 'faq_translations', 'faqs', 'menu_item_translations', 'menu_items', 'menus', 'banners', 'slider_translations',
            'sliders', 'page_section_translations', 'page_sections', 'page_translations', 'pages', 'recipe_media', 'recipe_products',
            'recipe_steps', 'recipe_ingredients', 'recipe_translations', 'recipes', 'post_product', 'post_media', 'post_translations',
            'posts', 'post_category_translations', 'post_categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
