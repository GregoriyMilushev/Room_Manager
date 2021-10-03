<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    // public function test_register_form()
    // {
    //     $response = $this->post('api/register', [
    //         'name' => 'Pesho',
    //         'email' => 'Pesho@goshev.com',
    //         'password' => '12345678',
    //         'password_confirmation' => '12345678',
    //     ]);

    //     $response->assertStatus(201);
    // }

    // public function test_login_form()
    // {
    //     $response = $this->post('api/login', [
    //         'email' => 'Pesho@goshev.com',
    //         'password' => '12345678', 
    //     ]);

    //     $response->assertStatus(200);
    // }

    public function test_logout_form()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('api/logout');

        $response->assertStatus(200);
    }

    public function test_user_duplication()
    {
        $user1 = User::make([
            'name' => 'Pesho',
            'email' => 'Pesho@gmail.com'
        ]);

        $user2 = User::make([
            'name' => 'Pesh',
            'email' => 'Pesh@gmail.com'
        ]);

        $this->assertTrue($user1->name != $user2->name);
    }

    
}
