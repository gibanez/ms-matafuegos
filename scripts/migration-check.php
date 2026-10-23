<?php

declare(strict_types=1);

/**
 * Diagnóstico temporal de migraciones para hostings sin SSH.
 *
 * Uso:
 *   1. Subir este archivo a la carpeta public/ del hosting como migration-check.php
 *   2. Abrir https://TU-DOMINIO/migration-check.php
 *   3. Copiar el resultado y borrar el archivo del hosting.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "MS Matafuegos - diagnostico de migraciones\n";
echo "==========================================\n\n";
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'Directorio public: ' . __DIR__ . "\n\n";

$autoload = __DIR__ . '/../vendor/autoload.php';
$configFile = __DIR__ . '/../phinx.php';

if (!is_file($autoload)) {
    echo "FALTA vendor/autoload.php\n";
    exit;
}

if (!is_file($configFile)) {
    echo "FALTA phinx.php\n";
    exit;
}

require $autoload;

$configArray = require $configFile;
$paths = $configArray['paths']['migrations'] ?? '';
$environment = $configArray['environments']['default_environment'] ?? 'development';

echo "Ruta de migraciones: {$paths}\n";
echo 'Existe: ' . (is_dir($paths) ? 'si' : 'NO') . "\n";
echo 'Legible: ' . (is_dir($paths) && is_readable($paths) ? 'si' : 'NO') . "\n";

if (is_dir($paths)) {
    $phpFiles = [];

    foreach (scandir($paths) ?: [] as $file) {
        if (substr($file, -4) === '.php') {
            $phpFiles[] = $file;
        }
    }

    sort($phpFiles);

    echo 'Archivos .php encontrados: ' . count($phpFiles) . "\n";

    foreach ($phpFiles as $file) {
        $valid = method_exists(\Phinx\Util\Util::class, 'isValidMigrationFileName')
            ? \Phinx\Util\Util::isValidMigrationFileName($file)
            : (bool) preg_match('/^[0-9]+_[a-zA-Z0-9_]+\.php$/', $file);

        echo '  - ' . $file . '  [' . ($valid ? 'nombre valido' : 'NOMBRE INVALIDO: Phinx lo ignora') . "]\n";
    }
} else {
    echo "No se puede listar la carpeta de migraciones.\n";
}

echo "\n";

try {
    $config = new \Phinx\Config\Config($configArray);
    $manager = new \Phinx\Migration\Manager(
        $config,
        new \Symfony\Component\Console\Input\ArrayInput([]),
        new \Symfony\Component\Console\Output\BufferedOutput()
    );

    $migrations = $manager->getMigrations($environment);
    $versionLog = $manager->getEnvironment($environment)->getVersionLog();

    echo 'Phinx detecta ' . count($migrations) . " migraciones (entorno {$environment}):\n";

    foreach ($migrations as $version => $migration) {
        $state = isset($versionLog[$version]) ? 'aplicada' : 'PENDIENTE';
        echo '  - ' . $version . '  ' . $migration->getName() . '  [' . $state . "]\n";
    }
} catch (\Throwable $error) {
    echo 'ERROR de Phinx: ' . get_class($error) . ': ' . $error->getMessage() . "\n";
}

echo "\n";

try {
    $dbPath = $configArray['environments'][$environment]['name'] ?? '';

    echo "Base de datos: {$dbPath}\n";
    echo 'Existe: ' . (is_file($dbPath) ? 'si' : 'NO') . "\n";

    if (is_file($dbPath)) {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $productColumns = $pdo->query('PRAGMA table_info(products)')->fetchAll(PDO::FETCH_COLUMN, 1);
        $invoiceColumns = $pdo->query('PRAGMA table_info(invoices)')->fetchAll(PDO::FETCH_COLUMN, 1);
        $notices = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'maintenance_notices'")->fetchColumn();

        echo 'products.requires_maintenance: ' . (in_array('requires_maintenance', $productColumns, true) ? 'SI' : 'NO') . "\n";
        echo 'products.maintenance_days: ' . (in_array('maintenance_days', $productColumns, true) ? 'SI' : 'NO') . "\n";
        echo 'invoices.paid_at: ' . (in_array('paid_at', $invoiceColumns, true) ? 'SI' : 'NO') . "\n";
        echo 'tabla maintenance_notices: ' . ($notices ? 'SI' : 'NO') . "\n";
        echo 'filas en phinxlog: ' . (int) $pdo->query('SELECT COUNT(*) FROM phinxlog')->fetchColumn() . "\n";
    }
} catch (\Throwable $error) {
    echo 'ERROR de base de datos: ' . get_class($error) . ': ' . $error->getMessage() . "\n";
}

echo "\nListo. Borrar este archivo del hosting.\n";
