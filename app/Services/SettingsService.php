<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Setting;
use App\Support\CacheGroups;
use App\Support\SettingsSchema;

class SettingsService
{
    private ?array $loaded = null;

    /** @return array<string, array<string, string|null>> key => [locale|'*' => value] */
    private function all(): array
    {
        return $this->loaded ??= CacheGroups::remember(CacheGroups::SETTINGS, 'all', function () {
            $out = [];
            foreach (Setting::all() as $row) {
                $out[$row->key][$row->locale ?? '*'] = $row->value;
            }

            return $out;
        });
    }

    public function get(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $values = $this->all()[$key] ?? [];
        $field = SettingsSchema::field($key);

        if ($field['translatable'] ?? false) {
            $locale ??= app()->getLocale();
            $value = $values[$locale] ?? null;
        } else {
            $value = $values['*'] ?? null;
        }

        return ($value === null || $value === '') ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function json(string $key, array $default = []): array
    {
        $value = $this->get($key);

        return $value ? (json_decode($value, true) ?: $default) : $default;
    }

    public function media(string $key): ?Media
    {
        $id = $this->get($key);

        return $id ? CacheGroups::remember(CacheGroups::SETTINGS, "media:{$id}", fn () => Media::with('translations')->find($id)) : null;
    }

    public function set(string $key, mixed $value, ?string $locale = null): void
    {
        $field = SettingsSchema::field($key);
        if (is_array($value)) {
            $value = json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
        }
        Setting::updateOrCreate(
            ['key' => $key, 'locale' => ($field['translatable'] ?? false) ? $locale : null],
            ['group' => $field['group'] ?? 'misc', 'value' => $value === null ? null : (string) $value]
        );
    }

    public function flush(): void
    {
        $this->loaded = null;
        CacheGroups::flush(CacheGroups::SETTINGS);
    }

    public function siteName(?string $locale = null): string
    {
        return (string) $this->get('site_name', config('app.name'), $locale);
    }

    public function phones(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $this->get('phones', '')))));
    }

    public function socials(): array
    {
        $out = [];
        foreach (array_keys(SettingsSchema::groups()['social']['fields']) as $network) {
            if ($url = $this->get($network)) {
                $out[$network] = $url;
            }
        }

        return $out;
    }

    public function commerceEnabled(): bool
    {
        return $this->bool('commerce_enabled');
    }

    public function showPrices(): bool
    {
        return $this->bool('show_prices', true);
    }

    /** CSS custom properties for the colour tokens that have been set. */
    public function themeCss(): string
    {
        $map = [
            'color_primary' => '--c-primary', 'color_secondary' => '--c-secondary', 'color_accent' => '--c-accent',
            'color_surface' => '--c-surface', 'color_surface_warm' => '--c-surface-warm', 'color_text' => '--c-text',
            'color_text_secondary' => '--c-text-2', 'color_border' => '--c-border',
        ];
        $css = '';
        foreach ($map as $key => $var) {
            $value = $this->get($key);
            if ($value && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                $css .= "{$var}:{$value};";
            }
        }

        return $css ? ":root{{$css}}" : '';
    }
}
