<?php

namespace App\Services;

use App\Models\Setting;
use App\Helpers\Format;

class ReceiptService
{
    public static function getCafeInfo(): array
    {
        $settingModel = new Setting();
        return [
            'name' => $settingModel->get('cafe_name', 'CafeFlow Coffee & Eatery'),
            'tagline' => $settingModel->get('cafe_tagline', 'Artisan Brews & Cozy Vibes'),
            'phone' => $settingModel->get('cafe_phone', '0812-3456-7890'),
            'address' => $settingModel->get('cafe_address', 'Jl. Senopati No. 45, Jakarta Selatan'),
            'footer' => $settingModel->get('receipt_footer', "Terima kasih atas kunjungan Anda!\nFollow IG: @cafeflow.id"),
        ];
    }
}
