<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiProductControllerTest extends TestCase
{
    /**
     * Testing against HTTP Responses Statu Codes
     * I dont verify against the text because that can always be changed.
     */
    use RefreshDatabase;

    private $product = [
        'name' => 'PHPUNIT_TEST_PRODUCT',
        'description' => 'PHPUNIT_TEST_DESCRIPTION',
        'price' => 1,
    ];

    #[Test]
    public function api_product_route_should_return_200_success(): void
    {
        $response = $this->getJson(route('products.index'));

        $response->assertOk();
    }

    #[Test]
    public function api_product_route_should_return_201_created(): void
    {
        $response = $this->postJson(route('products.store'), $this->product);

        $response->assertCreated();
    }

    #[Test]
    public function api_product_route_should_return_422_error(): void
    {
        $response = $this->postJson(route('products.store'), []);

        $response->assertUnprocessable();
    }

    #[Test]
    public function api_product_route_should_update_and_return_204_updated(): void
    {
        $updatedProduct = $this->product;
        $updatedProduct['description'] = 'PHPUNIT_TEST_PRODUCT_DESCRIPTION';

        $response = $this->postJson(route('products.store'), $this->product);

        $response->assertCreated();
        $json = $response->json();
        $id = $json['data']['id'];

        $response = $this->putJson(route('products.update', $id), $updatedProduct);

        $response->assertNoContent();
    }

    #[Test]
    public function api_product_route_should_remove_and_return_204_deleted(): void
    {
        $response = $this->postJson(route('products.store'), $this->product);
        $json = $response->json();
        $id = $json['data']['id'];

        $response = $this->deleteJson(route('products.destroy', $id));
        $response->assertNoContent();
    }

    #[Test]
    public function api_product_route_should_not_remove_and_return_404(): void
    {
        $response = $this->deleteJson(route('products.destroy', 999));
        $response->assertNotFound();
    }

    #[Test]
    public function api_products_route_should_return_200_if_product_found(): void
    {
        $response = $this->postJson(route('products.store'), $this->product);
        $response->assertCreated();

        $response = $this->getJson(route('products.show', 1));
        $response->assertOK();
    }

    #[Test]
    public function api_products_route_should_return_404_if_product_not_found(): void
    {
        $response = $this->getJson(route('products.show', 1));
        $response->assertNotFound();
    }
}
