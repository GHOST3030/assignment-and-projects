<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Menu Items') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav />

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('admin.menu-items.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-md hover:bg-gray-700">
                        {{ __('New Menu Item') }}
                    </a>
                </div>

                @if ($menuItems->isEmpty())
                    <p class="text-gray-500">{{ __('No menu items yet.') }}</p>
                @else
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-sm text-gray-500">
                                <th class="py-2">{{ __('Name') }}</th>
                                <th class="py-2">{{ __('Category') }}</th>
                                <th class="py-2">{{ __('Price') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($menuItems as $item)
                                <tr>
                                    <td class="py-3 font-medium text-gray-900">{{ $item->name }}</td>
                                    <td class="py-3 text-gray-600">{{ $item->category->name }}</td>
                                    <td class="py-3 text-gray-600">${{ number_format($item->price, 2) }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $item->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $item->is_available ? __('Available') : __('Unavailable') }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right space-x-3">
                                        <a href="{{ route('admin.menu-items.edit', $item) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                        <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}" class="inline"
                                              onsubmit="return confirm('{{ __('Delete this menu item?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                        </form>
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
