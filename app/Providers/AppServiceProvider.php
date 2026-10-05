<?php

namespace App\Providers;

use App\Models;
use App\Observers\ContentObserver;
use App\Services\CartService;
use App\Services\FavoriteService;
use App\Services\Seo;
use App\Services\SettingsService;
use App\Support\Locales;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->scoped(Seo::class);
        $this->app->scoped(CartService::class);
    }

    public function boot(): void
    {
        Relation::enforceMorphMap([
            'user' => Models\User::class,
            'product' => Models\Product::class,
            'category' => Models\Category::class,
            'post' => Models\Post::class,
            'post_category' => Models\PostCategory::class,
            'recipe' => Models\Recipe::class,
            'page' => Models\Page::class,
            'page_section' => Models\PageSection::class,
            'media' => Models\Media::class,
            'slider' => Models\Slider::class,
            'banner' => Models\Banner::class,
            'menu' => Models\Menu::class,
            'menu_item' => Models\MenuItem::class,
            'faq' => Models\Faq::class,
            'testimonial' => Models\Testimonial::class,
            'timeline_entry' => Models\TimelineEntry::class,
            'certificate' => Models\Certificate::class,
            'ingredient' => Models\Ingredient::class,
            'attribute' => Models\Attribute::class,
            'tag' => Models\Tag::class,
            'order' => Models\Order::class,
            'contact' => Models\Contact::class,
            'setting' => Models\Setting::class,
            'redirect' => Models\Redirect::class,
            'comment' => Models\Comment::class,
            'role' => Models\Role::class,
        ]);

        foreach ([
            Models\Product::class, Models\ProductTranslation::class, Models\Category::class, Models\CategoryTranslation::class,
            Models\Post::class, Models\PostTranslation::class, Models\Recipe::class, Models\RecipeTranslation::class,
            Models\Page::class, Models\PageSection::class, Models\PageSectionTranslation::class, Models\Slider::class,
            Models\Banner::class, Models\Menu::class, Models\MenuItem::class, Models\MenuItemTranslation::class,
            Models\Setting::class, Models\Media::class, Models\MediaTranslation::class, Models\Faq::class,
            Models\Testimonial::class, Models\TimelineEntry::class, Models\Certificate::class, Models\Ingredient::class,
            Models\Attribute::class, Models\AttributeValue::class, Models\Redirect::class, Models\PostCategory::class,
            Models\Tag::class, Models\Order::class, Models\User::class, Models\Role::class,
        ] as $model) {
            $model::observe(ContentObserver::class);
        }

        Event::listen(Login::class, function (Login $event) {
            app(FavoriteService::class)->mergeSessionInto($event->user->id);
        });

        $this->configureRateLimiting();

        Paginator::defaultView('partials.pagination');

        View::composer(['layouts.site', 'partials.*', 'sections.*', 'site.*'], function ($view) {
            $locale = app()->getLocale();
            $view->with([
                'locale' => $locale,
                'dir' => Locales::dir($locale),
                'settings' => app(SettingsService::class),
            ]);
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by(mb_strtolower((string) $r->input('email')).'|'.$r->ip()),
            Limit::perMinute(20)->by($r->ip()),
        ]);
        RateLimiter::for('contact', fn (Request $r) => Limit::perMinutes(10, 5)->by($r->ip()));
        RateLimiter::for('search', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('checkout', fn (Request $r) => Limit::perMinute(6)->by($r->ip()));
        RateLimiter::for('admin-upload', fn (Request $r) => Limit::perMinute(60)->by((string) $r->user()?->id));
    }
}
