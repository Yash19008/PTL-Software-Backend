## Setup

Create two database  

- `main_tenx` & `tenant` (You can create different name as per your system/personal preference)

- ```cp .env.example .env```  
- Update `main_tenx` database connection with your database creds in `.env` file

- run `php artisan migrate` to migrate && `php artisan db:seed` to set initial data

- After `db:seed` check `main_tenx.tenants` table  
- Update entry 1 with tenant database credentials.

- Finally run `php artisan migrate:tenant` && php artisan `passport:install:tenant`

## Tenant Guide

- All Tenant request should be have middleware `enforce.tenancy`
- While testing API, Make sure you add `X-TENANT-ID` header with value of tenant_id
- Each Tenant model should have `protected $connection = 'tenant'`
- All migrations related to tenant *must* be placed under `migrations\tenant` folder. To help with that, you can use `php artisan make:migration:tenant {name} {--table=?}`. Check below artisan command section for more commands on tenant.



## i18n Lang

-  For future support of i18n, always use ```__('message-or-text-here')```

## Tenant Artisan Commands

Commands mostly have same syntax as the original ones,
`?` signifies optional parameters.

- `php artisan passport:install:tenant <tenant_id?>`
- `php artisan make:migration:tenant {name} {--table?}`
- `php artisan migrate:tenant <tenant_id?> {--force?}`
- `php artisan db:seed:tenant <tenant_id?> {--class?}`

## For every manually create new tenant.

- Make entry in `main_tenx.tenants` table & run following commans
- `php artisan migrate:tenant`
- `php artisan passport:install:tenant`

## QR Login Setup

- After pulling the latest changes, run `composer dump-autoload` to install and register the QR package.

- Run `php artisan migrate --path=/database/migrations/2026_01_18_201930_create_qr_sessions_table.php` to migrate the qr_sessions table.

- Start the development server using `php artisan serve --host=127.0.0.1 --port=8000`.