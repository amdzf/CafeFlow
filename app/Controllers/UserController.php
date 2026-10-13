<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\User;
use App\Helpers\Logger;
use App\Helpers\Auth;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $userModel = new User();
        $users = $userModel->getAllUsers();

        $this->render('users/index', [
            'title' => 'Manajemen Pengguna & Staf - CafeFlow POS',
            'users' => $users
        ]);
    }

    public function store(Request $request): void
    {
        $this->validateCsrf($request);

        $name = trim((string)$request->post('name'));
        $email = trim((string)$request->post('email'));
        $password = (string)$request->post('password');
        $role = $request->post('role', 'kasir');
        $phone = trim((string)$request->post('phone', ''));

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $this->back('error', 'Email tersebut sudah terdaftar.');
            return;
        }

        $userId = $userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
            'phone' => $phone,
            'is_active' => 1
        ]);

        Logger::log('Tambah Pengguna', 'users', (string)$userId, "User {$name} ({$role}) berhasil dibuat.");

        $this->redirect('/users', 'success', "Pengguna {$name} berhasil didaftarkan!");
    }

    public function update(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $userModel = new User();
        $user = $userModel->find((int)$id);
        if (!$user) {
            $this->redirect('/users', 'error', 'Pengguna tidak ditemukan.');
            return;
        }

        $name = trim((string)$request->post('name'));
        $role = $request->post('role', $user['role']);
        $phone = trim((string)$request->post('phone', ''));
        $isActive = (int)$request->post('is_active', 1);

        $data = [
            'name' => $name,
            'role' => $role,
            'phone' => $phone,
            'is_active' => $isActive
        ];

        $newPassword = (string)$request->post('password', '');
        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $userModel->update($user['id'], $data);
        Logger::log('Update Pengguna', 'users', (string)$user['id'], "Data user {$name} diperbarui.");

        $this->redirect('/users', 'success', "Data pengguna {$name} berhasil diperbarui!");
    }

    public function delete(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $targetId = (int)$id;
        if ($targetId === (int)Auth::id()) {
            $this->redirect('/users', 'error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
            return;
        }

        $userModel = new User();
        $user = $userModel->find($targetId);
        if ($user) {
            $userModel->delete($targetId);
            Logger::log('Hapus Pengguna', 'users', (string)$targetId, "User {$user['name']} dihapus.");
        }

        $this->redirect('/users', 'success', 'Pengguna berhasil dihapus.');
    }
}
