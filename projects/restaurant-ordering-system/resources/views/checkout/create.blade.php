<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Order Summary') }}</h3>

                <div class="divide-y divide-gray-200">
                    @foreach ($items as $entry)
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-gray-900">{{ $entry['menuItem']->name }}</span>
                                <span class="text-sm text-gray-500">&times; {{ $entry['quantity'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">${{ number_format($entry['lineTotal'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="text-lg font-semibold text-gray-900">${{ number_format($total, 2) }}</span>
                </div>

                <form method="POST" action="{{ route('checkout.store') }}" class="mt-6">
                    @csrf

                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes (optional)') }}</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                              placeholder="{{ __('Any special requests?') }}">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />

                    <button type="submit" class="mt-4 w-full px-6 py-3 text-white bg-gray-900 rounded-md hover:bg-gray-700 font-medium">
                        {{ __('Place Order') }}
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
