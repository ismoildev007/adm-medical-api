<?php

/**
 * Test bootstrap — runs before PHPUnit, before Laravel, before anything.
 *
 * Responsibility:
 *   1. Wipe any cached config so env overrides actually take effect.
 *   2. Force DB_DATABASE to the test database.
 *   3. Create the PostgreSQL test database from scratch.
 *   4. Register a shutdown function that drops the test database after all tests finish.
 *
 * This file reads DB credentials from the project .env (host/user/pass only).
 * It NEVER touches the real application database.
 */

define('TEST_DB_NAME', 'medical_api_test');

$cacheFile = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
}

putenv('DB_DATABASE=' . TEST_DB_NAME);
putenv('AUDIT_DB_DATABASE=' . TEST_DB_NAME);
putenv('AUDITING_ENABLED=false');
$_ENV['DB_DATABASE']       = TEST_DB_NAME;
$_ENV['AUDIT_DB_DATABASE'] = TEST_DB_NAME;
$_ENV['AUDITING_ENABLED']  = 'false';

require __DIR__ . '/../vendor/autoload.php';

try {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();
} catch (Throwable) {
    //
}

$host   = $_ENV['DB_HOST']     ?? '127.0.0.1';
$port   = $_ENV['DB_PORT']     ?? '5432';
$user   = $_ENV['DB_USERNAME'] ?? 'postgres';
$pass   = $_ENV['DB_PASSWORD'] ?? '';
$testDb = TEST_DB_NAME;

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=postgres",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec(
        "SELECT pg_terminate_backend(pid)
         FROM pg_stat_activity
         WHERE datname = '$testDb'
           AND pid <> pg_backend_pid()"
    );

    $pdo->exec("DROP DATABASE IF EXISTS \"$testDb\"");
    $pdo->exec("CREATE DATABASE \"$testDb\"");

    echo "[test] PostgreSQL test database '$testDb' created.\n";
} catch (Throwable $e) {
    echo "[test] FATAL: Cannot create test database '$testDb': " . $e->getMessage() . "\n";
    exit(1);
}

register_shutdown_function(static function () use ($pdo, $testDb): void {
    try {
        $pdo->exec(
            "SELECT pg_terminate_backend(pid)
             FROM pg_stat_activity
             WHERE datname = '$testDb'
               AND pid <> pg_backend_pid()"
        );
        $pdo->exec("DROP DATABASE IF EXISTS \"$testDb\" WITH (FORCE)");
        echo "\n[test] Test database '$testDb' dropped.\n";
    } catch (Throwable $e) {
        echo "\n[test] Warning: Could not drop '$testDb': " . $e->getMessage() . "\n";
    }
});
