import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ProductService } from '../../core/services/product.service';
import { CategoryService } from '../../core/services/api.services';
import { NotificationService } from '../../core/services/notification.service';
import { Category, Product, ProductFilters } from '../../core/models/models';
import { ProductFormModalComponent } from './product-form-modal.component';
import { StockModalComponent } from './stock-modal.component';

@Component({
  selector: 'app-product-list',
  standalone: true,
  imports: [
    CommonModule, FormsModule,
    ProductFormModalComponent, StockModalComponent,
  ],
  templateUrl: './product-list.component.html',
})
export class ProductListComponent implements OnInit {

  products:    Product[]  = [];
  categories:  Category[] = [];
  loading      = false;
  totalPages   = 1;
  currentPage  = 1;
  totalRecords = 0;

  // Filtros
  filters: ProductFilters = { page: 1, per_page: 15 };

  // Modales
  showFormModal  = false;
  showStockModal = false;
  selectedProduct: Product | null = null;

  constructor(
    private productSvc:  ProductService,
    private categorySvc: CategoryService,
    private notify:      NotificationService,
  ) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadProducts();
  }

  loadCategories(): void {
    this.categorySvc.getAll().subscribe(cats => this.categories = cats);
  }

  loadProducts(): void {
    this.loading = true;
    this.productSvc.getAll({ ...this.filters, page: this.currentPage }).subscribe({
      next: res => {
        this.products     = res.data;
        this.totalPages   = res.last_page;
        this.totalRecords = res.total;
        this.loading      = false;
      },
      error: () => {
        this.notify.error('No se pudo cargar la lista de productos.');
        this.loading = false;
      },
    });
  }

  applyFilters(): void {
    this.currentPage = 1;
    this.loadProducts();
  }

  clearFilters(): void {
    this.filters = { page: 1, per_page: 15 };
    this.loadProducts();
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.totalPages) return;
    this.currentPage = page;
    this.loadProducts();
  }

  // ── CRUD ─────────────────────────────────────────────────────
  openCreate(): void {
    this.selectedProduct = null;
    this.showFormModal   = true;
  }

  openEdit(product: Product): void {
    this.selectedProduct = product;
    this.showFormModal   = true;
  }

  openStock(product: Product): void {
    this.selectedProduct  = product;
    this.showStockModal   = true;
  }

  onFormSaved(): void {
    this.showFormModal = false;
    this.loadProducts();
    this.notify.success(
      this.selectedProduct ? 'Producto actualizado.' : 'Producto creado correctamente.'
    );
  }

  onStockUpdated(result: { stock: number; low_stock: boolean; warning?: string }): void {
    this.showStockModal = false;
    this.loadProducts();
    this.notify.success('Movimiento de stock registrado.');
    if (result.low_stock && this.selectedProduct) {
      this.notify.stockWarning(this.selectedProduct.name, result.stock);
    }
  }

  deleteProduct(product: Product): void {
    this.notify.confirmDelete(product.name).then(res => {
      if (!res.isConfirmed) return;
      this.productSvc.delete(product.id).subscribe({
        next:  () => { this.notify.success('Producto eliminado.'); this.loadProducts(); },
        error: (err) => this.notify.error(err.error?.message ?? 'No se pudo eliminar.'),
      });
    });
  }

  // ── Helpers ──────────────────────────────────────────────────
  get pages(): number[] {
    return Array.from({ length: this.totalPages }, (_, i) => i + 1);
  }

  categoryName(id: number): string {
    return this.categories.find(c => c.id === id)?.name ?? '—';
  }
}
