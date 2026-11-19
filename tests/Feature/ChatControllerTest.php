<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_start_product_chat_and_send_a_message(): void
    {
        $this->withoutVite();
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create();

        $this->actingAs($buyer)
            ->post(route('chats.start.product', $product))
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();
        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($buyer)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Apakah produk masih tersedia?'])
            ->assertRedirectToRoute('chats.show', $conversation);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Apakah produk masih tersedia?',
        ]);

        $this->actingAs($seller)
            ->get(route('chats.show', $conversation))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Apakah produk masih tersedia?');
    }

    public function test_non_participant_cannot_read_a_conversation(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $outsider = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create();
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($outsider)->get(route('chats.show', $conversation))->assertForbidden();
    }
}
