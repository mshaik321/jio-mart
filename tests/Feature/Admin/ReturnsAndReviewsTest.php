<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReviewStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use App\Services\ReturnService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnsAndReviewsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_approve_and_complete_a_return_restoring_stock(): void
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $product = Product::factory()->create(['selling_price' => 100, 'mrp' => 120]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id, 'selling_price' => 100, 'mrp' => 120]);
        Inventory::create(['warehouse_id' => $warehouse->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => 10]);

        $customer = Customer::factory()->create();
        $address = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $zone = DeliveryZone::factory()->create();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($customer, 'return-test');
        $cartService->addItem($cart, $variant, 2);

        $order = app(CheckoutService::class)->placeOrder(
            $customer, $cart->fresh('items.variant.product'), $address, $zone, null, PaymentMethod::Cod,
        );

        $orderService = app(OrderService::class);
        foreach ([OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed, OrderStatus::OutForDelivery, OrderStatus::Delivered] as $status) {
            $orderService->updateStatus($order->fresh(), $status);
        }

        $return = app(ReturnService::class)->request($order->fresh(), [
            ['order_item_id' => $order->items->first()->id, 'quantity' => 2, 'reason' => 'Damaged'],
        ], 'Damaged on arrival');

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.returns.approve', $return))->assertRedirect();
        $this->assertSame('approved', $return->fresh()->status->value);

        $this->actingAs($admin)->post(route('admin.returns.complete', $return), [
            'refund_amount' => 200,
        ])->assertRedirect();

        $this->assertSame('completed', $return->fresh()->status->value);
        $this->assertSame(10, $variant->fresh()->totalStock());
    }

    public function test_admin_can_moderate_reviews(): void
    {
        $admin = $this->admin();
        $review = Review::factory()->for(Product::factory())->for(Customer::factory())->create(['status' => ReviewStatus::Pending]);

        $this->actingAs($admin)->post(route('admin.reviews.moderate', $review), [
            'status' => 'approved',
        ])->assertRedirect();

        $this->assertSame(ReviewStatus::Approved, $review->fresh()->status);
    }
}
