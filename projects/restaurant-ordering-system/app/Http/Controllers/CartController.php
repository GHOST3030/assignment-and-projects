<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\MenuItem;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
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

    public function add(AddToCartRequest $request, Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        if (! $menuItem->is_available) {
            return back()->with('error', "{$menuItem->name} is currently unavailable.");
        }

        $cart->add($menuItem, $request->validated('quantity') ?? 1);

        return back()->with('success', "{$menuItem->name} added to your cart.");
    }

    public function update(UpdateCartRequest $request, Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        $cart->update($menuItem->id, $request->validated('quantity'));

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Cart $cart, MenuItem $menuItem): RedirectResponse
    {
        $cart->remove($menuItem->id);

        return back()->with('success', "{$menuItem->name} removed from your cart.");
    }
}
