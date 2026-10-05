<?php

use App\Http\Controllers\Site;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

Route::get('/', Site\LocaleRedirectController::class)->name('root');
Route::get('robots.txt', [Site\SitemapController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [Site\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('sitemaps/{locale}-{type}.xml', [Site\SitemapController::class, 'show'])
    ->where(['locale' => Locales::routePattern(), 'type' => 'pages|products|categories|posts|recipes'])
    ->name('sitemap.show');

Route::prefix('{locale}')
    ->where(['locale' => Locales::routePattern()])
    ->middleware(['locale', 'track'])
    ->group(function () {
        Route::get('/', Site\HomeController::class)->name('home');

        Route::get('products', [Site\ProductController::class, 'index'])->name('products.index');
        Route::get('products/{slug}', [Site\ProductController::class, 'show'])->name('products.show');
        Route::get('quick-view/{product}', [Site\ProductController::class, 'quickView'])->whereNumber('product')->name('products.quick-view');

        Route::get('categories', [Site\CategoryController::class, 'index'])->name('categories.index');
        Route::get('category/{slug}', [Site\CategoryController::class, 'show'])->name('category.show');

        Route::get('about', [Site\PageController::class, 'about'])->name('about');
        Route::get('faq', [Site\PageController::class, 'faq'])->name('faq');
        Route::get('page/{slug}', [Site\PageController::class, 'show'])->name('page.show');

        Route::get('contact', [Site\ContactController::class, 'show'])->name('contact');
        Route::post('contact', [Site\ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
        Route::post('newsletter', [Site\NewsletterController::class, 'store'])->middleware('throttle:contact')->name('newsletter.store');
        Route::get('newsletter/unsubscribe/{token}', [Site\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

        Route::get('magazine', [Site\MagazineController::class, 'index'])->name('magazine.index');
        Route::get('magazine/category/{slug}', [Site\MagazineController::class, 'category'])->name('magazine.category');
        Route::get('magazine/{slug}', [Site\MagazineController::class, 'show'])->name('magazine.show');
        Route::post('magazine/{post}/comments', [Site\MagazineController::class, 'comment'])->whereNumber('post')->middleware('throttle:contact')->name('magazine.comment');

        Route::get('recipes', [Site\RecipeController::class, 'index'])->name('recipes.index');
        Route::get('recipes/{slug}', [Site\RecipeController::class, 'show'])->name('recipes.show');

        Route::get('search', [Site\SearchController::class, 'index'])->name('search');
        Route::get('search/live', [Site\SearchController::class, 'live'])->middleware('throttle:search')->name('search.live');

        Route::post('taste-finder', Site\TasteFinderController::class)->middleware('throttle:search')->name('taste-finder');

        Route::get('favorites', [Site\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('favorites/{product}', [Site\FavoriteController::class, 'toggle'])->whereNumber('product')->name('favorites.toggle');

        Route::get('cart', [Site\CartController::class, 'show'])->name('cart.show');
        Route::post('cart', [Site\CartController::class, 'add'])->name('cart.add');
        Route::patch('cart/{item}', [Site\CartController::class, 'update'])->whereNumber('item')->name('cart.update');
        Route::delete('cart/{item}', [Site\CartController::class, 'remove'])->whereNumber('item')->name('cart.remove');
        Route::get('checkout', [Site\CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('checkout', [Site\CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
        Route::get('order/{number}', [Site\CheckoutController::class, 'complete'])->name('checkout.complete');

        Route::middleware('guest')->group(function () {
            Route::get('login', [Site\AccountController::class, 'loginForm'])->name('account.login');
            Route::post('login', [Site\AccountController::class, 'login'])->middleware('throttle:login');
            Route::get('register', [Site\AccountController::class, 'registerForm'])->name('account.register');
            Route::post('register', [Site\AccountController::class, 'register'])->middleware('throttle:login');
        });
        Route::middleware('auth')->group(function () {
            Route::get('account', [Site\AccountController::class, 'index'])->name('account.index');
            Route::post('logout', [Site\AccountController::class, 'logout'])->name('account.logout');
        });
    });

Route::fallback(Site\FallbackController::class);
