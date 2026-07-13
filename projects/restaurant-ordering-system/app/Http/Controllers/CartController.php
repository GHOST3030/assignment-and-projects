<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        return view('cart.index', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    public function add(Request $request, Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        if (! $menuItem->is_available) {
            return back()->with('error', "{$menuItem->name} is currently unavailable.");
        }

        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $cart->add($menuItem, $validated['quantity'] ?? 1);

        return back()->with('success', "{$menuItem->name} added to your cart.");
    }

    public function update(Request $request, Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cart->update($menuItem->id, $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        $cart->remove($menuItem->id);

        return back()->with('success', "{$menuItem->name} removed from your cart.");
    }
}
