<?php
header('Content-Type: text/plain');

$dsn = 'pgsql:host=ep-dawn-grass-addapmd2-pooler.c-2.us-east-1.aws.neon.tech;port=5432;dbname=neondb;sslmode=require;options=endpoint=ep-dawn-grass-addapmd2';
$user = 'neondb_owner';
$pass = 'npg_umAjIfx4kX9i';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 5,
    ]);
    echo "OK: connected\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}