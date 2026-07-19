<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateUserToken
{
    public function handle(array $data): string
    {
        /** @var User|null $user */
        $user = User::whereEmail($data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $deviceName = $data['device_name'] ?? 'api-token';

        return $user->createToken($deviceName)->plainTextToken;
    }
}
