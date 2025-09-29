<?php
namespace QuikAPI\Middleware;

use QuikAPI\Http\Request;

class Cors
{
    public function __invoke(Request $req, callable $next)
    {
        $cfg = $GLOBALS['quikapi']['config']['cors'] ?? [];
        header('Access-Control-Allow-Origin: ' . ($cfg['allow_origin'] ?? '*'));
        header('Access-Control-Allow-Methods: ' . ($cfg['allow_methods'] ?? 'GET, POST, PUT, PATCH, DELETE, OPTIONS'));
        header('Access-Control-Allow-Headers: ' . ($cfg['allow_headers'] ?? 'Content-Type, Authorization, X-Requested-With'));
        header('Access-Control-Allow-Credentials: ' . ($cfg['allow_credentials'] ?? 'true'));
        header('Access-Control-Max-Age: ' . ($cfg['max_age'] ?? '86400'));

        if (($req->method ?? 'GET') === 'OPTIONS') {
            http_response_code(204);
            return ['ok' => true];
        }
        return $next($req);
    }
}
