<x-mail::message>
# Order #{{ $order->id }} Update

Hi {{ $order->user->name }},

Your order is now **{{ ucfirst($order->status) }}**.

<x-mail::table>
| Item | Qty | Price |
| :--- | :-: | ----: |
@foreach ($order->items as $item)
| {{ $item->menuItem->name }} | {{ $item->quantity }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
</x-mail::table>

**Total: ${{ number_format($order->total, 2) }}**

<x-mail::button :url="route('orders.show', $order)">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
