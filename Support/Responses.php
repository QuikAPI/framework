<?php
namespace QuikAPI\Support;

class Responses
{
    public static function success(mixed $data = null, int $status = 200): array
    {
        return [
            'status' => $status,
            'data' => $data ?? new \stdClass(),
            'errors' => new \stdClass(),
            'hasError' => false,
        ];
    }

    public static function fail(array $errors, int $status = 400): array
    {
        return [
            'status' => 'fail',
            'data' => new \stdClass(),
            'errors' => $errors,
            'hasError' => true,
        ];
    }
}
