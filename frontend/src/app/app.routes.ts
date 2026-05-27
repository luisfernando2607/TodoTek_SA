import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [
  // Pública
  {
    path: 'login',
    loadComponent: () =>
      import('./features/auth/login.component').then(m => m.LoginComponent),
  },

  // Shell protegida
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./layout/main-layout.component').then(m => m.MainLayoutComponent),
    children: [
      { path: '', redirectTo: 'products', pathMatch: 'full' },
      {
        path: 'products',
        loadComponent: () =>
          import('./features/products/product-list.component').then(m => m.ProductListComponent),
      },
      {
        path: 'categories',
        loadComponent: () =>
          import('./features/categories/category-list.component').then(m => m.CategoryListComponent),
      },
      {
        path: 'clients',
        loadComponent: () =>
          import('./features/clients/client-list.component').then(m => m.ClientListComponent),
      },
      {
        path: 'invoices',
        loadComponent: () =>
          import('./features/invoices/invoice-list.component').then(m => m.InvoiceListComponent),
      },
      {
        path: 'invoices/create',
        loadComponent: () =>
          import('./features/invoices/invoice-create.component').then(m => m.InvoiceCreateComponent),
      },
      {
        path: 'stock',
        loadComponent: () =>
          import('./features/stock/stock-page.component').then(m => m.StockPageComponent),
      },
    ],
  },

  // Cualquier ruta desconocida → login (no a '' para evitar loop)
  { path: '**', redirectTo: 'login' },
];