<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_only_sees_own_order_history_and_cannot_open_another_buyers_order(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $ownOrder = Order::factory()->create(['buyer_id' => $buyer->id, 'order_number' => 'ST-MILIK-SAYA']);
        $otherOrder = Order::factory()->create(['buyer_id' => $otherBuyer->id, 'order_number' => 'ST-MILIK-ORANG-LAIN']);

        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('ST-MILIK-SAYA')
            ->assertDontSee('ST-MILIK-ORANG-LAIN');

        $this->actingAs($buyer)->get(route('orders.show', $otherOrder))->assertForbidden();
        $this->actingAs($buyer)->get(route('orders.show', $ownOrder))->assertOk();
    }
}
