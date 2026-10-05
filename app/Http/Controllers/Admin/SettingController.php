<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaService;
use App\Services\SettingsService;
use App\Support\Locales;
use App\Support\SettingsSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct(private SettingsService $settings) {}

    public function edit(?string $group = null)
    {
        $groups = SettingsSchema::groups();
        $group ??= array_key_first($groups);
        abort_unless(isset($groups[$group]), 404);

        return view('admin.settings.edit', ['groups' => $groups, 'group' => $group, 'settings' => $this->settings]);
    }

    public function update(Request $request, string $group, ActivityLogger $logger)
    {
        $def = SettingsSchema::groups()[$group] ?? abort(404);
        $rules = [];
        foreach ($def['fields'] as $key => $field) {
            $rule = match ($field['type']) {
                'color' => ['nullable', 'hex_color'],
                'url' => ['nullable', 'url', 'max:500'],
                'number' => ['nullable', 'integer', 'min:0'],
                'media' => ['nullable', 'integer', 'exists:media,id'],
                'boolean' => ['nullable', 'boolean'],
                'checkboxes' => ['nullable', 'array'],
                'code' => ['nullable', 'string', 'max:20000'],
                default => ['nullable', 'string', 'max:5000'],
            };
            if ($field['translatable'] ?? false) {
                foreach (Locales::codes() as $l) {
                    $rules["{$key}.{$l}"] = $rule;
                }
            } else {
                $rules[$key] = $rule;
            }
        }
        $data = $request->validate($rules);

        foreach ($def['fields'] as $key => $field) {
            if ($field['translatable'] ?? false) {
                foreach (Locales::codes() as $l) {
                    $this->settings->set($key, $data[$key][$l] ?? null, $l);
                }
            } elseif ($field['type'] === 'boolean') {
                $this->settings->set($key, $request->boolean($key) ? '1' : '0');
            } elseif ($field['type'] === 'checkboxes') {
                $this->settings->set($key, array_values(array_intersect($data[$key] ?? [], array_keys($field['options']))));
            } else {
                $this->settings->set($key, $data[$key] ?? null);
            }
        }
        $this->settings->flush();
        $logger->log('settings', null, 'تنظیمات: '.$def['label']);

        return back()->with('success', 'تنظیمات ذخیره شد.');
    }

    /**
     * Derives the colour tokens from the real logo (or a chosen image): the
     * palette extracted from the brand artwork becomes Primary / Secondary /
     * Accent, and warm surfaces are mixed from the primary hue.
     */
    public function extractPalette(Request $request, MediaService $media)
    {
        $id = $request->integer('media_id') ?: (int) $this->settings->get('logo_media_id');
        $item = $id ? Media::find($id) : null;
        if (! $item || ! $item->isImage() || $item->isSvg()) {
            return back()->with('error', 'ابتدا لوگوی رستری (PNG/JPG/WebP) را در بخش «هویت برند» انتخاب کنید.');
        }

        [, $palette] = $media->extractPalette(Storage::disk($item->disk)->get($item->path()));
        if (count($palette) < 2) {
            return back()->with('error', 'رنگ کافی در تصویر پیدا نشد.');
        }

        $byDarkness = collect($palette)->sortBy(fn ($hex) => $this->luminance($hex))->values();
        $primary = $byDarkness->first();
        $accent = collect($palette)->sortByDesc(fn ($hex) => $this->saturation($hex))->first(fn ($hex) => $hex !== $primary) ?? $palette[1];
        $secondary = collect($palette)->first(fn ($hex) => ! in_array($hex, [$primary, $accent], true)) ?? $accent;

        $this->settings->set('color_primary', $primary);
        $this->settings->set('color_accent', $accent);
        $this->settings->set('color_secondary', $secondary);
        $this->settings->set('color_surface', $this->mix($primary, '#fbf8f2', 0.05));
        $this->settings->set('color_surface_warm', $this->mix($accent, '#f3ece0', 0.12));
        $this->settings->flush();

        return back()->with('success', 'پالت از تصویر برند استخراج شد: '.implode('، ', $palette).' — در صورت نیاز دستی اصلاح کنید.');
    }

    private function rgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }

    private function luminance(string $hex): float
    {
        [$r, $g, $b] = $this->rgb($hex);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function saturation(string $hex): float
    {
        $c = $this->rgb($hex);

        return max($c) === 0 ? 0 : (max($c) - min($c)) / max($c);
    }

    private function mix(string $a, string $b, float $weight): string
    {
        $x = $this->rgb($a);
        $y = $this->rgb($b);

        return sprintf('#%02x%02x%02x', ...array_map(fn ($i) => round($x[$i] * $weight + $y[$i] * (1 - $weight)), [0, 1, 2]));
    }
}
