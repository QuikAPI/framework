<?php
namespace QuikAPI\Controllers;

use QuikAPI\Http\Request;
use QuikAPI\Support\Responses;
use QuikAPI\Models\User;
use QuikAPI\Security\Password;
use QuikAPI\Security\JWT;
use QuikAPI\Requests\Auth\LoginRequest;

class AuthController
{
    public function login(Request $request): array
    {
        try {
            $data = (new LoginRequest())->validated($request);
            $user = User::where('email', $data['email'])->first();
            if (!$user || !Password::verify($data['password'], $user->password)) {
                return Responses::fail(['Invalid credentials'], 401);
            }
            $token = JWT::encode(['sub' => $user->id], '+2 hours');
            return Responses::success([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 7200
            ], 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }
}
