<?php
namespace QuikAPI\Middleware;

use QuikAPI\Http\Request;

class ErrorHandler
{
    public function __invoke(Request $req, callable $next)
    {
        try {
            $result = $next($req);
            if (!is_array($result)) {
                $result = ['data' => $result];
            }
            return $result;
        } catch (\Throwable $e) {
            http_response_code(500);
            return [
                'error' => [
                    'message' => 'Internal Server Error',
                    'code' => 'INTERNAL_ERROR',
                    'details' => $e->getMessage(),
                ]
            ];
        }
    }
}
