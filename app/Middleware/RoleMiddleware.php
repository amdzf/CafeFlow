<?php

namespace App\Middleware;

use App\Helpers\Auth;
use Core\Request;

class RoleMiddleware
{
    public static function handle(Request $request, array $allowedRoles): bool
    {
        AuthMiddleware::handle($request);

        $userRole = Auth::role();
        if (!in_array($userRole, $allowedRoles, true)) {
            if ($request->isJson() || $request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Redirect based on current role
            $target = match ($userRole) {
                'kasir' => '/pos',
                'dapur' => '/kitchen',
                default => '/login'
            };

            set_flash('error', 'Akses ditolak: Anda tidak memiliki izin untuk halaman tersebut.');
            header('Location: ' . url($target));
            exit;
        }

        return true;
    }
}
