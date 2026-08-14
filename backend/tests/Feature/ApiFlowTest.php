<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiFlowTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Books',
            'slug' => 'books-'.uniqid(),
            'description' => 'Books',
            'image_url' => 'https://example.com/c.jpg',
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Reading Journal',
            'slug' => 'reading-journal-'.uniqid(),
            'description' => 'A calm journal',
            'short_description' => 'Calm journal',
            'price' => 10000,
            'stock' => 10,
            'sku' => 'SKU-'.uniqid(),
            'image_url' => 'https://example.com/p.jpg',
            'is_featured' => true,
            'is_active' => true,
        ], $overrides));
    }

    public function test_registration_and_login(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $register->assertCreated()->assertJsonStructure(['user', 'token']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_product_listing_and_detail(): void
    {
        $product = $this->product();

        $this->getJson('/api/products')->assertOk()->assertJsonPath('data.0.id', $product->id);
        $this->getJson('/api/products/'.$product->slug)->assertOk()->assertJsonPath('name', $product->name);
    }

    public function test_cart_checkout_and_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 5, 'price' => 10000]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'full_name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'phone' => '123',
                'address' => '1 Quiet Street',
                'city' => 'Calmville',
                'postal_code' => '10000',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertCreated()
            ->assertJsonPath('total', '20000.00')
            ->assertJsonPath('user_id', $user->id);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => '20000.00',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_checkout_rejects_oversell(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 1]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertCreated();

        $product->update(['stock' => 0]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'full_name' => 'Ada',
                'email' => 'ada@example.com',
                'phone' => '123',
                'address' => '1 Quiet Street',
                'city' => 'Calmville',
                'postal_code' => '10000',
                'payment_method' => 'demo_card',
            ])
            ->assertStatus(422);
    }

    public function test_cart_belongs_to_member_and_guest_cannot_add(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $product = $this->product(['stock' => 5]);

        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertUnauthorized();

        $this->actingAs($alice, 'sanctum')
            ->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])
            ->assertCreated();

        $this->actingAs($bob, 'sanctum')
            ->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('total_quantity', 0);

        $this->actingAs($alice, 'sanctum')
            ->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('total_quantity', 2)
            ->assertJsonPath('items.0.product_id', $product->id);
    }

    public function test_admin_access_is_protected(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/products')
            ->assertForbidden();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/products')
            ->assertOk();
    }

    public function test_wishlist_and_review_uniqueness(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/wishlist', ['product_id' => $product->id])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/wishlist', ['product_id' => $product->id])
            ->assertOk();

        $this->assertDatabaseCount('wishlists', 1);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/products/'.$product->id.'/reviews', [
                'rating' => 5,
                'comment' => 'Lovely',
            ])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/products/'.$product->id.'/reviews', [
                'rating' => 4,
                'comment' => 'Updated',
            ])
            ->assertOk();

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', ['rating' => 4, 'comment' => 'Updated']);
    }

    public function test_ai_ask_fallback(): void
    {
        $this->product(['name' => 'Philosophy of Enough', 'is_featured' => true]);

        $this->postJson('/api/ai/ask', ['question' => 'I want to read more'])
            ->assertOk()
            ->assertJsonPath('mode', 'fallback')
            ->assertJsonStructure(['answer', 'products']);
    }
}
