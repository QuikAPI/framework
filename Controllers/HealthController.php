<?php
namespace QuikAPI\Controllers;

use QuikAPI\Http\Request;

class HealthController
{
    public function index(Request $req): array
    {
        return [
            'status' => 'ok',
            'time' => date('c'),
        ];
    }
}
