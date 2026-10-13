<?php

namespace Core;

use App\Helpers\Csrf;
use App\Helpers\Auth;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $path, ?string $flashType = null, ?string $flashMessage = null): void
    {
        if ($flashType && $flashMessage) {
            set_flash($flashType, $flashMessage);
        }
        header('Location: ' . url($path));
        exit;
    }

    protected function back(?string $flashType = null, ?string $flashMessage = null): void
    {
        if ($flashType && $flashMessage) {
            set_flash($flashType, $flashMessage);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        header('Location: ' . $referer);
        exit;
    }

    protected function validateCsrf(Request $request): void
    {
        $token = $request->input('csrf_token') ?? $request->getHeader('X-CSRF-TOKEN');
        if (!Csrf::validate($token)) {
            if ($request->isJson() || $request->getHeader('X-Requested-With') === 'XMLHttpRequest') {
                $this->json(['success' => false, 'message' => 'CSRF token tidak valid atau sesi telah kedaluwarsa.'], 403);
            }
            $this->redirect('/login', 'error', 'Sesi Anda telah kedaluwarsa, silakan login kembali.');
        }
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = trim((string)($data[$field] ?? ''));

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field] = "Field {$field} wajib diisi.";
                } elseif ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Format email tidak valid.";
                } elseif ($rule === 'numeric' && $value !== '' && !is_numeric($value)) {
                    $errors[$field] = "Field {$field} harus berupa angka.";
                } elseif (str_starts_with($rule, 'min:') && $value !== '') {
                    $min = (int)substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "Field {$field} minimal {$min} karakter.";
                    }
                }
            }
        }

        if (!empty($errors)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['old_input'] = $data;
            $_SESSION['errors'] = $errors;
        }

        return $errors;
    }
}
