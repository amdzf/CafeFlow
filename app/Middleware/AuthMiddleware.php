<?php

namespace App\Middleware;

use App\Helpers\Auth;
use Core\Request;

class AuthMiddleware
{
    public static function handle(Request $request): bool
    {
        if (!Auth::check()) {
            if ($request->isJson() || $request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            set_flash('error', 'Silakan login terlebih dahulu untuk mengakses sistem.');
            header('Location: ' . url('/login'));
            exit;
        }
        return true;
    }
}
