<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_menu(): void
    {
        $category = Category::create(['name' => 'Mains']);
        $item = $category->menuItems()->create([
            'name' => 'Grilled Chicken',
            'description' => 'Tasty chicken',
            'price' => 12.50,
            'is_available' => true,
        ]);

        $response = $this->get(route('menu.index'));

        $response->assertStatus(200);
        $response->assertSee('Mains');
        $response->assertSee('Grilled Chicken');
    }

    public function test_unavailable_items_are_hidden_from_the_menu(): void
    {
        $category = Category::create(['name' => 'Mains']);
        $category->menuItems()->create([
            'name' => 'Sold Out Dish',
            'price' => 9.00,
            'is_available' => false,
        ]);

        $response = $this->get(route('menu.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Sold Out Dish');
    }

    public function test_menu_can_be_filtered_by_category(): void
    {
        $mains = Category::create(['name' => 'Mains']);
        $drinks = Category::create(['name' => 'Drinks']);

        $mains->menuItems()->create(['name' => 'Burger', 'price' => 10, 'is_available' => true]);
        $drinks->menuItems()->create(['name' => 'Soda', 'price' => 2, 'is_available' => true]);

        $response = $this->get(route('menu.index', ['category' => $mains->id]));

        $response->assertStatus(200);
        $response->assertSee('Burger');
        $response->assertDontSee('Soda');
    }

    public function test_guest_can_view_a_menu_item_detail_page(): void
    {
        $category = Category::create(['name' => 'Desserts']);
        $item = $category->menuItems()->create([
            'name' => 'Cheesecake',
            'description' => 'Classic New York style.',
            'price' => 6.00,
            'is_available' => true,
        ]);

        $response = $this->get(route('menu.show', $item));

        $response->assertStatus(200);
        $response->assertSee('Cheesecake');
        $response->assertSee('Classic New York style.');
    }

    public function test_unavailable_item_shows_a_badge_on_its_detail_page(): void
    {
        $category = Category::create(['name' => 'Desserts']);
        $item = $category->menuItems()->create([
            'name' => 'Cheesecake',
            'price' => 6.00,
            'is_available' => false,
        ]);

        $response = $this->get(route('menu.show', $item));

        $response->assertStatus(200);
        $response->assertSee('Currently unavailable');
    }
}
