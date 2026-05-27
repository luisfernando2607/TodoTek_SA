import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProductService } from '../../core/services/product.service';
import { CategoryService } from '../../core/services/api.services';
import { NotificationService } from '../../core/services/notification.service';
import { StockModalComponent } from '../products/stock-modal.component';
import { Category, Product } from '../../core/models/models';

@Component({
  selector: 'app-stock-page',
  standalone: true,
  imports: [CommonModule, FormsModule, StockModalComponent],
  template: `
<div class="container-fluid py-4">

  <!-- Summary cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary bg-opacity-10 p-3">
            <i class="bi bi-box-seam fs-4 text-primary"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold">{{ totalProducts }}</div>
            <small class="text-muted">Productos</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-warning bg-opacity-10 p-3">
            <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold text-warning">{{ lowStockCount }}</div>
            <small class="text-muted">Stock bajo</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-danger bg-opacity-10 p-3">
            <i class="bi bi-x-circle fs-4 text-danger"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold text-danger">{{ outOfStockCount }}</div>
            <small class="text-muted">Sin stock</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-success bg-opacity-10 p-3">
            <i class="bi bi-archive fs-4 text-success"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold text-success">{{ totalStock }}</div>
            <small class="text-muted">Unidades totales</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Buscar</label>
          <input class="form-control form-control-sm" [(ngModel)]="search"
            placeholder="Nombre o SKU..." (keyup.enter)="applyFilters()">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Filtrar stock</label>
          <select class="form-select form-select-sm" [(ngModel)]="stockFilter">
            <option value="">Todos</option>
            <option value="low">Stock bajo</option>
            <option value="out">Sin stock</option>
            <option value="ok">Stock suficiente</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Categoría</label>
          <select class="form-select form-select-sm" [(ngModel)]="categoryFilter">
            <option value="">Todas</option>
            <option *ngFor="let c of categories" [value]="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
          <button class="btn btn-primary btn-sm flex-grow-1" (click)="applyFilters()">
            <i class="bi bi-search me-1"></i>Filtrar
          </button>
          <button class="btn btn-outline-secondary btn-sm" (click)="clearFilters()" title="Limpiar">
            <i class="bi bi-x-lg"></i>
          </button>
          <button class="btn btn-outline-info btn-sm" (click)="load()" title="Recargar">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading -->
  <div *ngIf="loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
    <p class="text-muted mt-2">Cargando stock...</p>
  </div>

  <!-- Product stock cards -->
  <div *ngIf="!loading && filteredProducts.length === 0" class="text-center py-5 text-muted">
    <i class="bi bi-archive fs-1 d-block mb-2"></i>
    No se encontraron productos.
  </div>

  <div class="row g-3" *ngIf="!loading">
    <div class="col-md-6 col-lg-4" *ngFor="let p of filteredProducts">
      <div class="card border-0 shadow-sm h-100" [class.border-warning]="p.is_low_stock && p.stock > 0"
        [class.border-danger]="p.stock === 0">
        <div class="card-body">
          <div class="d-flex align-items-start gap-3 mb-3">
            <img *ngIf="p.main_image" [src]="p.main_image.url" class="rounded object-fit-cover"
              width="48" height="48" [alt]="p.name">
            <div *ngIf="!p.main_image" class="bg-light rounded d-flex align-items-center justify-content-center"
              style="width:48px;height:48px;min-width:48px">
              <i class="bi bi-image text-muted"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-semibold text-truncate">{{ p.name }}</div>
              <small class="text-muted font-monospace">{{ p.sku }}</small>
              <span class="badge bg-secondary ms-1">{{ categoryName(p.category_id) }}</span>
            </div>
            <div class="text-end flex-shrink-0" style="min-width:60px">
              <div class="fs-4 fw-bold"
                [class.text-success]="p.stock >= p.stock_min * 2"
                [class.text-warning]="p.is_low_stock && p.stock > 0"
                [class.text-danger]="p.stock === 0">
                {{ p.stock }}
              </div>
              <small class="text-muted" style="font-size:.65rem">stock</small>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
              <span class="text-muted">Mín: {{ p.stock_min }}</span>
              <span class="text-muted">Stock actual</span>
            </div>
            <div class="progress" style="height:8px">
              <div class="progress-bar"
                [class.bg-success]="p.stock >= p.stock_min * 2"
                [class.bg-warning]="p.is_low_stock && p.stock > 0"
                [class.bg-danger]="p.stock === 0"
                [style.width.%]="stockPercent(p)"
                role="progressbar">
              </div>
            </div>
          </div>

          <!-- Quick actions -->
          <div class="d-flex gap-1 mb-2 flex-wrap">
            <button class="btn btn-sm btn-outline-success" (click)="quickMove(p, 'entry', 1)"
              [disabled]="savingId === p.id" title="+1">
              <i class="bi bi-plus-circle"></i> 1
            </button>
            <button class="btn btn-sm btn-outline-success" (click)="quickMove(p, 'entry', 5)"
              [disabled]="savingId === p.id" title="+5">
              <i class="bi bi-plus-circle"></i> 5
            </button>
            <button class="btn btn-sm btn-outline-success" (click)="quickMove(p, 'entry', 10)"
              [disabled]="savingId === p.id" title="+10">
              <i class="bi bi-plus-circle"></i> 10
            </button>
            <button class="btn btn-sm btn-outline-danger" (click)="quickMove(p, 'exit', 1)"
              [disabled]="savingId === p.id || p.stock < 1" title="-1">
              <i class="bi bi-dash-circle"></i> 1
            </button>
            <button class="btn btn-sm btn-outline-primary ms-auto" (click)="openStockModal(p)"
              title="Movimiento personalizado">
              <i class="bi bi-arrow-left-right"></i>
            </button>
          </div>

          <!-- Saving spinner -->
          <div *ngIf="savingId === p.id" class="text-center py-1">
            <div class="spinner-border spinner-border-sm text-primary"></div>
          </div>

          <!-- Toggle history -->
          <button class="btn btn-sm btn-link text-muted p-0 w-100 text-decoration-none"
            (click)="toggleHistory(p)">
            <i class="bi" [class.bi-chevron-down]="historyId !== p.id"
              [class.bi-chevron-up]="historyId === p.id"></i>
            {{ historyId === p.id ? 'Ocultar' : 'Ver' }} movimientos
          </button>

          <div *ngIf="historyId === p.id" class="mt-2">
            <div *ngIf="historyLoading" class="text-center py-2">
              <div class="spinner-border spinner-border-sm text-muted"></div>
            </div>
            <div *ngIf="!historyLoading && (!historyData || historyData.length === 0)"
              class="text-muted small text-center py-2">Sin movimientos registrados.</div>
            <div *ngFor="let m of historyData" class="d-flex justify-content-between align-items-center small py-1 border-bottom">
              <div>
                <span class="badge me-1"
                  [class.bg-success]="m.type === 'entry'"
                  [class.bg-danger]="m.type === 'exit'"
                  [class.bg-warning]="m.type === 'adjustment'">
                  {{ m.type === 'entry' ? 'E' : (m.type === 'exit' ? 'S' : 'A') }}
                </span>
                <span *ngIf="m.type === 'entry'">+</span>
                <span *ngIf="m.type === 'exit'">-</span>
                {{ m.quantity }}
                <span class="text-muted" *ngIf="m.reason"> · {{ m.reason }}</span>
              </div>
              <small class="text-muted">{{ m.created_at | date:'dd/MM HH:mm' }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Stock modal -->
<app-stock-modal
  *ngIf="showModal"
  [product]="selectedProduct!"
  (updated)="onStockUpdated($event)"
  (closed)="showModal = false"
></app-stock-modal>
  `,
})
export class StockPageComponent implements OnInit {
  products: Product[] = [];
  categories: Category[] = [];
  loading = false;

  search = '';
  stockFilter = '';
  categoryFilter = '';

  showModal = false;
  selectedProduct: Product | null = null;
  savingId: number | null = null;

  // History
  historyId: number | null = null;
  historyData: any[] = [];
  historyLoading = false;

  constructor(
    private productSvc: ProductService,
    private categorySvc: CategoryService,
    private notify: NotificationService,
  ) {}

  ngOnInit(): void {
    this.categorySvc.getAll().subscribe(cats => this.categories = cats);
    this.load();
  }

  load(): void {
    this.loading = true;
    this.productSvc.getAll({ per_page: 200 }).subscribe({
      next: res => {
        this.products = res.data;
        this.loading = false;
      },
      error: () => {
        this.notify.error('Error al cargar productos.');
        this.loading = false;
      },
    });
  }

  get totalProducts(): number { return this.products.length; }
  get lowStockCount(): number { return this.products.filter(p => p.is_low_stock && p.stock > 0).length; }
  get outOfStockCount(): number { return this.products.filter(p => p.stock === 0).length; }
  get totalStock(): number { return this.products.reduce((s, p) => s + p.stock, 0); }

  get filteredProducts(): Product[] {
    return this.products.filter(p => {
      if (this.search) {
        const q = this.search.toLowerCase();
        if (!p.name.toLowerCase().includes(q) && !p.sku.toLowerCase().includes(q)) return false;
      }
      if (this.stockFilter === 'low' && !(p.is_low_stock && p.stock > 0)) return false;
      if (this.stockFilter === 'out' && p.stock !== 0) return false;
      if (this.stockFilter === 'ok' && (p.is_low_stock || p.stock === 0)) return false;
      if (this.categoryFilter && p.category_id !== Number(this.categoryFilter)) return false;
      return true;
    });
  }

  stockPercent(p: Product): number {
    const target = p.stock_min * 3 || 1;
    return Math.min(100, Math.round((p.stock / target) * 100));
  }

  applyFilters(): void {}
  clearFilters(): void { this.search = ''; this.stockFilter = ''; this.categoryFilter = ''; }

  quickMove(p: Product, type: string, qty: number): void {
    this.savingId = p.id;
    this.productSvc.moveStock(p.id, { type, quantity: qty } as any).subscribe({
      next: res => {
        p.stock = res.stock;
        p.is_low_stock = res.low_stock;
        this.savingId = null;
        if (res.low_stock) this.notify.stockWarning(p.name, res.stock);
      },
      error: err => {
        this.savingId = null;
        this.notify.error(err.error?.message ?? 'Error al mover stock.');
      },
    });
  }

  openStockModal(p: Product): void {
    this.selectedProduct = p;
    this.showModal = true;
  }

  onStockUpdated(result: { stock: number; low_stock: boolean; warning?: string }): void {
    this.showModal = false;
    if (this.selectedProduct) {
      this.selectedProduct.stock = result.stock;
      this.selectedProduct.is_low_stock = result.low_stock;
    }
    if (result.low_stock && this.selectedProduct) {
      this.notify.stockWarning(this.selectedProduct.name, result.stock);
    }
  }

  toggleHistory(p: Product): void {
    if (this.historyId === p.id) {
      this.historyId = null;
      return;
    }
    this.historyId = p.id;
    this.historyLoading = true;
    this.historyData = [];
    this.productSvc.getStockHistory(p.id).subscribe({
      next: res => {
        this.historyData = res.data ?? res;
        this.historyLoading = false;
      },
      error: () => {
        this.historyLoading = false;
      },
    });
  }

  categoryName(id: number): string {
    return this.categories.find(c => c.id === id)?.name ?? '—';
  }
}
