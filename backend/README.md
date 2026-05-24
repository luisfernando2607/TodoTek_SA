# Todostock — Backend (Laravel 11)

## Requisitos
- PHP 8.3+
- Composer
- PostgreSQL 15+

## Quick Start

```bash
# 1. Instalar dependencias
composer install

# 2. Copiar y configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Configurar base de datos en .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todostock_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# 4. Instalar Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 5. Ejecutar migraciones + datos de prueba
php artisan migrate --seed

# 6. Crear enlace storage (para imágenes)
php artisan storage:link

# 7. Levantar servidor
php artisan serve
```

## Credenciales de prueba (Seeder)
- Email: admin@todostock.com
- Password: password

## Estructura App
```
app/
├── Http/
│   ├── Controllers/Api/   AuthController, ProductController, ClientController
│   │                      InvoiceController, StockController, ProductImageController
│   │                      CategoryController
│   ├── Requests/          StoreProductRequest, UpdateProductRequest
│   │                      StoreClientRequest, UpdateClientRequest, StoreInvoiceRequest
│   └── Resources/         (API Resources opcionales para transformar respuestas)
├── Models/                User, Product, Category, ProductImage
│                          StockMovement, Client, Invoice, InvoiceItem
└── Services/              ProductService, StockService, ImageService, InvoiceService
```

## Variables de entorno necesarias
```env
APP_NAME=Todostock
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todostock_db
DB_USERNAME=postgres
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:4200
SESSION_DRIVER=cookie
FILESYSTEM_DISK=public
```

## Supuestos técnicos
- IVA configurable por producto (0%, 5%, 12%, 15%)
- SoftDeletes en products y clients
- Snapshot de precios en invoice_items
- Auditoría de stock en stock_movements
- Número de factura: FAC-00001, FAC-00002...