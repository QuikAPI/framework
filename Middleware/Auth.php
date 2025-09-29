<?php
namespace QuikAPI\Middleware;

use QuikAPI\Http\Request;
use QuikAPI\Security\JWT;
use QuikAPI\Models\User;

class Auth
{
    public function __invoke(Request $req, callable $next)
    {
        $auth = $req->headers['Authorization'] ?? $req->headers['authorization'] ?? '';
        if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            $token = trim($m[1]);
            try {
                $claims = JWT::decode($token);
                // Expecting sub to be user id
                $userId = $claims['sub'] ?? null;
                if ($userId) {
                    $req->authUser = User::find($userId);
                }
            } catch (\Throwable $e) {
                // invalid token -> keep authUser null
            }
        }
        return $next($req);
    }
}
