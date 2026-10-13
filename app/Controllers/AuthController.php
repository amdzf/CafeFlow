<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\User;
use App\Helpers\Auth;
use App\Helpers\Logger;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirectRole();
            return;
        }

        $this->render('auth/login', [
            'title' => 'Login - CafeFlow POS'
        ], 'auth');
    }

    public function loginProcess(Request $request): void
    {
        $this->validateCsrf($request);

        $email = trim((string)$request->post('email', ''));
        $password = (string)$request->post('password', '');

        $errors = $this->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $this->back('error', 'Silakan lengkapi email dan password dengan benar.');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->back('error', 'Email atau password yang Anda masukkan salah.');
            return;
        }

        if ((int)$user['is_active'] !== 1) {
            $this->back('error', 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.');
            return;
        }

        // Authentication successful
        Auth::login($user);
        Logger::log('Login Berhasil', 'users', (string)$user['id'], "User {$user['name']} ({$user['role']}) berhasil masuk.");

        set_flash('success', "Selamat datang kembali, {$user['name']}!");
        $this->redirectRole();
    }

    public function logout(): void
    {
        $userName = Auth::user()['name'] ?? 'User';
        Logger::log('Logout', 'users', (string)Auth::id(), "User {$userName} telah logout.");
        Auth::logout();
        set_flash('success', 'Anda telah berhasil keluar dari sistem.');
        $this->redirect('/login');
    }

    private function redirectRole(): void
    {
        $role = Auth::role();
        $target = match ($role) {
            'admin' => '/dashboard',
            'kasir' => '/pos',
            'dapur' => '/kitchen',
            default => '/login'
        };
        $this->redirect($target);
    }
}
