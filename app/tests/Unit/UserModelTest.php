<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_can_be_initiated(): void
    {
        // Arrange
        $user = User::factory()->create();

        //Act

        // Assert
        $this->assertModelExists($user);
        $this->assertDatabaseCount('users', 1);
    }
    /**
     * A basic unit test example.
     */
    public function test_user_has_full_name_attribute(): void
    {
        // Arrange
        $testFallenUser = [
            'firstname' => 'Fallen',
            'lastname' => 'Shadow',
            'email' => 'fallen.shadow@phpunittest.local',
            'password' => 'changeme',
        ];

        // Act
        $user = User::create($testFallenUser);

        // Act
        $this->assertDatabaseCount('users', 1);
        $this->assertEquals('Fallen Shadow', $user->fullname);
    }
}

