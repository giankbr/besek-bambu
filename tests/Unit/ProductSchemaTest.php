<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Schema Product',
            'slug' => 'schema-product-'.uniqid(),
            'icon' => '🧺',
            'price' => 150000,
            'stock' => 5,
            'rating' => 5,
            'color_class' => 'p-1',
            'is_active' => true,
            'sort_order' => 0,
        ], $attrs));
    }

    public function test_product_offer_includes_merchant_listing_fields(): void
    {
        $product = $this->product([
            'production_lead_days' => 3,
        ]);

        $offer = seo_product_offer_schema($product);

        $this->assertSame('Offer', $offer['@type']);
        $this->assertArrayHasKey('shippingDetails', $offer);
        $this->assertArrayHasKey('hasMerchantReturnPolicy', $offer);
        $this->assertSame('OfferShippingDetails', $offer['shippingDetails']['@type']);
        $this->assertSame('MerchantReturnPolicy', $offer['hasMerchantReturnPolicy']['@type']);
        $this->assertSame(14, $offer['hasMerchantReturnPolicy']['merchantReturnDays']);
    }

    public function test_product_schema_includes_reviews_when_available(): void
    {
        $product = $this->product();
        $user = User::factory()->create(['name' => 'Budi']);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'title' => 'Bagus',
            'body' => 'Kualitas anyaman rapi.',
            'is_approved' => true,
        ]);

        $schema = seo_product_schema_node($product->fresh());

        $this->assertArrayHasKey('aggregateRating', $schema);
        $this->assertSame(5.0, $schema['aggregateRating']['ratingValue']);
        $this->assertArrayHasKey('review', $schema);
        $this->assertSame('Review', $schema['review'][0]['@type']);
        $this->assertSame('Kualitas anyaman rapi.', $schema['review'][0]['reviewBody']);
    }
}
