<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function makeItem(array $overrides = []): MenuItem
    {
        $category = Category::create(['name' => 'Mains']);

        return $category->menuItems()->create(array_merge([
            'name' => 'Burger',
            'price' => 10.00,
            'is_available' => true,
        ], $overrides));
    }

    public function test_guest_can_add_an_item_to_the_cart(): void
    {
        $item = $this->makeItem();

        $response = $this->post(route('cart.add', $item), ['quantity' => 2]);

        $response->assertRedirect();
        $this->get(route('cart.index'))
            ->assertSee('Burger')
            ->assertSee('20.00');
    }

    public function test_unavailable_item_cannot_be_added_to_the_cart(): void
    {
        $item = $this->makeItem(['is_available' => false]);

        $this->post(route('cart.add', $item));

        $this->get(route('cart.index'))->assertSee('Your cart is empty');
    }

    public function test_quantity_is_clamped_between_one_and_twenty(): void
    {
        $item = $this->makeItem();

        $response = $this->post(route('cart.add', $item), ['quantity' => 50]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_cart_quantity_can_be_updated(): void
    {
        $item = $this->makeItem();
        $this->post(route('cart.add', $item), ['quantity' => 1]);

        $this->patch(route('cart.update', $item), ['quantity' => 5]);

        $this->get(route('cart.index'))->assertSee('50.00');
    }

    public function test_item_can_be_removed_from_the_cart(): void
    {
        $item = $this->makeItem();
        $this->post(route('cart.add', $item), ['quantity' => 1]);

        $this->delete(route('cart.remove', $item));

        $this->get(route('cart.index'))->assertSee('Your cart is empty');
    }
}
