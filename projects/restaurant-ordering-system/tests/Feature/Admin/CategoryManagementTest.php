<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_a_category(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Desserts']);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Desserts']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::create(['name' => 'Desserts']);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Desserts']);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = Category::create(['name' => 'Drinks']);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Beverages']);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Beverages']);
    }

    public function test_admin_can_delete_an_empty_category(): void
    {
        $category = Category::create(['name' => 'Drinks']);

        $this->actingAs($this->admin())->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_menu_items_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Drinks']);
        $category->menuItems()->create(['name' => 'Soda', 'price' => 2, 'is_available' => true]);

        $this->actingAs($this->admin())->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
