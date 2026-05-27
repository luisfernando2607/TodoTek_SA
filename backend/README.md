# Todostock — Backend

API REST desarrollada con Laravel 13 como parte de la prueba técnica para **Todotek S.A.**

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP         | 8.3+          |
| Composer    | 2.x           |
| PostgreSQL  | 15+           |
| Node.js     | 18+ (para Vite) |

---

## Instalación y ejecución

```bash
# 1. Clonar el repositorio
git clone https://github.com/luisfernando2607/TodoTek_SA.git
cd TodoTek_SA/backend

# 2. Instalar dependencias PHP
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 5. Ejecutar migraciones + seeders
php artisan migrate --seed

# 6. Crear enlace simbólico de storage (imágenes)
php artisan storage:link

# 7. Levantar servidor
php artisan serve
# → http://localhost:8000
```

### Modo desarrollo completo (servidor + queue + logs + vite)
```bash
composer run dev
```

---

## Variables de entorno (.env)

```env
APP_NAME=Todostock
APP_ENV=local
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todostock_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password_aqui

SANCTUM_STATEFUL_DOMAINS=localhost:4200
SESSION_DRIVER=cookie
FILESYSTEM_DISK=public

# Swagger
L5_SWAGGER_GENERATE_ALWAYS=true

# Telescope (solo desarrollo)
TELESCOPE_ENABLED=true
```

---

## Credenciales de prueba

| Campo    | Valor               |
|----------|---------------------|
| Email    | admin@todostock.com |
| Password | password            |

> Creadas automáticamente por `UserSeeder` al ejecutar `php artisan migrate --seed`

---

## Stack y dependencias principales

| Paquete                | Versión | Uso                              |
|------------------------|---------|----------------------------------|
| Laravel Framework      | 13.8    | Framework principal              |
| Laravel Sanctum        | 4.3     | Autenticación por tokens API     |
| DarkaOnline L5-Swagger | 8.6     | Documentación interactiva de API |
| Laravel Telescope      | 5.20    | Debug y monitoreo en desarrollo  |
| DomPDF                 | 3.1     | Generación de PDFs               |
| Laravel Pint           | 1.27    | Formateador de código PHP        |
| PHP                    | 8.3+    | Lenguaje base                    |
| PostgreSQL             | 15+     | Base de datos                    |

---

## Estructura del proyecto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php            → Login, logout, me
│   │   │   ├── ProductController.php         → CRUD de productos
│   │   │   ├── ProductImageController.php    → Upload y eliminación de imágenes
│   │   │   ├── StockController.php           → Movimientos de stock
│   │   │   ├── CategoryController.php        → CRUD de categorías
│   │   │   ├── ClientController.php          → CRUD de clientes
│   │   │   └── InvoiceController.php         → Facturación y cancelación
│   │   ├── Requests/
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── UpdateProductRequest.php
│   │   │   ├── StoreClientRequest.php
│   │   │   ├── UpdateClientRequest.php
│   │   │   └── StoreInvoiceRequest.php
│   │   └── Resources/
│   ├── Models/
│   │   ├── User.php                          → HasApiTokens (Sanctum)
│   │   ├── Product.php                       → SoftDeletes, accessor price_with_tax
│   │   ├── Category.php
│   │   ├── ProductImage.php                  → Accessor URL pública
│   │   ├── StockMovement.php                 → Auditoría de stock
│   │   ├── Client.php                        → SoftDeletes
│   │   ├── Invoice.php
│   │   └── InvoiceItem.php                   → Snapshot de precios
│   └── Services/
│       ├── ProductService.php                → Paginación con filtros ilike
│       ├── StockService.php                  → Movimientos con DB::transaction
│       ├── ImageService.php                  → Upload múltiple + promoción automática
│       └── InvoiceService.php                → Facturación + reversión de stock
├── database/
│   ├── migrations/                           → 8 migraciones en orden de dependencia
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── CategorySeeder.php
│       ├── ProductSeeder.php                 → 12 productos de prueba
│       └── ClientSeeder.php                  → 5 clientes de prueba
├── routes/
│   └── api.php                               → Rutas públicas y protegidas (auth:sanctum)
└── storage/app/public/products/              → Imágenes subidas por producto
```

---

## Base de datos

### Tablas (8 en total)

| Tabla             | Descripción                                |
|-------------------|--------------------------------------------|
| `users`           | Usuarios del sistema                       |
| `categories`      | Categorías de productos                    |
| `products`        | Catálogo con precio, IVA y stock           |
| `product_images`  | Imágenes por producto (múltiples)          |
| `stock_movements` | Auditoría de entradas, salidas y ajustes   |
| `clients`         | Clientes con RUC/cédula único              |
| `invoices`        | Cabecera de facturas                       |
| `invoice_items`   | Detalle con snapshot de precios al vender  |

### Script SQL alternativo
```bash
psql -U postgres -d todostock_db -f database/todostock_schema.sql
```

---

## API REST

### Rutas públicas
| Método | Endpoint        | Descripción                  |
|--------|-----------------|------------------------------|
| POST   | /api/auth/login | Login → retorna Bearer token |

### Rutas protegidas (`Authorization: Bearer {token}`)
| Método | Endpoint                            | Descripción                     |
|--------|-------------------------------------|---------------------------------|
| POST   | /api/auth/logout                    | Invalida el token actual        |
| GET    | /api/auth/me                        | Usuario autenticado             |
| GET    | /api/products                       | Listado paginado con filtros    |
| POST   | /api/products                       | Crear producto                  |
| PUT    | /api/products/{id}                  | Actualizar producto             |
| DELETE | /api/products/{id}                  | SoftDelete producto             |
| POST   | /api/products/{id}/images           | Subir imágenes (multipart)      |
| DELETE | /api/products/{id}/images/{imageId} | Eliminar imagen específica      |
| POST   | /api/products/{id}/stock            | Registrar movimiento de stock   |
| GET    | /api/categories                     | Listar categorías               |
| POST   | /api/categories                     | Crear categoría                 |
| GET    | /api/clients                        | Listar clientes paginado        |
| POST   | /api/clients                        | Crear cliente                   |
| GET    | /api/invoices                       | Listar facturas paginado        |
| POST   | /api/invoices                       | Crear factura (descuenta stock) |
| GET    | /api/invoices/{id}                  | Detalle con ítems               |
| PATCH  | /api/invoices/{id}/cancel           | Cancelar + revertir stock       |

---

## Herramientas de desarrollo

### Swagger UI
```
http://localhost:8000/api/documentation
```

### Laravel Telescope
```
http://localhost:8000/telescope
```
> Desactivar en producción: `TELESCOPE_ENABLED=false`

### Colección Postman
`/docs/Todostock_v2.postman_collection.json`
- Variables de entorno para local y staging
- Tests automáticos en cada endpoint
- Token guardado automáticamente al hacer Login

---

## Scripts Composer

```bash
composer run dev      # Servidor + queue + logs + vite en paralelo
composer run test     # Ejecutar tests PHPUnit
composer run setup    # Instalación completa desde cero
```

---

## Supuestos técnicos

| Decisión | Detalle |
|----------|---------|
| **IVA configurable** | Cada producto tiene `tax_rate` propio (0%, 5%, 12%, 15%) |
| **SoftDeletes** | `products` y `clients` se marcan con `deleted_at`, no se borran físicamente |
| **Snapshot en facturas** | `invoice_items` guarda `product_name`, `unit_price` y `tax_rate` al momento de vender |
| **Auditoría de stock** | `stock_movements` registra cada cambio con usuario, cantidad y stock antes/después |
| **Número de factura** | Generado en `InvoiceService` como `FAC-00001` secuencial |
| **Storage público** | Imágenes servidas por URL pública vía `php artisan storage:link` |
| **Paginación server-side** | Eloquent `->paginate(15)` — el frontend nunca carga todos los registros |
| **ilike en filtros** | Búsquedas case-insensitive en PostgreSQL |