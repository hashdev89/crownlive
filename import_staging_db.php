<?php
/**
 * Helper script to import staging database
 * 
 * Usage:
 * 1. Place your staging SQL dump file in the project root
 * 2. Run: php import_staging_db.php staging_dump.sql
 * 
 * Or manually:
 * mysql -u root -p u564736181_cg_bagisto < staging_dump.sql
 */

if ($argc < 2) {
    echo "Usage: php import_staging_db.php <sql_dump_file>\n";
    echo "\n";
    echo "Example: php import_staging_db.php staging_dump.sql\n";
    echo "\n";
    echo "Or use MySQL directly:\n";
    echo "mysql -u root u564736181_cg_bagisto < staging_dump.sql\n";
    exit(1);
}

$sqlFile = $argv[1];

if (!file_exists($sqlFile)) {
    echo "Error: File '$sqlFile' not found!\n";
    exit(1);
}

// Load .env to get database credentials
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "Error: .env file not found!\n";
    exit(1);
}

$env = parse_ini_file($envFile);
$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

if (empty($dbName)) {
    echo "Error: DB_DATABASE not set in .env\n";
    exit(1);
}

echo "Importing database dump...\n";
echo "Database: $dbName\n";
echo "File: $sqlFile\n";
echo "\n";

$command = sprintf(
    'mysql -h %s -P %s -u %s %s %s < %s',
    escapeshellarg($dbHost),
    escapeshellarg($dbPort),
    escapeshellarg($dbUser),
    $dbPass ? '-p' . escapeshellarg($dbPass) : '',
    escapeshellarg($dbName),
    escapeshellarg($sqlFile)
);

echo "Running: mysql import command...\n";
exec($command, $output, $returnVar);

if ($returnVar === 0) {
    echo "\n✓ Database imported successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Run: php artisan cache:clear\n";
    echo "2. Run: php artisan config:clear\n";
    echo "3. Run: php artisan view:clear\n";
    echo "4. Check your site at http://127.0.0.1:8000\n";
} else {
    echo "\n✗ Error importing database!\n";
    echo "Try running manually:\n";
    echo "mysql -u $dbUser " . ($dbPass ? "-p" : "") . " $dbName < $sqlFile\n";
    exit(1);
}

