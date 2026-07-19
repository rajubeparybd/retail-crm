<?php

declare(strict_types=1);

use App\Models\User;

it('authenticates user and returns token', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $response = $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => 'password',
    ]);

    $response = $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
