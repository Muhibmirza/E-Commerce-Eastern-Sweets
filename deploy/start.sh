#!/bin/sh
set -eu

DB_HOST_VALUE="${DB_HOST:-${MYSQLHOST:-127.0.0.1}}"
DB_PORT_VALUE="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_NAME_VALUE="${DB_NAME:-${MYSQLDATABASE:-eastern_sweets}}"
DB_USER_VALUE="${DB_USER:-${MYSQLUSER:-root}}"
DB_PASS_VALUE="${DB_PASS:-${MYSQLPASSWORD:-}}"
export DB_HOST_VALUE DB_PORT_VALUE DB_NAME_VALUE DB_USER_VALUE DB_PASS_VALUE

export APACHE_HTTP_PORT="${PORT:-8080}"
sed -i "s/^Listen 80$/Listen ${APACHE_HTTP_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \\*:8080>/<VirtualHost *:${APACHE_HTTP_PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ ! -e /var/www/html/uploads/.initialized ]; then
    cp -a /opt/eastern-sweets-uploads/. /var/www/html/uploads/
    touch /var/www/html/uploads/.initialized
fi
chown -R www-data:www-data /var/www/html/uploads

attempt=0
until php -r '
    try {
        new PDO(
            "mysql:host=" . getenv("DB_HOST_VALUE") . ";port=" . getenv("DB_PORT_VALUE") . ";dbname=" . getenv("DB_NAME_VALUE"),
            getenv("DB_USER_VALUE"),
            getenv("DB_PASS_VALUE"),
            [PDO::ATTR_TIMEOUT => 3]
        );
    } catch (Throwable $e) {
        exit(1);
    }
'; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 30 ]; then
        echo "Database did not become ready in time." >&2
        exit 1
    fi
    sleep 2
done

TABLE_COUNT="$(mysql \
    --skip-ssl \
    -h "$DB_HOST_VALUE" \
    -P "$DB_PORT_VALUE" \
    -u "$DB_USER_VALUE" \
    --password="$DB_PASS_VALUE" \
    -Nse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME_VALUE}' AND table_name='static_pages'")"

if [ "$TABLE_COUNT" = "0" ]; then
    echo "Empty database detected; importing initial schema and seed data."
    sed -e '/^CREATE DATABASE IF NOT EXISTS /d' -e '/^USE eastern_sweets;/d' /var/www/html/database/eastern_sweets.sql |
        mysql \
            --skip-ssl \
            -h "$DB_HOST_VALUE" \
            -P "$DB_PORT_VALUE" \
            -u "$DB_USER_VALUE" \
            --password="$DB_PASS_VALUE" \
            "$DB_NAME_VALUE"
fi

php /var/www/html/scripts/seed_category_expansion.php

rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

exec apache2-foreground
