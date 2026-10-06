<?php

namespace Tests\Feature\Storefront;

use App\Enums\ProductStatus;
use App\Livewire\Storefront\AddToCartButton;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\CartService;
use App\Support\CartContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AddToCartButtonTest extends TestCase
{
    use RefreshDatabase;

    private function stockedVariant(int $stock = 20): ProductVariant
    {
        $product = Product::factory()->create(['status' => ProductStatus::Active, 'min_order_qty' => 1, 'max_order_qty' => 20]);
        $variant = ProductVariant::factory()->default()->create(['product_id' => $product->id]);
        Inventory::create(['warehouse_id' => Warehouse::factory()->create()->id, 'product_variant_id' => $variant->id, 'quantity_on_hand' => $stock]);

        return $variant;
    }

    private function cartQuantity(ProductVariant $variant): int
    {
        return (int) CartItem::where('product_variant_id', $variant->id)->sum('quantity');
    }

    public function test_quantity_controls_add_increase_decrease_and_remove_the_item(): void
    {
        $variant = $this->stockedVariant();

        $component = Livewire::test(AddToCartButton::class, ['variant' => $variant])
            ->assertSee('Add to Cart')
            ->call('add')
            ->assertSet('quantity', 1)
            ->assertDispatched('cart-updated')
            ->assertSee('Decrease quantity')
            ->call('add')
            ->assertSet('quantity', 2);

        $this->assertSame(2, $this->cartQuantity($variant));

        $component->call('decrement')->assertSet('quantity', 1);
        $this->assertSame(1, $this->cartQuantity($variant));

        $component->call('decrement')->assertSet('quantity', 0)->assertSee('Add to Cart');
        $this->assertSame(0, $this->cartQuantity($variant));
    }

    public function test_cannot_add_more_than_is_in_stock(): void
    {
        $variant = $this->stockedVariant(stock: 2);

        Livewire::test(AddToCartButton::class, ['variant' => $variant])
            ->call('add')
            ->call('add')
            ->call('add')
            ->assertSet('quantity', 2)
            ->assertSet('error', 'Not enough stock available for this item.');

        $this->assertSame(2, $this->cartQuantity($variant));
    }

    public function test_button_starts_from_the_quantity_already_in_the_cart(): void
    {
        $variant = $this->stockedVariant();
        Livewire::test(AddToCartButton::class, ['variant' => $variant])->call('add')->call('add');

        Livewire::test(AddToCartButton::class, ['variant' => $variant])->assertSet('quantity', 2);
    }

    public function test_guest_cart_is_kept_when_the_shopper_logs_in(): void
    {
        $variant = $this->stockedVariant();
        $customer = Customer::factory()->create();

        // Separate Livewire::test() calls don't share a session, so build the guest cart
        // for the current session directly, then log in through the real login form.
        $this->startSession();
        $carts = app(CartService::class);
        $carts->addItem($carts->getOrCreateCart(null, CartContext::sessionId()), $variant, 2);

        Volt::test('pages.auth.login')
            ->set('form.email', $customer->user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        $cart = Cart::where('customer_id', $customer->id)->firstOrFail();
        $this->assertSame(2, (int) $cart->items()->where('product_variant_id', $variant->id)->value('quantity'));
        $this->assertSame(0, Cart::whereNull('customer_id')->count());
    }
}
