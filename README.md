# AlmaConecta

Buscador de profesionales con perfiles revisados y aprobados por admins.
Construido con **Laravel**, **Blade**, **Tailwind/Bootstrap**, **DataTables**.

## Requisitos
- PHP 8.3+ y Composer
- Node 18+
- SQLite / Postgres / MySQL (a elección)

## Setup local
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate         # o migrate --seed si tenés seeders
npm install
npm run dev                 # o npm run build
php artisan serve
