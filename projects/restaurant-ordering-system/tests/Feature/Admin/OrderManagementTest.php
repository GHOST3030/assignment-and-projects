<?php

namespace Tests\Feature\Admin;

use App\Mail\OrderStatusUpdated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_the_orders_board(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create(['status' => 'pending', 'total' => 20]);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee("#{$order->id}");
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        $customer = User::factory()->create();
        $customer->orders()->create(['status' => 'pending', 'total' => 10]);
        $customer->orders()->create(['status' => 'delivered', 'total' => 15]);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.index', ['status' => 'delivered']));

        $response->assertStatus(200);
        $response->assertDontSee('$10.00');
    }

    public function test_admin_can_advance_an_order_status(): void
    {
        Mail::fake();

        $customer = User::factory()->create();
        $order = $customer->orders()->create(['status' => 'pending', 'total' => 20]);

        $response = $this->actingAs($this->admin())->patch(route('admin.orders.advance', $order));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'preparing']);
        Mail::assertSent(OrderStatusUpdated::class, fn ($mail) => $mail->hasTo($customer->email));
    }

    public function test_cancelled_orders_cannot_be_advanced(): void
    {
        $customer = User::factory()->create();
        $order = $customer->orders()->create(['status' => 'cancelled', 'total' => 20]);

        $this->actingAs($this->admin())->patch(route('admin.orders.advance', $order));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }
}
