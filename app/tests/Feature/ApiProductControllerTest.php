<?php

namespace Tests\Feature;

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
        // Arrage / Prepare

        // Act / Perform
        $response = $this->getJson(route('products.index'));

        // Assert / Predict
        $response->assertOk();
    }

    #[Test]
    public function api_product_route_should_return_201_created(): void
    {
        // Arrage / Prepare

        // Act / Perform
        $response = $this->postJson(route('products.store'), $this->product);

        // Assert / Predict
        $response->assertCreated();
    }

    #[Test]
    public function api_product_route_should_return_422_error(): void
    {
        // Arrage / Prepare

        // Act / Perform
        $response = $this->postJson(route('products.store'), []);

        // Assert / Predict
        $response->assertUnprocessable();
    }

    #[Test]
    public function api_product_route_should_update_and_return_204_updated(): void
    {
        // Arrage / Prepare
        $updatedProduct = $this->product;
        $updatedProduct['description'] = 'PHPUNIT_TEST_PRODUCT_DESCRIPTION';

        $response = $this->postJson(route('products.store'), $this->product);

        $response->assertCreated();
        $json = $response->json();
        $id = $json['data']['id'];

        // Act / Perform
        $response = $this->putJson(route('products.update', $id), $updatedProduct);

        // Assert / Predict
        $response->assertNoContent();
    }

    #[Test]
    public function api_product_route_should_remove_and_return_204_deleted(): void
    {
        // Arrage / Prepare
        $response = $this->postJson(route('products.store'), $this->product);
        $json = $response->json();
        $id = $json['data']['id'];

        // Act / Perform
        $response = $this->deleteJson(route('products.destroy', $id));
        //
        // Assert / Predict
        $response->assertNoContent();
    }

    #[Test]
    public function api_product_route_should_not_remove_and_return_404(): void
    {
        // Arrage / Prepare

        // Act / Perform
        $response = $this->deleteJson(route('products.destroy', 999));

        // Assert / Predict
        $response->assertNotFound();
    }

    #[Test]
    public function api_products_route_should_return_200_if_product_found(): void
    {
        // Arrage / Prepare
        $response = $this->postJson(route('products.store'), $this->product);
        $response->assertCreated();

        // Act / Perform
        $response = $this->getJson(route('products.show', 1));
        // Assert / Predict
        $response->assertOK();
    }

    #[Test]
    public function api_products_route_should_return_404_if_product_not_found(): void
    {
        // Arrage / Prepare

        // Act / Perform
        $response = $this->getJson(route('products.show', 1));
        // Assert / Predict
        $response->assertNotFound();
    }
}
