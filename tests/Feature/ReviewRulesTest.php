<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRulesTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $prefix): User
    {
        return User::create([
            'name' => ucfirst($prefix),
            'email' => $prefix.uniqid().'@t.com',
            'password' => 'password123',
            'role' => 'buyer',
            'is_active' => true,
        ]);
    }

    private function makeOrder(OrderStatus $status): array
    {
        $seller = $this->makeUser('seller');
        $buyer = $this->makeUser('buyer');
        $category = Category::create(['name' => 'Laptop', 'slug' => 'laptop-'.uniqid(), 'is_active' => true]);
        $product = Product::create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Tes',
            'price' => 1000000,
            'condition' => 'Bekas baik',
            'weight' => 1000,
            'location' => 'Jember',
            'stock' => 1,
            'status' => 'sold',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-TEST-'.uniqid(),
            'subtotal' => 1000000,
            'service_fee' => 0,
            'shipping_cost' => 0,
            'total' => 1000000,
            'status' => $status,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price_snapshot' => 1000000,
            'qty' => 1,
        ]);

        return [$buyer, $order];
    }

    public function test_buyer_can_review_completed_order(): void
    {
        [$buyer, $order] = $this->makeOrder(OrderStatus::Completed);

        $this->actingAs($buyer)
            ->post(route('orders.review.store', $order), ['rating' => 5, 'comment' => 'Bagus'])
            ->assertRedirect();

        $this->assertSame(1, Review::where('order_id', $order->id)->count());
    }

    public function test_cannot_review_before_completed(): void
    {
        [$buyer, $order] = $this->makeOrder(OrderStatus::Shipped);

        $this->actingAs($buyer)
            ->post(route('orders.review.store', $order), ['rating' => 5])
            ->assertStatus(422);

        $this->assertSame(0, Review::count());
    }

    public function test_only_order_owner_can_review(): void
    {
        [, $order] = $this->makeOrder(OrderStatus::Completed);
        $stranger = $this->makeUser('stranger');

        $this->actingAs($stranger)
            ->post(route('orders.review.store', $order), ['rating' => 5])
            ->assertForbidden();
    }

    public function test_only_one_review_per_order(): void
    {
        [$buyer, $order] = $this->makeOrder(OrderStatus::Completed);

        $this->actingAs($buyer)->post(route('orders.review.store', $order), ['rating' => 5]);
        $this->actingAs($buyer)->post(route('orders.review.store', $order), ['rating' => 4]);

        $this->assertSame(1, Review::where('order_id', $order->id)->count());
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        [$buyer, $order] = $this->makeOrder(OrderStatus::Completed);

        $this->actingAs($buyer)
            ->post(route('orders.review.store', $order), ['rating' => 6])
            ->assertSessionHasErrors('rating');
    }
}