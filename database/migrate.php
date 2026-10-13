<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Helpers/Env.php';
\App\Helpers\Env::load(ROOT_PATH . '/.env');

$host = \App\Helpers\Env::get('DB_HOST', '127.0.0.1');
$port = \App\Helpers\Env::get('DB_PORT', '3306');
$dbName = \App\Helpers\Env::get('DB_DATABASE', 'cafeflow_pos');
$user = \App\Helpers\Env::get('DB_USERNAME', 'root');
$pass = \App\Helpers\Env::get('DB_PASSWORD', '');

echo "Connecting to MySQL server...\n";
try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Creating database {$dbName} if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");

    $sqlFile = ROOT_PATH . '/database/cafeflow_pos.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at {$sqlFile}\n");
    }

    echo "Executing schema and seed queries from cafeflow_pos.sql...\n";
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);

    echo "Success! Database cafeflow_pos has been initialized and seeded successfully.\n";
    echo "Default accounts:\n";
    echo " - Admin:  admin@cafeflow.com / admin123\n";
    echo " - Kasir:  kasir@cafeflow.com / kasir123\n";
    echo " - Dapur:  dapur@cafeflow.com / dapur123\n";
} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
