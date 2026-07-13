<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class Cart
{
    protected const SESSION_KEY = 'cart';

    protected const MIN_QUANTITY = 1;

    protected const MAX_QUANTITY = 20;

    public function items(): Collection
    {
        $quantities = session(self::SESSION_KEY, []);

        if (empty($quantities)) {
            return collect();
        }

        $menuItems = MenuItem::whereIn('id', array_keys($quantities))->get()->keyBy('id');

        return collect($quantities)
            ->map(function ($quantity, $menuItemId) use ($menuItems) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem) {
                    return null;
                }

                return [
                    'menuItem' => $menuItem,
                    'quantity' => $quantity,
                    'lineTotal' => $menuItem->price * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function add(MenuItem $menuItem, int $quantity = 1): void
    {
        $cart = session(self::SESSION_KEY, []);
        $current = $cart[$menuItem->id] ?? 0;
        $cart[$menuItem->id] = $this->clamp($current + $quantity);
        session([self::SESSION_KEY => $cart]);
    }

    public function update(int $menuItemId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);

        if (! isset($cart[$menuItemId])) {
            return;
        }

        $cart[$menuItemId] = $this->clamp($quantity);
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $menuItemId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$menuItemId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(session(self::SESSION_KEY, []));
    }

    public function total(): float
    {
        return (float) $this->items()->sum('lineTotal');
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    protected function clamp(int $quantity): int
    {
        return max(self::MIN_QUANTITY, min(self::MAX_QUANTITY, $quantity));
    }
}
