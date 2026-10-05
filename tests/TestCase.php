<?php

namespace Tests;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\StructureSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Test fixtures are created per test and rolled back; they exist only to
 * exercise the code and are never part of the shipped site content.
 */
abstract class TestCase extends BaseTestCase
{
    protected function seedStructure(): void
    {
        $this->seed([RolesSeeder::class, StructureSeeder::class]);
    }

    protected function admin(string $role = 'super_admin'): User
    {
        $user = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $user->roles()->attach(Role::where('key', $role)->firstOrFail());

        return $user;
    }

    protected function category(array $titles = ['fa' => 'دسته آزمایشی']): Category
    {
        $category = Category::create(['status' => ContentStatus::Published]);
        $category->saveTranslations(collect($titles)->map(fn ($t) => ['title' => $t])->all());

        return $category;
    }

    protected function product(array $titles = ['fa' => 'محصول آزمایشی'], array $attrs = [], ?Category $category = null): Product
    {
        $product = Product::create($attrs + ['status' => ContentStatus::Published, 'published_at' => now()->subMinute(), 'stock_status' => 'in_stock']);
        $product->saveTranslations(collect($titles)->map(fn ($t) => ['title' => $t, 'short_description' => 'توضیح '.$t])->all());
        if ($category) {
            $product->categories()->attach($category);
            $product->update(['primary_category_id' => $category->id]);
        }

        return $product->fresh();
    }

    protected function media(): Media
    {
        return Media::create(['disk' => 'public', 'directory' => 'media/test', 'filename' => 'x.png', 'mime_type' => 'image/png', 'size' => 10, 'width' => 10, 'height' => 10, 'conversion_status' => 'done']);
    }
}
