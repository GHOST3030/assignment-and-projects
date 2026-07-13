<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav />

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-3 py-1.5 rounded-full text-sm font-medium {{ is_null($selectedStatus) ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                        {{ __('All') }}
                    </a>
                    @foreach ($statuses as $status)
                        <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
                           class="px-3 py-1.5 rounded-full text-sm font-medium {{ $selectedStatus === $status ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                            {{ ucfirst($status) }}
                        </a>
                    @endforeach
                </div>

                @if ($orders->isEmpty())
                    <p class="text-gray-500">{{ __('No orders found.') }}</p>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-sm text-gray-500">
                                <th class="py-2">{{ __('Order') }}</th>
                                <th class="py-2">{{ __('Customer') }}</th>
                                <th class="py-2">{{ __('Total') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="py-3 font-medium text-gray-900">#{{ $order->id }}</td>
                                    <td class="py-3 text-gray-600">{{ $order->user->name }}</td>
                                    <td class="py-3 text-gray-600">${{ number_format($order->total, 2) }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @class([
                                                'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                                'bg-blue-100 text-blue-800' => $order->status === 'preparing',
                                                'bg-purple-100 text-purple-800' => $order->status === 'ready',
                                                'bg-green-100 text-green-800' => $order->status === 'delivered',
                                                'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                            ])">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        @if (in_array($order->status, ['pending', 'preparing', 'ready']))
                                            <form method="POST" action="{{ route('admin.orders.advance', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                                    {{ __('Advance') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
