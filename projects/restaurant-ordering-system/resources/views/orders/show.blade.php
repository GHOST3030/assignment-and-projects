<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order') }} #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-between items-center mb-4">
                    <span class="inline-block px-3 py-1 text-sm font-medium rounded-full
                        @class([
                            'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                            'bg-blue-100 text-blue-800' => $order->status === 'preparing',
                            'bg-purple-100 text-purple-800' => $order->status === 'ready',
                            'bg-green-100 text-green-800' => $order->status === 'delivered',
                            'bg-red-100 text-red-800' => $order->status === 'cancelled',
                        ])">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach ($order->items as $orderItem)
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-gray-900">{{ $orderItem->menuItem->name }}</span>
                                <span class="text-sm text-gray-500">&times; {{ $orderItem->quantity }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">${{ number_format($orderItem->price * $orderItem->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-between items-center border-t border-gray-200 pt-4">
                    <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="text-lg font-semibold text-gray-900">${{ number_format($order->total, 2) }}</span>
                </div>

                @if ($order->notes)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700">{{ __('Notes') }}</h4>
                        <p class="text-gray-600">{{ $order->notes }}</p>
                    </div>
                @endif

                @if ($order->isCancellable())
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-6"
                          onsubmit="return confirm('{{ __('Cancel this order?') }}');">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100">
                            {{ __('Cancel Order') }}
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
