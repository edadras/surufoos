<?php

use App\Support\TranslationSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('icon_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('theme_color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 16)->default('published')->index();
            $table->boolean('show_in_menu')->default(true);
            $table->timestamps();
        });
        TranslationSchema::create('category_translations', 'category_id', 'categories');

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 64)->nullable()->unique();
            $table->string('status', 16)->default('draft')->index();
            $table->foreignId('main_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('packshot_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('primary_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedBigInteger('price')->nullable()->index();
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->string('stock_status', 16)->default('in_stock')->index();
            $table->integer('stock_qty')->nullable();
            $table->decimal('net_weight', 10, 2)->nullable();
            $table->string('weight_unit', 8)->nullable();
            $table->string('theme_color', 7)->nullable();
            $table->string('badge', 32)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_new')->default(false)->index();
            $table->boolean('show_inside')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('views_count')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index('created_at');
        });
        TranslationSchema::create('product_translations', 'product_id', 'products', function (Blueprint $t) {
            $t->longText('story')->nullable();
            $t->text('how_to_use')->nullable();
            $t->text('storage')->nullable();
            $t->text('serving_suggestion')->nullable();
            $t->text('features')->nullable();
            $t->string('badge_label', 64)->nullable();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('type', 16)->default('gallery')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_category', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
            $table->index('category_id');
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type', 16)->default('select');
            $table->boolean('is_filterable')->default(true)->index();
            $table->boolean('is_quiz')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->string('quiz_question')->nullable();
            $table->timestamps();
            $table->unique(['attribute_id', 'locale']);
        });
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['attribute_id', 'code']);
        });
        Schema::create('attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('value');
            $table->timestamps();
            $table->unique(['attribute_value_id', 'locale']);
        });
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'attribute_value_id']);
            $table->index('attribute_value_id');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64)->nullable()->unique();
            $table->unsignedBigInteger('price')->nullable()->index();
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->integer('stock_qty')->nullable();
            $table->string('stock_status', 16)->default('in_stock');
            $table->decimal('net_weight', 10, 2)->nullable();
            $table->string('weight_unit', 8)->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id']);
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });
        Schema::create('ingredient_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['ingredient_id', 'locale']);
        });
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('amount', 32)->nullable();
            $table->decimal('position_x', 5, 2)->nullable();
            $table->decimal('position_y', 5, 2)->nullable();
            $table->boolean('is_highlight')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'ingredient_id']);
        });

        Schema::create('nutrition_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('nutrient', 32);
            $table->decimal('amount', 10, 2);
            $table->string('unit', 8);
            $table->string('per', 32)->default('100g');
            $table->decimal('daily_value_percent', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'nutrient', 'per']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->timestamps();
        });
        Schema::create('tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->string('slug', 191);
            $table->timestamps();
            $table->unique(['tag_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->primary(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        foreach (['taggables', 'tag_translations', 'tags', 'nutrition_facts', 'product_ingredients', 'ingredient_translations', 'ingredients',
            'variant_attribute_values', 'product_variants', 'product_attribute_values', 'attribute_value_translations', 'attribute_values',
            'attribute_translations', 'attributes', 'product_category', 'product_images', 'product_translations', 'products',
            'category_translations', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
