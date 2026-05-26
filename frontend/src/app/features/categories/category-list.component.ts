import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { CategoryService } from '../../core/services/api.services';
import { NotificationService } from '../../core/services/notification.service';
import { Category } from '../../core/models/models';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-category-list',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  template: `
<div class="container-fluid py-4">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Categorías</h4>
      <small class="text-muted">{{ categories.length }} categorías registradas</small>
    </div>
    <button class="btn btn-primary" (click)="openCreate()">
      <i class="bi bi-plus-lg me-1"></i>Nueva Categoría
    </button>
  </div>

  <!-- Tarjetas -->
  <div class="row g-3" *ngIf="!loading">
    <div class="col-12" *ngIf="categories.length === 0">
      <div class="text-center py-5 text-muted">
        <i class="bi bi-tags fs-2 d-block mb-2"></i>No hay categorías registradas.
      </div>
    </div>
    <div class="col-md-4 col-lg-3" *ngFor="let c of categories">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary rounded-pill">{{ c.products_count }} productos</span>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-primary" (click)="openEdit(c)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-outline-danger" (click)="delete(c)">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <h6 class="fw-bold mb-1">{{ c.name }}</h6>
          <small class="text-muted font-monospace">{{ c.slug }}</small>
          <p class="text-muted small mt-2 mb-0">{{ c.description || 'Sin descripción' }}</p>
        </div>
      </div>
    </div>
  </div>

  <div *ngIf="loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
  </div>
</div>

<!-- Modal -->
<ng-container *ngIf="showModal">
  <div class="modal-backdrop fade show"></div>
  <div class="modal fade show d-block" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-sm-down">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="bi me-2"
              [class.bi-tag]="!editing"
              [class.bi-pencil-square]="editing"></i>
            {{ editing ? 'Editar categoría' : 'Nueva categoría' }}
          </h5>
          <button type="button" class="btn-close btn-close-white" (click)="showModal=false"></button>
        </div>
        <div class="modal-body">
          <form [formGroup]="form">
            <div class="mb-3">
              <label class="form-label fw-semibold">Nombre *</label>
              <input class="form-control" formControlName="name"
                [class.is-invalid]="isInvalid('name')"
                placeholder="Ej: Electrónica"
                (input)="autoSlug()">
              <div class="invalid-feedback">El nombre es requerido.</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Slug *</label>
              <input class="form-control font-monospace" formControlName="slug"
                [class.is-invalid]="isInvalid('slug')"
                placeholder="electronica">
              <small class="text-muted">Se genera automáticamente del nombre.</small>
              <div class="invalid-feedback">El slug es requerido.</div>
            </div>
            <div class="mb-1">
              <label class="form-label fw-semibold">Descripción</label>
              <textarea class="form-control" formControlName="description" rows="2"
                placeholder="Descripción opcional..."></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" (click)="showModal=false">Cancelar</button>
          <button class="btn btn-primary" (click)="submit()" [disabled]="saving">
            <span *ngIf="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ saving ? 'Guardando...' : (editing ? 'Actualizar' : 'Crear') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</ng-container>
  `,
})
export class CategoryListComponent implements OnInit {
  categories: Category[] = [];
  loading = false; saving = false; showModal = false; editing = false;
  selectedCategory: Category | null = null;
  form!: FormGroup;

  constructor(
    private svc:    CategoryService,
    private notify: NotificationService,
    private fb:     FormBuilder,
    private http:   HttpClient,
  ) {}

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading = true;
    this.svc.getAll().subscribe({
      next:  cats => { this.categories = cats; this.loading = false; },
      error: ()   => { this.notify.error('Error al cargar categorías.'); this.loading = false; },
    });
  }

  buildForm(c?: Category): void {
    this.form = this.fb.group({
      name:        [c?.name        ?? '', Validators.required],
      slug:        [c?.slug        ?? '', Validators.required],
      description: [c?.description ?? ''],
    });
  }

  autoSlug(): void {
    if (this.editing) return;
    const name = this.form.get('name')?.value ?? '';
    const slug = name.toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '');
    this.form.get('slug')?.setValue(slug, { emitEvent: false });
  }

  openCreate(): void {
    this.editing = false; this.selectedCategory = null;
    this.buildForm(); this.showModal = true;
  }

  openEdit(c: Category): void {
    this.editing = true; this.selectedCategory = c;
    this.buildForm(c); this.showModal = true;
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving = true;
    const url  = `${environment.apiUrl}/categories${this.editing ? '/' + this.selectedCategory!.id : ''}`;
    const req$ = this.editing
      ? this.http.put<Category>(url, this.form.value)
      : this.http.post<Category>(url, this.form.value);

    req$.subscribe({
      next:  () => { this.saving = false; this.showModal = false; this.load(); this.notify.success(this.editing ? 'Categoría actualizada.' : 'Categoría creada.'); },
      error: (err) => { this.saving = false; this.notify.error(err.error?.message ?? 'Error al guardar.'); },
    });
  }

  delete(c: Category): void {
    this.notify.confirmDelete(c.name).then(r => {
      if (!r.isConfirmed) return;
      this.http.delete(`${environment.apiUrl}/categories/${c.id}`).subscribe({
        next:  () => { this.notify.success('Categoría eliminada.'); this.load(); },
        error: (err) => this.notify.error(err.error?.message ?? 'No se pudo eliminar.'),
      });
    });
  }

  isInvalid(f: string): boolean { const c = this.form.get(f); return !!(c?.invalid && c?.touched); }
}
