<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('admin.dashboard') }}"
       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
        {{ __('Dashboard') }}
    </a>
    <a href="{{ route('admin.menu-items.index') }}"
       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.menu-items.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
        {{ __('Menu Items') }}
    </a>
    <a href="{{ route('admin.categories.index') }}"
       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.categories.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
        {{ __('Categories') }}
    </a>
    <a href="{{ route('admin.orders.index') }}"
       class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.orders.*') ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
        {{ __('Orders') }}
    </a>
</div>
