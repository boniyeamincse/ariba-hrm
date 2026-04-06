#!/usr/bin/env bash
set -e

echo "Waiting for MySQL database to be fully ready and accepting connections..."

cat << 'EOF' > wait_for_db.php
<?php
$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USERNAME') ?: 'hms';
$pass = getenv('DB_PASSWORD') ?: 'hms';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query("SELECT 1");
    exit(0);
} catch (PDOException $e) {
    exit(1);
}
EOF

TRIES=0
MAX_TRIES=30
until php wait_for_db.php; do
    echo "  -> Database is still initializing... sleeping ($TRIES/$MAX_TRIES)"
    sleep 3
    TRIES=$((TRIES+1))
    if [ "$TRIES" -ge "$MAX_TRIES" ]; then
        echo "Error: Database did not become ready in time."
        rm wait_for_db.php
        exit 1
    fi
done
rm wait_for_db.php

echo "================================================="
echo "✅ Database is ready!"
echo "================================================="

echo "🔄 Running Auto Migrations..."
php artisan migrate --force

echo "🌱 Running Auto Seeders..."
php artisan db:seed --force
echo "================================================="
echo "✅ Project Database is 100% Up-To-Date!"
echo "================================================="

echo "🚀 Starting Laravel Development Server..."
if [ $# -eq 0 ]; then
    exec php artisan serve --host=0.0.0.0 --port=8000
else
    exec "$@"
fi
