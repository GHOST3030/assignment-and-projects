<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Your Cart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($items->isEmpty())
                    <p class="text-gray-500">
                        {{ __('Your cart is empty.') }}
                        <a href="{{ route('menu.index') }}" class="text-gray-900 underline">{{ __('Browse the menu') }}</a>
                    </p>
                @else
                    <x-input-error :messages="$errors->get('quantity')" class="mb-4" />

                    <div class="divide-y divide-gray-200">
                        @foreach ($items as $entry)
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $entry['menuItem']->name }}</h4>
                                    <p class="text-sm text-gray-500">${{ number_format($entry['menuItem']->price, 2) }} {{ __('each') }}</p>
                                </div>

                                <form method="POST" action="{{ route('cart.update', $entry['menuItem']) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $entry['quantity'] }}" min="1" max="20"
                                           class="w-16 rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                                    <button type="submit" class="px-2 py-1 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                        {{ __('Update') }}
                                    </button>
                                </form>

                                <span class="w-20 text-right font-semibold text-gray-900">${{ number_format($entry['lineTotal'], 2) }}</span>

                                <form method="POST" action="{{ route('cart.remove', $entry['menuItem']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        {{ __('Remove') }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-4">
                        <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}</span>
                        <span class="text-lg font-semibold text-gray-900">${{ number_format($total, 2) }}</span>
                    </div>

                    <div class="mt-6 text-right">
                        <a href="{{ route('checkout.create') }}" class="inline-block px-6 py-2 text-white bg-gray-900 rounded-md hover:bg-gray-700 font-medium">
                            {{ __('Proceed to Checkout') }}
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
