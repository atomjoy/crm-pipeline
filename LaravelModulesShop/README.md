# App

## Production

Serwer produkcyjny dodaj w pliku **.env**.

### File .env

```sh
# SERWER PRODUKCYJNY (w pliku .env)
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prod_database
DB_USERNAME=prod_username
DB_PASSWORD=prod_password

# Wymuszenie kodowania znaków kompatybilnego z MariaDB / starszym MySQL
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# More config ...
```

### Migrate

```sh
# Clear cache
php artisan optimize:clear

# Update - Dodan nowe migracje nie usunie istniejących
php artisan migrate --force

# Refresh - Całkowicie wyczyści Twoją produkcyjną bazę danych, wypełni początkowymi danymi z seederów.
php artisan migrate:fresh --force --seed
```

### Testing

```sh
php artisan optimize:clear
php artisan test
```
