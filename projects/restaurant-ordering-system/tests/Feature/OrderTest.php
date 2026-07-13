<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_checking_out(): void
    {
        $item = $this->makeItem();
        $this->post(route('cart.add', $item));

        $response = $this->get(route('checkout.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_place_an_order_from_their_cart(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();

        $this->actingAs($user)->post(route('cart.add', $item), ['quantity' => 3]);

        $response = $this->actingAs($user)->post(route('checkout.store'), ['notes' => 'No onions']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 30.00,
            'notes' => 'No onions',
        ]);

        $order = Order::first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $item->id,
            'quantity' => 3,
            'price' => 10.00,
        ]);

        $response->assertRedirect(route('orders.show', $order));

        // Cart should be cleared after checkout.
        $this->get(route('cart.index'))->assertSee('Your cart is empty');
    }

    public function test_cannot_checkout_with_an_empty_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('checkout.create'));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_view_their_order_history(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create(['status' => 'pending', 'total' => 15.00]);

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee("Order #{$order->id}");
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $owner->orders()->create(['status' => 'pending', 'total' => 15.00]);

        $response = $this->actingAs($intruder)->get(route('orders.show', $order));

        $response->assertForbidden();
    }

    public function test_customer_can_cancel_a_pending_order(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create(['status' => 'pending', 'total' => 15.00]);

        $response = $this->actingAs($user)->post(route('orders.cancel', $order));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_customer_cannot_cancel_an_order_that_is_already_preparing(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create(['status' => 'preparing', 'total' => 15.00]);

        $response = $this->actingAs($user)->post(route('orders.cancel', $order));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'preparing']);
    }
}
