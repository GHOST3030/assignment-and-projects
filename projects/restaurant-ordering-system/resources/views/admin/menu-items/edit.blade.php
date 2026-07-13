<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Menu Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <x-admin-nav />

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($menuItem->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($menuItem->image) }}" alt="{{ $menuItem->name }}" class="w-32 h-32 object-cover rounded-md mb-4">
                @endif

                <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <x-input-label for="category_id" :value="__('Category')" />
                    <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $menuItem->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />

                    <x-input-label for="name" :value="__('Name')" class="mt-4" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $menuItem->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />

                    <x-input-label for="description" :value="__('Description')" class="mt-4" />
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('description', $menuItem->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />

                    <x-input-label for="price" :value="__('Price')" class="mt-4" />
                    <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $menuItem->price)" required />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />

                    <x-input-label for="image" :value="__('Replace Image')" class="mt-4" />
                    <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />

                    <div class="mt-4 flex items-center gap-2">
                        <input id="is_available" name="is_available" type="checkbox" value="1" @checked(old('is_available', $menuItem->is_available))
                               class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-500">
                        <x-input-label for="is_available" :value="__('Available')" />
                    </div>

                    <div class="mt-6 flex gap-3">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('admin.menu-items.index') }}" class="text-sm text-gray-600 self-center hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
