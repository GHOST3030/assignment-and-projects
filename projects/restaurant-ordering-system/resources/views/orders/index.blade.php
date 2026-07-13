<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($orders->isEmpty())
                    <p class="text-gray-500">
                        {{ __("You haven't placed any orders yet.") }}
                        <a href="{{ route('menu.index') }}" class="text-gray-900 underline">{{ __('Browse the menu') }}</a>
                    </p>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <a href="{{ route('orders.show', $order) }}" class="py-4 flex justify-between items-center hover:bg-gray-50 -mx-6 px-6">
                                <div>
                                    <span class="font-medium text-gray-900">{{ __('Order') }} #{{ $order->id }}</span>
                                    <span class="text-sm text-gray-500 block">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                        @class([
                                            'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                            'bg-blue-100 text-blue-800' => $order->status === 'preparing',
                                            'bg-purple-100 text-purple-800' => $order->status === 'ready',
                                            'bg-green-100 text-green-800' => $order->status === 'delivered',
                                            'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                        ])">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                    <span class="font-semibold text-gray-900 block mt-1">${{ number_format($order->total, 2) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
