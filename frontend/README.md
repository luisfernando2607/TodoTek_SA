# Todostock — Frontend

Sistema de gestión de productos desarrollado con Angular 19 como parte de la prueba técnica para **Todotek S.A.**

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|---------------|
| Node.js     | 18+           |
| Angular CLI | 19.2.20       |
| npm         | 9+            |

Instalar Angular CLI globalmente si no lo tienes:
```bash
npm install -g @angular/cli@19
```

---

## Instalación y ejecución

```bash
# 1. Clonar el repositorio
git clone https://github.com/luisfernando2607/TodoTek_SA.git
cd TodoTek_SA/frontend

# 2. Instalar dependencias
npm install

# 3. Levantar servidor de desarrollo
npm start
# → http://localhost:4200
```

> El archivo `proxy.conf.json` redirige automáticamente `/api/*` hacia `http://localhost:8000`, eliminando problemas de CORS en desarrollo.

---

## Variables de entorno

Editar `src/environments/environment.ts`:

```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
};
```

---

## Credenciales de prueba

| Campo    | Valor                  |
|----------|------------------------|
| Email    | admin@todostock.com    |
| Password | password               |

> Estas credenciales son creadas automáticamente por el `UserSeeder` del backend.

---

## Estructura del proyecto

```
src/
├── app/
│   ├── app.component.ts          → Componente raíz (solo <router-outlet>)
│   ├── app.config.ts             → Providers: Router, HttpClient, AuthInterceptor
│   ├── app.routes.ts             → Rutas lazy-loaded con AuthGuard
│   │
│   ├── core/
│   │   ├── guards/
│   │   │   └── auth.guard.ts               → Protege rutas privadas
│   │   ├── interceptors/
│   │   │   └── auth.interceptor.ts         → Agrega Bearer token a cada petición
│   │   ├── models/
│   │   │   └── models.ts                   → Interfaces TypeScript del sistema
│   │   └── services/
│   │       ├── auth.service.ts             → Login, logout, Signal de usuario actual
│   │       ├── product.service.ts          → CRUD + imágenes + movimientos de stock
│   │       ├── api.services.ts             → CategoryService, ClientService, InvoiceService
│   │       └── notification.service.ts     → SweetAlert2: toasts, confirmaciones, alertas
│   │
│   ├── features/
│   │   ├── auth/
│   │   │   └── login.component             → Pantalla de login con validación reactiva
│   │   ├── products/
│   │   │   ├── product-list.component      → Tabla con filtros y paginación server-side
│   │   │   ├── product-form-modal.component → Modal crear/editar + upload de imágenes
│   │   │   └── stock-modal.component       → Modal entrada/salida/ajuste de stock
│   │   ├── categories/
│   │   │   └── category-list.component     → Tarjetas con auto-generación de slug
│   │   ├── clients/
│   │   │   └── client-list.component       → CRUD completo con modal inline
│   │   └── invoices/
│   │       ├── invoice-list.component      → Listado, detalle modal y cancelación
│   │       └── invoice-create.component    → Carrito con cálculo de IVA en tiempo real
│   │
│   └── layout/
│       ├── main-layout.component.ts        → Shell principal (sidebar + router-outlet)
│       └── sidebar/
│           └── sidebar.component           → Menú lateral colapsable con logout
│
├── environments/
│   └── environment.ts            → URL de la API
├── styles.scss                   → Bootstrap 5 + Bootstrap Icons + estilos globales
├── main.ts                       → Bootstrap de la aplicación
└── index.html                    → HTML base
```

---

## Stack y dependencias principales

| Paquete            | Versión   | Uso                              |
|--------------------|-----------|----------------------------------|
| Angular            | 19.2.0    | Framework principal              |
| Bootstrap          | 5.3.8     | Estilos y componentes UI         |
| Bootstrap Icons    | 1.13.1    | Iconografía                      |
| SweetAlert2        | 11.26.25  | Alertas, confirmaciones y toasts |
| RxJS               | 7.8.0     | Programación reactiva            |
| TypeScript         | 5.7.2     | Tipado estático                  |

---

## Módulos del sistema

### Productos
- Listado paginado con filtros por nombre, SKU, categoría y stock bajo
- Crear y editar mediante modal con preview de precio final (con IVA)
- Subida múltiple de imágenes (hasta 10, máx 2MB c/u)
- Gestión de imagen principal con promoción automática al eliminar

### Stock
- Registro de entradas, salidas y ajustes
- Preview del stock resultante antes de confirmar
- Alerta visual con SweetAlert2 cuando el stock queda por debajo del mínimo

### Categorías
- CRUD con tarjetas
- Auto-generación de slug desde el nombre

### Clientes
- CRUD completo con búsqueda por nombre y RUC/cédula
- SoftDelete (no se elimina si tiene facturas)

### Facturación
- Carrito de compra con cálculo automático de subtotal, IVA y total
- Soporte para múltiples productos con distintas tasas de IVA
- Cancelación de factura con reversión automática de stock

---

## Decisiones técnicas

| Decisión | Detalle |
|----------|---------|
| **Standalone components** | Sin NgModules, alineado con Angular 17+ best practices |
| **Lazy loading** | Cada feature carga su componente solo al navegar hacia él |
| **Signals** | `currentUser` en AuthService usa Signal de Angular para reactividad |
| **AuthInterceptor funcional** | Usa `HttpInterceptorFn` (API moderna, sin clases) |
| **Paginación server-side** | Angular nunca carga todos los registros; cada filtro dispara una nueva petición |
| **Proxy en desarrollo** | `proxy.conf.json` evita configurar CORS en localhost |

---

## Scripts disponibles

```bash
npm start        # Servidor de desarrollo en localhost:4200
npm run build    # Build de producción en /dist
npm test         # Ejecutar tests unitarios con Karma
```

---

## Conexión con el Backend

El frontend consume la API REST del backend Laravel ubicado en `/backend`. Asegúrate de que el backend esté corriendo antes de iniciar el frontend:

```bash
# En la carpeta backend:
php artisan serve   # → http://localhost:8000
```

Para más detalles sobre la API, consultar:
- **Swagger UI**: http://localhost:8000/api/documentation
- **Laravel Telescope**: http://localhost:8000/telescope
- **Colección Postman**: `/docs/Todostock_v2.postman_collection.json`
