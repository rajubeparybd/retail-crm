<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Api\CreateUserToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Authenticate and issue a new Sanctum token.
     */
    public function store(LoginRequest $request, CreateUserToken $createToken): JsonResponse
    {
        $token = $createToken->handle($request->validated());

        return response()->json([
            'token' => $token,
        ]);
    }
}
