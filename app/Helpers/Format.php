<?php

namespace App\Helpers;

class Format
{
    public static function rupiah(float|int|string $number): string
    {
        $val = (float)$number;
        return 'Rp ' . number_format($val, 0, ',', '.');
    }

    public static function date(string $datetime, string $format = 'd M Y, H:i'): string
    {
        if (empty($datetime)) return '-';
        $timestamp = strtotime($datetime);
        return date($format, $timestamp);
    }

    public static function dateIndo(string $date): string
    {
        if (empty($date)) return '-';
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $time = strtotime($date);
        $d = date('j', $time);
        $m = $months[(int)date('n', $time)];
        $y = date('Y', $time);
        return "{$d} {$m} {$y}";
    }

    public static function orderStatusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'pending' => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>',
            'processing' => '<span class="badge bg-info text-dark"><i class="bi bi-hourglass-split me-1"></i>Diproses</span>',
            'ready' => '<span class="badge bg-primary"><i class="bi bi-bell me-1"></i>Siap Saji</span>',
            'completed' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>',
            'cancelled' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Dibatalkan</span>',
            default => '<span class="badge bg-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>',
        };
    }

    public static function paymentStatusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'paid' => '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Lunas (PAID)</span>',
            'unpaid', 'pending' => '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pending</span>',
            'failed' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Gagal</span>',
            'expired' => '<span class="badge bg-secondary"><i class="bi bi-stopwatch me-1"></i>Kedaluwarsa</span>',
            'refunded' => '<span class="badge bg-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Refund</span>',
            default => '<span class="badge bg-light text-dark">' . htmlspecialchars(ucfirst($status)) . '</span>',
        };
    }

    public static function paymentMethodBadge(string $method): string
    {
        return match (strtolower($method)) {
            'cash' => '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-cash me-1"></i>TUNAI</span>',
            'qris' => '<span class="badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-qr-code-scan me-1"></i>QRIS</span>',
            default => '<span class="badge bg-secondary-subtle text-secondary">-</span>',
        };
    }
}
