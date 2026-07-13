<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $menuItem->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('menu.index', ['category' => $menuItem->category_id]) }}" class="text-sm text-gray-500 hover:text-gray-700">
                    &larr; {{ $menuItem->category->name }}
                </a>

                <div class="mt-4 flex justify-between items-start gap-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $menuItem->name }}</h1>
                    <span class="text-2xl font-semibold text-gray-900 whitespace-nowrap">${{ number_format($menuItem->price, 2) }}</span>
                </div>

                @unless ($menuItem->is_available)
                    <span class="inline-block mt-3 px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-full">
                        {{ __('Currently unavailable') }}
                    </span>
                @endunless

                @if ($menuItem->description)
                    <p class="mt-4 text-gray-600">{{ $menuItem->description }}</p>
                @endif

                @if ($menuItem->is_available)
                    <form method="POST" action="{{ route('cart.add', $menuItem) }}" class="mt-6 flex items-center gap-3">
                        @csrf
                        <label for="quantity" class="text-sm font-medium text-gray-700">{{ __('Quantity') }}</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="20"
                               class="w-20 rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-700">
                            {{ __('Add to Cart') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
