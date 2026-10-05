<?php

namespace App\Support;

/**
 * Every editable site setting. `translatable` settings are stored once per
 * locale; the rest are stored with a null locale. Values start empty — brand
 * data comes from the Suru Food importer or the admin, never from code.
 */
class SettingsSchema
{
    public static function groups(): array
    {
        return [
            'brand' => [
                'label' => 'هویت برند',
                'fields' => [
                    'site_name' => ['label' => 'نام سایت', 'type' => 'text', 'translatable' => true],
                    'tagline' => ['label' => 'شعار برند', 'type' => 'text', 'translatable' => true],
                    'short_about' => ['label' => 'معرفی کوتاه (فوتر)', 'type' => 'textarea', 'translatable' => true],
                    'logo_media_id' => ['label' => 'لوگو', 'type' => 'media'],
                    'logo_light_media_id' => ['label' => 'لوگوی روشن (روی تصویر)', 'type' => 'media'],
                    'favicon_media_id' => ['label' => 'فاوآیکن', 'type' => 'media'],
                    'og_media_id' => ['label' => 'تصویر پیش‌فرض اشتراک‌گذاری', 'type' => 'media'],
                    'founded_year' => ['label' => 'سال تأسیس', 'type' => 'text'],
                    'legal_name' => ['label' => 'نام حقوقی شرکت', 'type' => 'text', 'translatable' => true],
                ],
            ],
            'theme' => [
                'label' => 'رنگ‌ها (Design Tokens)',
                'fields' => [
                    'color_primary' => ['label' => 'Primary', 'type' => 'color'],
                    'color_secondary' => ['label' => 'Secondary', 'type' => 'color'],
                    'color_accent' => ['label' => 'Accent', 'type' => 'color'],
                    'color_surface' => ['label' => 'Surface', 'type' => 'color'],
                    'color_surface_warm' => ['label' => 'Surface Warm', 'type' => 'color'],
                    'color_text' => ['label' => 'Text Primary', 'type' => 'color'],
                    'color_text_secondary' => ['label' => 'Text Secondary', 'type' => 'color'],
                    'color_border' => ['label' => 'Border', 'type' => 'color'],
                ],
            ],
            'contact' => [
                'label' => 'اطلاعات تماس',
                'fields' => [
                    'phones' => ['label' => 'شماره‌های تماس (هر خط یک شماره)', 'type' => 'textarea'],
                    'email' => ['label' => 'ایمیل', 'type' => 'text'],
                    'address' => ['label' => 'آدرس', 'type' => 'textarea', 'translatable' => true],
                    'factory_address' => ['label' => 'آدرس کارخانه', 'type' => 'textarea', 'translatable' => true],
                    'working_hours' => ['label' => 'ساعات کاری', 'type' => 'text', 'translatable' => true],
                    'map_lat' => ['label' => 'عرض جغرافیایی نقشه', 'type' => 'text'],
                    'map_lng' => ['label' => 'طول جغرافیایی نقشه', 'type' => 'text'],
                    'map_embed_url' => ['label' => 'آدرس Embed نقشه (اختیاری)', 'type' => 'text'],
                ],
            ],
            'social' => [
                'label' => 'شبکه‌های اجتماعی',
                'fields' => [
                    'instagram' => ['label' => 'Instagram', 'type' => 'url'],
                    'telegram' => ['label' => 'Telegram', 'type' => 'url'],
                    'whatsapp' => ['label' => 'WhatsApp', 'type' => 'url'],
                    'linkedin' => ['label' => 'LinkedIn', 'type' => 'url'],
                    'aparat' => ['label' => 'Aparat', 'type' => 'url'],
                    'youtube' => ['label' => 'YouTube', 'type' => 'url'],
                    'x' => ['label' => 'X / Twitter', 'type' => 'url'],
                    'eitaa' => ['label' => 'Eitaa', 'type' => 'url'],
                    'bale' => ['label' => 'Bale', 'type' => 'url'],
                ],
            ],
            'header' => [
                'label' => 'هدر و نوار بالا',
                'fields' => [
                    'topbar_enabled' => ['label' => 'نمایش نوار بالا', 'type' => 'boolean'],
                    'topbar_text' => ['label' => 'متن نوار بالا', 'type' => 'text', 'translatable' => true],
                    'topbar_link' => ['label' => 'لینک نوار بالا', 'type' => 'text', 'translatable' => true],
                    'topbar_secondary' => ['label' => 'متن دوم (مثلاً پشتیبانی)', 'type' => 'text', 'translatable' => true],
                    'mega_promo_title' => ['label' => 'مگامنو: عنوان بخش داستان/تبلیغ', 'type' => 'text', 'translatable' => true],
                    'mega_promo_text' => ['label' => 'مگامنو: متن', 'type' => 'textarea', 'translatable' => true],
                    'mega_promo_url' => ['label' => 'مگامنو: لینک', 'type' => 'text', 'translatable' => true],
                    'mega_promo_media_id' => ['label' => 'مگامنو: تصویر', 'type' => 'media'],
                    'mega_featured_ids' => ['label' => 'مگامنو: شناسه محصولات ویژه (با کاما)', 'type' => 'text'],
                ],
            ],
            'footer' => [
                'label' => 'فوتر و CTA پایانی',
                'fields' => [
                    'footer_cta_title' => ['label' => 'عنوان CTA پیش از فوتر', 'type' => 'text', 'translatable' => true],
                    'footer_cta_text' => ['label' => 'متن CTA', 'type' => 'textarea', 'translatable' => true],
                    'footer_cta_label' => ['label' => 'برچسب دکمه CTA', 'type' => 'text', 'translatable' => true],
                    'footer_cta_url' => ['label' => 'لینک CTA', 'type' => 'text', 'translatable' => true],
                    'footer_cta_product_ids' => ['label' => 'محصولات نمایش داده‌شده در CTA (شناسه‌ها با کاما)', 'type' => 'text'],
                    'copyright' => ['label' => 'متن کپی‌رایت', 'type' => 'text', 'translatable' => true],
                    'trust_badges_html' => ['label' => 'کد نماد اعتماد (eNamad و ...)', 'type' => 'code'],
                ],
            ],
            'commerce' => [
                'label' => 'فروشگاه',
                'fields' => [
                    'commerce_enabled' => ['label' => 'فعال بودن فروش آنلاین', 'type' => 'boolean'],
                    'show_prices' => ['label' => 'نمایش قیمت‌ها', 'type' => 'boolean'],
                    'shipping_flat' => ['label' => 'هزینه ارسال ثابت (تومان)', 'type' => 'number'],
                    'free_shipping_over' => ['label' => 'ارسال رایگان بالای (تومان)', 'type' => 'number'],
                    'payment_methods' => ['label' => 'روش‌های پرداخت فعال', 'type' => 'checkboxes', 'options' => ['cod' => 'پرداخت در محل', 'bank_transfer' => 'کارت‌به‌کارت / واریز']],
                    'bank_transfer_info' => ['label' => 'اطلاعات واریز', 'type' => 'textarea', 'translatable' => true],
                    'order_notify_email' => ['label' => 'ایمیل اطلاع‌رسانی سفارش جدید', 'type' => 'text'],
                ],
            ],
            'seo' => [
                'label' => 'سئو و آنالیتیکس',
                'fields' => [
                    'title_suffix' => ['label' => 'پسوند عنوان صفحات', 'type' => 'text', 'translatable' => true],
                    'default_description' => ['label' => 'توضیح پیش‌فرض', 'type' => 'textarea', 'translatable' => true],
                    'robots_extra' => ['label' => 'قوانین اضافه robots.txt', 'type' => 'code'],
                    'head_scripts' => ['label' => 'کد اضافه در head (آنالیتیکس)', 'type' => 'code'],
                    'contact_notify_email' => ['label' => 'ایمیل دریافت پیام‌های تماس', 'type' => 'text'],
                ],
            ],
        ];
    }

    public static function field(string $key): ?array
    {
        foreach (self::groups() as $group => $def) {
            if (isset($def['fields'][$key])) {
                return $def['fields'][$key] + ['group' => $group];
            }
        }

        return null;
    }
}
