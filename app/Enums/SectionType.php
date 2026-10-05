<?php

namespace App\Enums;

/**
 * Page builder section types. Each type maps to a Blade view in
 * resources/views/sections and a field schema in the admin editor;
 * editors pick content and data sources, never free-form layout.
 */
enum SectionType: string
{
    case Hero = 'hero';
    case Story = 'story';
    case ProductUniverse = 'product_universe';
    case ProductShowcase = 'product_showcase';
    case InsideSuru = 'inside_suru';
    case Journey = 'journey';
    case Banners = 'banners';
    case ProductShelf = 'product_shelf';
    case TasteFinder = 'taste_finder';
    case RichText = 'rich_text';
    case ImageText = 'image_text';
    case ProductSlider = 'product_slider';
    case CategoryShowcase = 'category_showcase';
    case Gallery = 'gallery';
    case Video = 'video';
    case Faq = 'faq';
    case Testimonials = 'testimonials';
    case Recipes = 'recipes';
    case Articles = 'articles';
    case Cta = 'cta';
    case Timeline = 'timeline';
    case Certificates = 'certificates';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'هیرو (صحنه محصول)',
            self::Story => 'داستان برند',
            self::ProductUniverse => 'دنیای محصولات (دسته‌ها)',
            self::ProductShowcase => 'محصولات منتخب',
            self::InsideSuru => 'درون سورو (مواد اولیه)',
            self::Journey => 'مسیر محصول',
            self::Banners => 'بنرهای تبلیغاتی',
            self::ProductShelf => 'قفسه محصولات جدید',
            self::TasteFinder => 'چی دوست داری؟',
            self::RichText => 'متن',
            self::ImageText => 'تصویر + متن',
            self::ProductSlider => 'اسلایدر محصولات',
            self::CategoryShowcase => 'نمایش دسته‌بندی‌ها',
            self::Gallery => 'گالری',
            self::Video => 'ویدیو',
            self::Faq => 'پرسش‌های متداول',
            self::Testimonials => 'نظرات مشتریان',
            self::Recipes => 'دستورهای غذایی',
            self::Articles => 'مقالات مجله',
            self::Cta => 'دعوت به اقدام (CTA)',
            self::Timeline => 'تایم‌لاین تاریخچه',
            self::Certificates => 'گواهی‌ها و افتخارات',
        };
    }

    /** Settings fields (non-translatable) shown in the admin editor for this type. */
    public function settingsSchema(): array
    {
        return match ($this) {
            self::Hero => ['slider_placement' => 'text'],
            self::Story => ['media_id' => 'media', 'secondary_media_id' => 'media', 'keywords_source' => 'none'],
            self::ProductUniverse, self::CategoryShowcase => ['limit' => 'number'],
            self::ProductShowcase, self::ProductSlider => ['source' => 'product_source', 'category_id' => 'category', 'limit' => 'number'],
            self::ProductShelf => ['source' => 'product_source', 'limit' => 'number'],
            self::InsideSuru => ['limit' => 'number'],
            self::Journey, self::Timeline => ['group' => 'text'],
            self::Banners => ['placement' => 'text'],
            self::ImageText => ['media_id' => 'media', 'reverse' => 'boolean'],
            self::Gallery => ['media_ids' => 'media_multi'],
            self::Video => ['video_url' => 'text', 'poster_media_id' => 'media'],
            self::Faq => ['group' => 'text'],
            self::Recipes, self::Articles, self::Testimonials => ['limit' => 'number'],
            self::Cta => ['media_ids' => 'media_multi'],
            default => [],
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
