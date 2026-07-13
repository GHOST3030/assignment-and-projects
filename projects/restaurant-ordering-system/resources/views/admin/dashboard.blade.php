<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav />

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __("Today's Orders") }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $todayOrdersCount }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __("Today's Revenue") }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($todayRevenue, 2) }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __('Most Ordered Item') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $mostOrderedItem?->name ?? __('N/A') }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500">{{ __('Menu Items') }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $menuItemCount }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
