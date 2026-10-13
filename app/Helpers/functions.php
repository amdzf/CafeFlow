<?php

use App\Helpers\Csrf;
use App\Helpers\Auth;
use App\Helpers\Format;

if (!function_exists('base_path_url')) {
    function base_path_url(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            return '';
        }
        return rtrim($dir, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = base_path_url();
        $path = '/' . ltrim($path, '/');
        if ($path === '//') $path = '/';
        return ($base === '' ? $path : $base . $path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::getField();
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$type] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $type): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $type): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['flash'][$type]);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $val = $_SESSION['old_input'][$key] ?? $default;
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('product_image_url')) {
    function product_image_url(?string $image): string
    {
        $default = asset('images/products/kopi-susu.jpg');
        if (empty($image)) {
            return $default;
        }
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $fullPath = $root . '/public/images/products/' . $image;
        if (file_exists($fullPath)) {
            return asset('images/products/' . $image);
        }
        return $default;
    }
}

if (!function_exists('has_product_image')) {
    function has_product_image(?string $image): bool
    {
        if (empty($image)) {
            return false;
        }
        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        return file_exists($root . '/public/images/products/' . $image);
    }
}
