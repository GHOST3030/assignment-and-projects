<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    protected const NEXT_STATUS = [
        'pending' => 'preparing',
        'preparing' => 'ready',
        'ready' => 'delivered',
    ];

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $orders = Order::with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'selectedStatus' => $status,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function advance(Order $order): RedirectResponse
    {
        $next = self::NEXT_STATUS[$order->status] ?? null;

        if (! $next) {
            return back()->with('error', 'This order has no further status to advance to.');
        }

        $order->update(['status' => $next]);

        return back()->with('success', "Order #{$order->id} marked as {$next}.");
    }
}
