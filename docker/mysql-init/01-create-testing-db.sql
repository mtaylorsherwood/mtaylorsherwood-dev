-- Runs automatically on a fresh `db` volume (mysql's docker-entrypoint-initdb.d
-- convention). Gives the test suite its own database so `php artisan test`
-- no longer wipes the seeded dev database via RefreshDatabase/migrate:fresh.
CREATE DATABASE IF NOT EXISTS `mtsdev-testing`;
GRANT ALL PRIVILEGES ON `mtsdev-testing`.* TO 'mtsdev'@'%';
FLUSH PRIVILEGES;
