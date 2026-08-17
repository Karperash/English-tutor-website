<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;
use App\Models\User;

require dirname(__DIR__) . '/vendor/autoload.php';
Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set((string)Env::get('APP_TIMEZONE', 'Europe/Berlin'));

$checks = [];
$checks['PHP >= 8.3'] = version_compare(PHP_VERSION, '8.3.0', '>=');
$checks['PDO'] = extension_loaded('pdo');
$checks['pdo_mysql'] = extension_loaded('pdo_mysql');
$checks['mbstring'] = extension_loaded('mbstring');
$checks['fileinfo'] = extension_loaded('fileinfo');
$checks['storage/homework writable'] = is_dir(dirname(__DIR__) . '/storage/homework') && is_writable(dirname(__DIR__) . '/storage/homework');

try {
    Database::connection()->query('SELECT 1')->fetchColumn();
    $checks['MySQL connection'] = true;
    $teacher = User::findByLogin('Katy1009');
    $checks['Teacher exists'] = (bool)$teacher;
    $checks['Teacher password'] = $teacher ? password_verify('loveyou', (string)$teacher['password_hash']) : false;
} catch (Throwable $e) {
    $checks['MySQL connection'] = false;
    $checks['Teacher exists'] = false;
    $checks['Teacher password'] = false;
    fwrite(STDERR, "DB error: {$e->getMessage()}\n");
}

$failed = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[OK]   ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$ok) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);
