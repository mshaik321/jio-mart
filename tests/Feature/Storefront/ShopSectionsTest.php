<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShopSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_lists_the_four_shop_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Groceries &amp; Provisions', false)
            ->assertSee('House Keeping')
            ->assertSee('Online Food Delivery')
            ->assertSee('Medicines');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function placeholderSections(): array
    {
        return [
            'house keeping' => ['sections.housekeeping', 'House Keeping'],
            'food delivery' => ['sections.food', 'Online Food Delivery'],
            'medicines' => ['sections.medicines', 'Medicines'],
        ];
    }

    #[DataProvider('placeholderSections')]
    public function test_placeholder_sections_show_a_coming_soon_page(string $routeName, string $label): void
    {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSee($label)
            ->assertSee('Coming soon')
            ->assertSee(route('home'), false);
    }

    public function test_grocery_category_bar_is_hidden_on_placeholder_sections(): void
    {
        Category::factory()->create(['name' => 'Spices Bar Test', 'is_active' => true, 'parent_id' => null]);

        $this->get(route('home'))->assertSee('aria-label="Categories"', false);
        $this->get(route('sections.medicines'))->assertDontSee('aria-label="Categories"', false);
    }

    public function test_the_active_section_is_marked_in_the_header(): void
    {
        $this->get(route('sections.food'))->assertSee('aria-current="page"', false);
    }
}
