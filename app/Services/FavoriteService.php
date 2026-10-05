<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Product;

/** Favourites live in the session for guests and in the database for members; they merge on login. */
class FavoriteService
{
    private const SESSION_KEY = 'favorites';

    public function ids(): array
    {
        if ($user = auth()->user()) {
            return $user->favorites()->pluck('product_id')->all();
        }

        return array_map('intval', session(self::SESSION_KEY, []));
    }

    public function toggle(Product $product): bool
    {
        if ($user = auth()->user()) {
            $existing = Favorite::where('user_id', $user->id)->where('product_id', $product->id)->first();
            if ($existing) {
                $existing->delete();

                return false;
            }
            Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

            return true;
        }

        $ids = $this->ids();
        if (in_array($product->id, $ids, true)) {
            session([self::SESSION_KEY => array_values(array_diff($ids, [$product->id]))]);

            return false;
        }
        session([self::SESSION_KEY => array_slice(array_merge($ids, [$product->id]), -200)]);

        return true;
    }

    public function mergeSessionInto(int $userId): void
    {
        foreach (array_map('intval', session(self::SESSION_KEY, [])) as $productId) {
            if (Product::whereKey($productId)->exists()) {
                Favorite::firstOrCreate(['user_id' => $userId, 'product_id' => $productId]);
            }
        }
        session()->forget(self::SESSION_KEY);
    }
}
