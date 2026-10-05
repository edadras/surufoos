<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds only structure, never content: roles/permissions, menu skeletons,
 * system pages with their section layout, quiz taxonomy and safe defaults.
 * Products, categories, images, articles and brand data come from
 * `php artisan suru:import` (real Suru Food content) or the admin panel.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            StructureSeeder::class,
        ]);
    }
}
