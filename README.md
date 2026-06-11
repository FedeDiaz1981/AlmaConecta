# AlmaConecta

Buscador de profesionales con perfiles revisados y aprobados por admins.
Construido con Laravel, Blade, Tailwind, Bootstrap y DataTables.

## Requisitos

- PHP 8.3+
- Composer
- Node 18+
- SQLite, Postgres o MySQL

## Levantar en local

1. Copia el archivo de entorno:

```bash
cp .env.example .env
```

2. Verifica que exista la base SQLite local en `database/database.sqlite`.
   Si no esta creada, puedes generarla con:

```powershell
New-Item -ItemType File -Force .\database\database.sqlite
```

3. Instala dependencias de PHP:

```bash
composer install
```

4. Genera la key de la aplicacion:

```bash
php artisan key:generate
```

5. Ejecuta migraciones:

```bash
php artisan migrate
```

6. Instala dependencias de frontend:

```bash
npm install
```

7. Levanta Vite:

```bash
npm run dev
```

8. Levanta Laravel:

```bash
php artisan serve
```

## Opcion con Docker

Si no tenes PHP instalado en tu maquina, esta es la forma mas simple:

```bash
docker compose up --build
```

Luego abrí:

- `http://localhost:8000`

## Notas utiles

- El `.env.example` ya viene preparado para local con `DB_CONNECTION=sqlite`,
  `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` y `SESSION_DRIVER=cookie`.
- El archivo local [`.env`](./.env) deja todo listo para correr con Docker y
  SQLite sin instalar PHP en el host.
- Si ves errores de config despues de cambiar `.env`, ejecuta:

```bash
php artisan optimize:clear
```

- Si el proyecto usa subida de archivos, crea el enlace publico:

```bash
php artisan storage:link
```

## Accesos de diagnostico

- `/healthz` para verificar app y DB.
- `/whoami` para ver el usuario autenticado.
