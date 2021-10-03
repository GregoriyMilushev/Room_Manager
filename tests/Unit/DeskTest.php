<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeskTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_admin_getting_all_desks()
    {
        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->get('api/desks');

        $response->assertOk();
        $response->assertStatus(200);
    }

    public function test_admin_create_desk()
    {
        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->post('api/desks',[
            'price_per_week' => 15.50,
            'position' => 'centered',
            'size' => 'small',
            'room_id' => 1,
        ]);

        $response->assertStatus(201);
    }
}
