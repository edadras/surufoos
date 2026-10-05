<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 48)->index();
            $table->string('key', 100);
            $table->string('locale', 5)->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['key', 'locale']);
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('route_key', 100)->nullable()->index();
            $table->nullableMorphs('seoable');
            $table->string('locale', 5)->index();
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('canonical', 500)->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('noindex')->default(false);
            $table->timestamps();
            $table->unique(['route_key', 'locale']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 500);
            $table->string('to_path', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
            $table->index('from_path', 'redirects_from_path_index');
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('locale', 5)->default('fa');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->text('reply_note')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('fa');
            $table->string('status', 16)->default('active')->index();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->text('body');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('locale', 5)->default('fa');
            $table->string('status', 16)->default('pending')->index();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 48)->index();
            $table->nullableMorphs('subject');
            $table->string('description', 500)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term', 191);
            $table->string('locale', 5);
            $table->unsignedInteger('hits')->default(0);
            $table->unsignedInteger('last_results_count')->default(0);
            $table->timestamps();
            $table->unique(['term', 'locale']);
            $table->index('hits');
        });

        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('path', 400);
            $table->string('locale', 5);
            $table->nullableMorphs('viewable');
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->unique(['date', 'path', 'locale'], 'page_views_day_path_unique');
        });

        Schema::create('content_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_url', 1000);
            $table->string('source_key', 64)->index();
            $table->string('source_type', 32)->index();
            $table->string('destination_type', 64)->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->string('status', 16)->index();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['source_key', 'source_type']);
            $table->index(['destination_type', 'destination_id']);
        });
    }

    public function down(): void
    {
        foreach (['content_import_logs', 'page_views', 'search_terms', 'activity_logs', 'comments', 'newsletter_subscribers',
            'contacts', 'redirects', 'seo_meta', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
