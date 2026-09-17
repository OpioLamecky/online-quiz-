<?php
date_default_timezone_set('Africa/Kampala');

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'postgres';
$username = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: 5432;

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password;sslmode=require"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET TIME ZONE 'Africa/Kampala'");

    $pdo->exec("CREATE TABLE IF NOT EXISTS app_migrations (
        migration_name VARCHAR(255) PRIMARY KEY,
        applied_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    )");

    $migrationStmt = $pdo->prepare(
        "INSERT INTO app_migrations (migration_name) VALUES (?)
         ON CONFLICT (migration_name) DO NOTHING
         RETURNING migration_name"
    );
    $migrationStmt->execute(['20260917_fix_legacy_quiz_times']);

    if ($migrationStmt->fetchColumn()) {
        $pdo->exec("UPDATE quizzes
                    SET start_time = start_time - INTERVAL '3 hours',
                        end_time = end_time - INTERVAL '3 hours'");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
