<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_buyer_can_start_a_conversation_and_seller_can_reply(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = $this->productFor($seller);

        $response = $this->actingAs($buyer)
            ->post(route('chats.start.product', $product));

        $conversation = Conversation::query()->firstOrFail();

        $response->assertRedirectToRoute('chats.show', $conversation);
        $this->assertDatabaseHas('conversations', [
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($buyer)
            ->get(route('chats.index'))
            ->assertOk()
            ->assertSee($seller->name)
            ->assertSee($product->name);

        $this->actingAs($buyer)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Apakah barang ini masih tersedia?'])
            ->assertRedirectToRoute('chats.show', $conversation);

        $message = Message::query()->firstOrFail();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Apakah barang ini masih tersedia?',
        ]);

        $this->actingAs($seller)
            ->get(route('chats.show', $conversation))
            ->assertOk()
            ->assertSee('Apakah barang ini masih tersedia?');

        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($seller)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Masih tersedia, silakan checkout.'])
            ->assertRedirectToRoute('chats.show', $conversation);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $seller->id,
            'body' => 'Masih tersedia, silakan checkout.',
        ]);
    }

    public function test_only_conversation_participants_can_open_or_send_messages(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $outsider = User::factory()->create();
        $product = $this->productFor($seller);
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($outsider)
            ->get(route('chats.show', $conversation))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Pesan tidak sah'])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('chats.start.product', $product))
            ->assertForbidden();
    }

    public function test_buyer_and_seller_reuse_the_same_conversation_from_an_order(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $product = $this->productFor($seller);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::Paid,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'price_snapshot' => $product->price,
            'qty' => 1,
        ]);

        $this->actingAs($buyer)
            ->post(route('chats.start.order', $order))
            ->assertRedirect();
        $this->assertDatabaseCount('conversations', 1);

        $this->actingAs($seller)
            ->post(route('chats.start.order', $order))
            ->assertRedirect();
        $this->assertDatabaseCount('conversations', 1);
    }

    private function productFor(User $seller): Product
    {
        return Product::factory()
            ->for($seller, 'seller')
            ->for(Category::factory())
            ->create(['status' => 'active', 'stock' => 1]);
    }
}
