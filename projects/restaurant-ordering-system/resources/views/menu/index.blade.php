<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Category Filter -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('menu.index') }}"
                   class="px-4 py-2 rounded-full text-sm font-medium {{ is_null($selectedCategory) ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                    All
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('menu.index', ['category' => $category->id]) }}"
                       class="px-4 py-2 rounded-full text-sm font-medium {{ $selectedCategory?->id === $category->id ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            @php
                $categoriesToShow = $selectedCategory ? collect([$selectedCategory]) : $categories;
            @endphp

            @forelse ($categoriesToShow as $category)
                @if ($category->menuItems->isNotEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $category->name }}</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($category->menuItems as $item)
                                <a href="{{ route('menu.show', $item) }}"
                                   class="block border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-medium text-gray-900">{{ $item->name }}</h4>
                                        <span class="text-gray-900 font-semibold whitespace-nowrap">${{ number_format($item->price, 2) }}</span>
                                    </div>
                                    @if ($item->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $item->description }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-500">
                    {{ __('No menu items available right now.') }}
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
