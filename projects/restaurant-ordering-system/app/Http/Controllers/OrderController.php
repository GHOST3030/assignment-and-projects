<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(Cart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('checkout.create', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    public function store(StoreOrderRequest $request, Cart $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validated();
        $items = $cart->items();

        foreach ($items as $entry) {
            if (! $entry['menuItem']->is_available) {
                return redirect()->route('cart.index')
                    ->with('error', "{$entry['menuItem']->name} is no longer available. Please remove it from your cart.");
            }
        }

        $order = DB::transaction(function () use ($request, $items, $validated) {
            $order = $request->user()->orders()->create([
                'status' => 'pending',
                'total' => $items->sum('lineTotal'),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $entry) {
                $order->items()->create([
                    'menu_item_id' => $entry['menuItem']->id,
                    'quantity' => $entry['quantity'],
                    'price' => $entry['menuItem']->price,
                ]);
            }

            return $order;
        });

        $cart->clear();

        return redirect()->route('orders.show', $order)->with('success', "Order placed! Order #{$order->id}");
    }

    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->get();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load('items.menuItem');

        return view('orders.show', ['order' => $order]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        if (! $order->isCancellable()) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        Mail::to($order->user)->send(new OrderStatusUpdated($order->load('items.menuItem')));

        return back()->with('success', 'Order cancelled.');
    }
}
