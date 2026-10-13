<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Model;
use App\Models\Setting;
use App\Helpers\Logger;

class SettingController extends Controller
{
    public function index(Request $request): void
    {
        $settingModel = new Setting();
        $settings = $settingModel->getAll();

        // Recent activity logs
        $db = Model::db();
        $stmtLogs = $db->query("
            SELECT al.*, u.name AS user_name, u.role AS user_role
            FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.id DESC
            LIMIT 50
        ");
        $logs = $stmtLogs->fetchAll();

        $this->render('settings/index', [
            'title' => 'Pengaturan Aplikasi & Log - CafeFlow POS',
            'settings' => $settings,
            'logs' => $logs
        ]);
    }

    public function update(Request $request): void
    {
        $this->validateCsrf($request);

        $settingModel = new Setting();
        $keys = [
            'cafe_name',
            'cafe_tagline',
            'cafe_phone',
            'cafe_address',
            'currency_symbol',
            'tax_percent',
            'service_charge_percent',
            'receipt_footer',
            'midtrans_is_production',
            'midtrans_server_key',
            'midtrans_client_key',
            'midtrans_merchant_id'
        ];

        foreach ($keys as $key) {
            if ($request->post($key) !== null) {
                $settingModel->set($key, trim((string)$request->post($key)));
            }
        }

        Logger::log('Update Pengaturan', 'settings', null, 'Konfigurasi kafe dan payment gateway diperbarui.');

        $this->redirect('/settings', 'success', 'Pengaturan berhasil disimpan!');
    }
}
