<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuItemManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_a_menu_item_with_an_image(): void
    {
        Storage::fake('public');
        $category = Category::create(['name' => 'Mains']);

        $response = $this->actingAs($this->admin())->post(route('admin.menu-items.store'), [
            'category_id' => $category->id,
            'name' => 'Burger',
            'description' => 'Juicy beef burger',
            'price' => 10.50,
            'is_available' => '1',
            'image' => UploadedFile::fake()->image('burger.jpg'),
        ]);

        $response->assertRedirect(route('admin.menu-items.index'));
        $this->assertDatabaseHas('menu_items', [
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => 10.50,
            'is_available' => true,
        ]);

        $menuItem = $category->menuItems()->first();
        Storage::disk('public')->assertExists($menuItem->image);
    }

    public function test_admin_can_toggle_availability(): void
    {
        $category = Category::create(['name' => 'Mains']);
        $item = $category->menuItems()->create(['name' => 'Burger', 'price' => 10, 'is_available' => true]);

        $this->actingAs($this->admin())->put(route('admin.menu-items.update', $item), [
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => 10,
        ]);

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'is_available' => false]);
    }

    public function test_admin_can_delete_a_menu_item(): void
    {
        $category = Category::create(['name' => 'Mains']);
        $item = $category->menuItems()->create(['name' => 'Burger', 'price' => 10, 'is_available' => true]);

        $this->actingAs($this->admin())->delete(route('admin.menu-items.destroy', $item));

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_price_must_be_a_non_negative_number(): void
    {
        $category = Category::create(['name' => 'Mains']);

        $response = $this->actingAs($this->admin())->post(route('admin.menu-items.store'), [
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => -5,
        ]);

        $response->assertSessionHasErrors('price');
    }
}
