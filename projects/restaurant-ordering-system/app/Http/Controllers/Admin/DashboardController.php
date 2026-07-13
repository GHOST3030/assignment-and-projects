<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        $todayOrders = Order::whereDate('created_at', $today);

        $mostOrderedItem = OrderItem::query()
            ->selectRaw('menu_item_id, SUM(quantity) as total_quantity')
            ->groupBy('menu_item_id')
            ->orderByDesc('total_quantity')
            ->with('menuItem')
            ->first();

        return view('admin.dashboard', [
            'todayOrdersCount' => (clone $todayOrders)->count(),
            'todayRevenue' => (clone $todayOrders)->whereNotIn('status', ['cancelled'])->sum('total'),
            'mostOrderedItem' => $mostOrderedItem?->menuItem,
            'menuItemCount' => MenuItem::count(),
        ]);
    }
}
