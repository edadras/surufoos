<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view' => ['مشاهده داشبورد', 'عمومی'],
        'products.manage' => ['مدیریت محصولات، دسته‌ها، ویژگی‌ها و مواد اولیه', 'کاتالوگ'],
        'content.manage' => ['مدیریت صفحات، مجله، دستورها، اسلایدر، بنر، منو و دیدگاه‌ها', 'محتوا'],
        'media.manage' => ['مدیریت رسانه‌ها', 'محتوا'],
        'translations.manage' => ['مدیریت ترجمه‌ها', 'محتوا'],
        'messages.manage' => ['پیام‌ها و خبرنامه', 'ارتباطات'],
        'orders.manage' => ['مدیریت سفارش‌ها', 'فروش'],
        'seo.manage' => ['سئو و ریدایرکت‌ها', 'سئو'],
        'users.manage' => ['کاربران و نقش‌ها', 'سیستم'],
        'settings.manage' => ['تنظیمات و زبان‌ها', 'سیستم'],
        'reports.view' => ['گزارش‌ها و گزارش فعالیت', 'سیستم'],
        'import.manage' => ['درون‌ریزی محتوای Suru Food', 'سیستم'],
    ];

    public const ROLES = [
        'super_admin' => ['مدیر کل', ['*']],
        'content_manager' => ['مدیر محتوا', ['dashboard.view', 'content.manage', 'media.manage', 'translations.manage', 'messages.manage']],
        'product_manager' => ['مدیر محصولات', ['dashboard.view', 'products.manage', 'media.manage', 'translations.manage']],
        'translator' => ['مترجم', ['dashboard.view', 'translations.manage']],
        'order_manager' => ['مدیر سفارش‌ها', ['dashboard.view', 'orders.manage', 'messages.manage']],
        'seo_manager' => ['مدیر سئو', ['dashboard.view', 'seo.manage', 'reports.view', 'translations.manage']],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $key => [$name, $group]) {
            Permission::updateOrCreate(['key' => $key], ['name' => $name, 'group' => $group]);
        }
        foreach (self::ROLES as $key => [$name, $perms]) {
            $role = Role::updateOrCreate(['key' => $key], ['name' => $name]);
            $ids = $perms === ['*'] ? Permission::pluck('id') : Permission::whereIn('key', $perms)->pluck('id');
            $role->permissions()->sync($ids);
        }
    }
}
