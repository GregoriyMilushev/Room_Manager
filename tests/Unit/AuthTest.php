<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Pesho',
            'email' => 'Peshos@goshev.com',
            'password' => bcrypt('12345678'), 
        ]);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_register_form()
    {
        $response = $this->post('api/register', [
            'name' => 'Peshoto',
            'email' => 'Pesho@abv.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertStatus(201);
        $response->assertSee('Peshoto');
    }

    public function test_login_form()
    {

        $response = $this->post('api/login', [
            'email' => 'Peshos@goshev.com',
            'password' => '12345678', 
        ]);

        $response->assertStatus(200);
        $response->assertSee('Pesho');
    }

    public function test_logout_form()
    {
        $response = $this->post('api/login', [
            'email' => 'Peshos@goshev.com',
            'password' => '12345678', 
        ]);

        $response = $this->actingAs($this->user)->post('api/logout');

        $response->assertStatus(200);
        $response->assertSee('Logged Out!');
    }

    public function test_user_duplication()
    {
        $user2 = User::factory()->create();

        $this->assertTrue($this->user->name != $user2->name);
    }

    
}
