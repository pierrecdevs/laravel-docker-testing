<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user = [
        'firstname' => 'PHPUNIT_TEST_FIRSTNAME',
        'lastname' => 'PHPUNIT_TEST_LASTNAME',
        'email' => 'PHPUNIT_TEST@EMAIL.TLD',
        'password' => 'PHPUNIT_TEST_PASSWORD',
        'password_confirmation' => 'PHPUNIT_TEST_PASSWORD',
    ];

    #[Test]
    public function it_should_return_200_ok_status_code(): void
    {
        // Arrange / Prepare

        // Act / Perform
        $response = $this->getJson(route('auth.root'));

        $response
            ->assertOk()
            ->assertJson([
                'status' => 200,
                'message' => 'OK',
            ]);
    }

    #[Test]
    public function it_should_register_a_user(): void
    {
        // Arrange / Prepare

        // Act / Perform
        $response = $this->postJson(route('auth.register'), $this->user);

        // Assert / Predict
        $response
            ->assertCreated()
            ->assertJsonStructure([
                'status',
                'message' => [
                    'user' => [
                        'id',
                        'email',
                        'firstname',
                        'lastname',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'PHPUNIT_TEST@EMAIL.TLD',
        ]);
    }

    #[Test]
    public function it_should_not_register_user_with_invalid_email(): void
    {
        // Arrange / Prepare
        $this->user['email'] = 'invalid-email';

        // Act / Perform
        $response = $this->postJson(route('auth.register'), $this->user);

        // Assert / Predict
        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'The email field must be a valid email address.',
                'errors' => [
                    'email' => [
                        'The email field must be a valid email address.'
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_should_login_a_user_with_successful_credentials(): void
    {
        // Arrange / Prepar

        // Act / Perform
        $user = User::factory()->create([
            'password' => Hash::make($this->user['password'])
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'PHPUNIT_TEST_PASSWORD',
        ];

        $response = $this->postJson(route('auth.login'), $loginData);

        // Assert / Predict
        $response
            ->assertOK()
            ->assertJsonStructure([
                'status',
                'message' => [
                    'user' => [
                        'id',
                        'email',
                        'firstname',
                        'lastname',
                        'created_at',
                        'updated_at',
                    ],
                    'token',
                ],
            ]);

        $this->assertAuthenticated();
    }

    #[Test]
    public function it_should_not_login_with_invalid_credentials(): void
    {
        // Arrange / Prepare
        $user = User::factory()->create([
            'password' => Hash::make($this->user['password']),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'invalid-password',
        ];

        // Act / Perform
        $response = $this->postJson(route('auth.login', $loginData));

        // Assert / Predict
        /* NOTE: When we were hashing the user initially we returned our own error codes */
        /*$response->assertUnprocessable()*/
        /*    ->assertJson([*/
        /*        'status' => 422,*/
        /*        'message' => 'Invalid credentials',*/
        /*    ]);*/
        $response
            ->assertUnauthorized()
            ->assertJson([
                'status' => 401,
                'message' => 'Could not authorize user',
            ]);
    }

    #[Test]
    public function it_should_logout_user()
    {
        // Arrange / Prepare
        $user  = User::factory()->create([
            'password' => Hash::make($this->user['password']),
        ]);

        // Act / Perform
        $response = $this
            ->deleteJson(route('auth.logout'))
            ->assertUnauthorized();

        Sanctum::actingAs($user);
        $this->assertAuthenticatedAs($user);
        $response = $this->deleteJson(route('auth.logout'));

        // Assert / Predict
        $response
            ->assertOk()
            ->assertJson(['status' => 200, 'message' => 'logged out']);
        $this->assertCount(0, $user->tokens);
        $this->assertGuest();
    }

    #[Test]
    public function it_should_send_401_when_logging_out_an_not_logged_in(): void
    {

        // Arrange / Prepare
        $user  = User::factory()->create([
            'password' => Hash::make($this->user['password']),
        ]);


        // Act / Perform
        $response = $this
            ->deleteJson(route('auth.logout'));

        // Assert / Predict
        $response->assertUnauthorized();
        $this->getJson(route('auth.user'))
            ->assertUnauthorized();
    }

    #[Test]
    public function it_should_access_authenticated_route(): void
    {
        // Arrange / Prepare
        $user  = User::factory()->create([
            'password' => Hash::make($this->user['password']),
        ]);

        // Act / Perform
        $this->assertGuest();
        Sanctum::actingAs($user);
        $this->assertAuthenticatedAs($user);
        $response = $this->getJson('/api/user');

        // Assert / Predict

        $response
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'message' => [
                    'user' => [
                        'id',
                        'email',
                        'firstname',
                        'lastname',
                        'created_at',
                        'updated_at',
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_should_not_access_authenticated_route(): void
    {
        // Arrange / Prepare

        // Act / Perform
        $this->assertGuest();
        $response = $this->getJson('/api/user');

        // Assert / Predict
        $response->assertUnauthorized();
    }
}
