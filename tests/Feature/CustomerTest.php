<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_can_create_a_customer(): void
    {
        $payload = [
            'name' => 'Acme Corp',
            'phone' => '0599123456',
            'email' => 'acme@example.com',
            'address' => 'Gaza, Palestine',
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Customer created successfully',
                'data' => ['name' => 'Acme Corp', 'phone' => '0599123456'],
            ]);

        $this->assertDatabaseHas('customers', ['phone' => '0599123456']);
    }

    public function test_it_returns_validation_error_when_name_is_missing(): void
    {
        $response = $this->postJson('/api/customers', [
            'phone' => '0599123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors('name');
    }

    public function test_it_rejects_a_duplicate_phone(): void
    {
        Customer::factory()->create(['phone' => '0599000000']);

        $response = $this->postJson('/api/customers', [
            'name' => 'Duplicate',
            'phone' => '0599000000',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $response = $this->postJson('/api/customers', [
            'name' => 'Bad Email',
            'phone' => '0599111222',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_it_lists_customers_with_pagination_and_search(): void
    {
        Customer::factory()->create(['name' => 'Findable Company']);
        Customer::factory(5)->create();

        $response = $this->getJson('/api/customers?search=Findable&per_page=2');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Findable Company')
            ->assertJsonStructure(['meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }

    public function test_it_shows_updates_and_deletes_a_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Original']);

        $this->getJson("/api/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Original');

        $this->putJson("/api/customers/{$customer->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');

        $this->deleteJson("/api/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_it_returns_404_for_a_missing_customer(): void
    {
        $this->getJson('/api/customers/9999')
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }
}
