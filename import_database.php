<?php
/**
 * Import staging database using Laravel's database connection
 * 
 * Usage: php import_database.php <path_to_sql_file>
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

if ($argc < 2) {
    echo "Usage: php import_database.php <path_to_sql_file>\n";
    echo "\nExample: php import_database.php staging_dump.sql\n";
    echo "         php import_database.php /path/to/staging_dump.sql\n";
    exit(1);
}

$sqlFile = $argv[1];

// Handle relative and absolute paths
if (!file_exists($sqlFile)) {
    // Try in current directory
    $sqlFile = __DIR__ . '/' . $sqlFile;
    if (!file_exists($sqlFile)) {
        echo "Error: SQL file not found: {$argv[1]}\n";
        echo "Searched in: " . __DIR__ . "\n";
        exit(1);
    }
}

echo "Importing database from: $sqlFile\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "\n";

// Read SQL file
$sql = file_get_contents($sqlFile);

if (empty($sql)) {
    echo "Error: SQL file is empty!\n";
    exit(1);
}

// Split by semicolons but preserve within quotes and comments
$statements = [];
$currentStatement = '';
$inQuotes = false;
$quoteChar = null;
$inComment = false;

for ($i = 0; $i < strlen($sql); $i++) {
    $char = $sql[$i];
    $nextChar = $i + 1 < strlen($sql) ? $sql[$i + 1] : '';
    
    // Handle comments
    if (!$inQuotes && $char === '-' && $nextChar === '-') {
        $inComment = true;
        continue;
    }
    
    if ($inComment && $char === "\n") {
        $inComment = false;
        continue;
    }
    
    if ($inComment) {
        continue;
    }
    
    // Handle quotes
    if (($char === '"' || $char === "'" || $char === '`') && ($i === 0 || $sql[$i-1] !== '\\')) {
        if (!$inQuotes) {
            $inQuotes = true;
            $quoteChar = $char;
        } elseif ($char === $quoteChar) {
            $inQuotes = false;
            $quoteChar = null;
        }
    }
    
    $currentStatement .= $char;
    
    // Check for statement end
    if (!$inQuotes && $char === ';') {
        $statement = trim($currentStatement);
        if (!empty($statement) && 
            !preg_match('/^(SET|USE|CREATE DATABASE|DROP DATABASE)/i', $statement)) {
            $statements[] = $statement;
        }
        $currentStatement = '';
    }
}

if (!empty(trim($currentStatement))) {
    $statements[] = trim($currentStatement);
}

echo "Found " . count($statements) . " SQL statements to execute\n";
echo "Importing...\n\n";

$db = DB::connection();
$imported = 0;
$errors = 0;

// Disable foreign key checks temporarily
$db->statement('SET FOREIGN_KEY_CHECKS=0');

foreach ($statements as $index => $statement) {
    if (empty(trim($statement))) {
        continue;
    }
    
    try {
        $db->statement($statement);
        $imported++;
        
        if (($index + 1) % 100 === 0) {
            echo "Processed " . ($index + 1) . " statements...\n";
        }
    } catch (\Exception $e) {
        $errors++;
        if ($errors <= 10) { // Show first 10 errors
            echo "Warning: " . substr($e->getMessage(), 0, 100) . "\n";
        }
    }
}

// Re-enable foreign key checks
$db->statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n";
echo "✓ Import completed!\n";
echo "  - Statements executed: $imported\n";
echo "  - Errors: $errors\n";
echo "\n";
echo "Next steps:\n";
echo "1. php artisan cache:clear\n";
echo "2. php artisan config:clear\n";
echo "3. php artisan view:clear\n";
echo "4. Visit http://127.0.0.1:8000\n";

