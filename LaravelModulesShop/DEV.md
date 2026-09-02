# Production Testing

Lokalne testowanie produkcji.

## Mysql

```sql
-- Tworzenie bazy danych
CREATE DATABASE IF NOT EXISTS testing_production
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Tworzenie nowego użytkownika (zmień nazwę i hasło według uznania)
CREATE USER 'user_test'@'localhost' IDENTIFIED BY 'TajneHaslo123';

-- Nadanie pełnych uprawnień temu użytkownikowi TYLKO do tej jednej bazy testowej
GRANT ALL PRIVILEGES ON testing_production.* TO 'user_test'@'localhost';

-- Przeładowanie uprawnień
FLUSH PRIVILEGES;
```

## Config

phpunit.production.xml

```xml
<!-- Mówimy aplikacji, że ma zachowywać się jak na produkcji -->
<env name="APP_ENV" value="production"/>
<env name="APP_DEBUG" value="false"/>

<!-- Nadpisujemy bazę z pliku .env na naszą lokalną bazę testową -->
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="3306"/>
<env name="DB_DATABASE" value="testing_production"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value=""/>

<!-- Nadpisujemy kodowanie, aby MariaDB nie rzuciła błędem -->
<env name="DB_CHARSET" value="utf8mb4"/>
<env name="DB_COLLATION" value="utf8mb4_unicode_ci"/>

<!-- Blokujemy wysyłanie realnych maili -->
<env name="MAIL_MAILER" value="log"/>
<env name="QUEUE_CONNECTION" value="sync"/>
```

## Testing

```sh
php artisan optimize:clear
php artisan test --configuration=phpunit.production.xml
php artisan optimize:clear
```

## Seeders

```sh
php artisan migrate:fresh --seed
php artisan db:seed --class=UserSeeder
```

## Roles

```txt
1. Nie przypisuj permissions do roli (nigdy)!
2. Roles - określają do jakiej części panelu użytkownik ma dostęp (shop, blog, partner).
3. Permissions - określają co w tej części może zrobić (edit, create, delete, view).

app/
└── Enums/
    ├── UserRole.php
    └── Permissions/
        ├── SystemPermission.php
        ├── BlogPermission.php
        ├── FinancePermission.php
        ├── PartnerPermission.php
        └── CommunityPermission.php
```
