<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

/*
| Persian admin panel. Every section is behind `admin` (active admin account +
| passed 2FA when enabled) and a permission key checked by `admin.can`.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'loginForm'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:login');
    });
    Route::middleware('auth')->group(function () {
        Route::get('two-factor', [Admin\AuthController::class, 'challengeForm'])->name('two-factor.challenge');
        Route::post('two-factor', [Admin\AuthController::class, 'challenge'])->middleware('throttle:login');
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/two-factor', [Admin\ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
        Route::post('profile/two-factor/confirm', [Admin\ProfileController::class, 'confirmTwoFactor'])->name('profile.2fa.confirm');
        Route::delete('profile/two-factor', [Admin\ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');

        Route::middleware('admin.can:products.manage')->group(function () {
            Route::resource('products', Admin\ProductController::class)->except('show');
            Route::post('products/{product}/duplicate', [Admin\ProductController::class, 'duplicate'])->name('products.duplicate');
            Route::resource('categories', Admin\CategoryController::class)->except('show');
            Route::post('categories/reorder', [Admin\CategoryController::class, 'reorder'])->name('categories.reorder');
            Route::resource('attributes', Admin\AttributeController::class)->except('show');
            Route::resource('ingredients', Admin\IngredientController::class)->except('show');
        });

        Route::middleware('admin.can:content.manage')->group(function () {
            Route::resource('posts', Admin\PostController::class)->except('show');
            Route::resource('post-categories', Admin\PostCategoryController::class)->except('show')->parameters(['post-categories' => 'postCategory']);
            Route::resource('recipes', Admin\RecipeController::class)->except('show');
            Route::resource('pages', Admin\PageController::class)->except('show');
            Route::post('pages/{page}/sections', [Admin\PageSectionController::class, 'store'])->name('pages.sections.store');
            Route::get('pages/{page}/sections/{section}/edit', [Admin\PageSectionController::class, 'edit'])->name('pages.sections.edit');
            Route::put('pages/{page}/sections/{section}', [Admin\PageSectionController::class, 'update'])->name('pages.sections.update');
            Route::delete('pages/{page}/sections/{section}', [Admin\PageSectionController::class, 'destroy'])->name('pages.sections.destroy');
            Route::post('pages/{page}/sections/reorder', [Admin\PageSectionController::class, 'reorder'])->name('pages.sections.reorder');
            Route::patch('pages/{page}/sections/{section}/toggle', [Admin\PageSectionController::class, 'toggle'])->name('pages.sections.toggle');
            Route::resource('sliders', Admin\SliderController::class)->except('show');
            Route::resource('banners', Admin\BannerController::class)->except('show');
            Route::resource('menus', Admin\MenuController::class)->only(['index', 'edit', 'update']);
            Route::post('menus/{menu}/items', [Admin\MenuController::class, 'storeItem'])->name('menus.items.store');
            Route::put('menus/{menu}/items/{item}', [Admin\MenuController::class, 'updateItem'])->name('menus.items.update');
            Route::delete('menus/{menu}/items/{item}', [Admin\MenuController::class, 'destroyItem'])->name('menus.items.destroy');
            Route::post('menus/{menu}/reorder', [Admin\MenuController::class, 'reorder'])->name('menus.reorder');
            Route::get('blocks/{type}', [Admin\BlockController::class, 'index'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.index');
            Route::get('blocks/{type}/create', [Admin\BlockController::class, 'create'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.create');
            Route::post('blocks/{type}', [Admin\BlockController::class, 'store'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.store');
            Route::get('blocks/{type}/{id}/edit', [Admin\BlockController::class, 'edit'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.edit');
            Route::put('blocks/{type}/{id}', [Admin\BlockController::class, 'update'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.update');
            Route::delete('blocks/{type}/{id}', [Admin\BlockController::class, 'destroy'])->whereIn('type', array_keys(Admin\BlockController::TYPES))->name('blocks.destroy');
            Route::get('comments', [Admin\CommentController::class, 'index'])->name('comments.index');
            Route::patch('comments/{comment}', [Admin\CommentController::class, 'update'])->name('comments.update');
            Route::delete('comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');
        });

        Route::middleware('admin.can:media.manage')->group(function () {
            Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
            Route::get('media/picker', [Admin\MediaController::class, 'picker'])->name('media.picker');
            Route::post('media', [Admin\MediaController::class, 'store'])->middleware('throttle:admin-upload')->name('media.store');
            Route::get('media/{media}/edit', [Admin\MediaController::class, 'edit'])->name('media.edit');
            Route::put('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
            Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
            Route::post('media/{media}/regenerate', [Admin\MediaController::class, 'regenerate'])->name('media.regenerate');
            Route::post('media-folders', [Admin\MediaController::class, 'storeFolder'])->name('media.folders.store');
        });

        Route::middleware('admin.can:translations.manage')->group(function () {
            Route::get('translations', [Admin\TranslationController::class, 'index'])->name('translations.index');
            Route::get('translations/{type}/{id}', [Admin\TranslationController::class, 'edit'])->name('translations.edit');
            Route::put('translations/{type}/{id}', [Admin\TranslationController::class, 'update'])->name('translations.update');
        });

        Route::middleware('admin.can:messages.manage')->group(function () {
            Route::get('contacts', [Admin\ContactController::class, 'index'])->name('contacts.index');
            Route::get('contacts/{contact}', [Admin\ContactController::class, 'show'])->name('contacts.show');
            Route::put('contacts/{contact}', [Admin\ContactController::class, 'update'])->name('contacts.update');
            Route::delete('contacts/{contact}', [Admin\ContactController::class, 'destroy'])->name('contacts.destroy');
            Route::get('newsletter', [Admin\NewsletterController::class, 'index'])->name('newsletter.index');
            Route::get('newsletter/export', [Admin\NewsletterController::class, 'export'])->name('newsletter.export');
            Route::delete('newsletter/{subscriber}', [Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');
        });

        Route::middleware('admin.can:orders.manage')->group(function () {
            Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
            Route::put('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
        });

        Route::middleware('admin.can:seo.manage')->group(function () {
            Route::get('seo', [Admin\SeoController::class, 'index'])->name('seo.index');
            Route::put('seo', [Admin\SeoController::class, 'update'])->name('seo.update');
            Route::resource('redirects', Admin\RedirectController::class)->except('show');
        });

        Route::middleware('admin.can:users.manage')->group(function () {
            Route::resource('users', Admin\UserController::class)->except('show');
            Route::resource('roles', Admin\RoleController::class)->except('show');
        });

        Route::middleware('admin.can:settings.manage')->group(function () {
            Route::get('settings/{group?}', [Admin\SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings/{group}', [Admin\SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/theme/extract', [Admin\SettingController::class, 'extractPalette'])->name('settings.extract-palette');
            Route::get('languages', [Admin\LanguageController::class, 'index'])->name('languages.index');
        });

        Route::middleware('admin.can:reports.view')->group(function () {
            Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
            Route::get('activity', [Admin\ReportController::class, 'activity'])->name('activity.index');
        });

        Route::middleware('admin.can:import.manage')->group(function () {
            Route::get('import', [Admin\ImportController::class, 'index'])->name('import.index');
            Route::post('import', [Admin\ImportController::class, 'run'])->name('import.run');
        });
    });
});
