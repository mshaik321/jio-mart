<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_upload_and_remove_a_product_image(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $product = Product::factory()->create();

        $file = UploadedFile::fake()->image('rice.jpg', 400, 400)->size(500);

        $this->actingAs($admin)->post(route('admin.products.images.store', $product), [
            'image' => $file,
            'is_primary' => true,
        ])->assertRedirect(route('admin.products.edit', $product));

        $image = $product->images()->first();
        $this->assertNotNull($image);
        $this->assertTrue($image->is_primary);
        Storage::disk('public')->assertExists($image->path);

        $this->actingAs($admin)->delete(route('admin.products.images.destroy', [$product, $image]))
            ->assertRedirect(route('admin.products.edit', $product));

        Storage::disk('public')->assertMissing($image->path);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    }

    public function test_rejects_non_image_file_uploads(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $product = Product::factory()->create();

        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $this->actingAs($admin)->post(route('admin.products.images.store', $product), [
            'image' => $file,
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, $product->images()->count());
    }
}
