<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $categories = Category::with(['menuItems' => function ($query) use ($search) {
            $query->where('is_available', true)
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name');
        }])->orderBy('name')->get();

        $selectedCategory = null;

        if ($request->filled('category')) {
            $selectedCategory = $categories->firstWhere('id', (int) $request->input('category'));
        }

        return view('menu.index', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
        ]);
    }

    public function show(MenuItem $menuItem): View
    {
        $menuItem->load('category');

        return view('menu.show', [
            'menuItem' => $menuItem,
        ]);
    }
}
