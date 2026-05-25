import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ClientService } from '../../core/services/api.services';
import { NotificationService } from '../../core/services/notification.service';
import { Client } from '../../core/models/models';

@Component({
  selector: 'app-client-list',
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule],
  template: `
<div class="container-fluid py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Clientes</h4>
      <small class="text-muted">{{ total }} registros</small>
    </div>
    <button class="btn btn-primary" (click)="openCreate()">
      <i class="bi bi-person-plus me-1"></i>Nuevo Cliente
    </button>
  </div>

  <!-- Filtro -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <input class="form-control form-control-sm" [(ngModel)]="searchName"
            placeholder="Buscar por nombre..." (keyup.enter)="load()">
        </div>
        <div class="col-md-3">
          <input class="form-control form-control-sm" [(ngModel)]="searchId"
            placeholder="RUC / Cédula..." (keyup.enter)="load()">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary btn-sm" (click)="load()">
            <i class="bi bi-search me-1"></i>Buscar
          </button>
          <button class="btn btn-outline-secondary btn-sm ms-1" (click)="clearFilters()">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>Nombre</th>
              <th>RUC / Cédula</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr *ngIf="loading">
              <td colspan="6" class="text-center py-5">
                <div class="spinner-border text-primary"></div>
              </td>
            </tr>
            <tr *ngIf="!loading && clients.length === 0">
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="bi bi-people fs-2 d-block mb-2"></i>No se encontraron clientes.
              </td>
            </tr>
            <tr *ngFor="let c of clients">
              <td class="fw-semibold">{{ c.name }}</td>
              <td class="font-monospace">{{ c.identification }}</td>
              <td>{{ c.email || '—' }}</td>
              <td>{{ c.phone || '—' }}</td>
              <td class="text-center">
                <span class="badge" [class.bg-success]="c.active" [class.bg-secondary]="!c.active">
                  {{ c.active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" (click)="openEdit(c)"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-outline-danger" (click)="delete(c)"><i class="bi bi-trash"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top" *ngIf="totalPages > 1">
        <small class="text-muted">Página {{ currentPage }} de {{ totalPages }}</small>
        <nav><ul class="pagination pagination-sm mb-0">
          <li class="page-item" [class.disabled]="currentPage===1">
            <a class="page-link" (click)="goToPage(currentPage-1)"><i class="bi bi-chevron-left"></i></a>
          </li>
          <li class="page-item" *ngFor="let p of pages" [class.active]="p===currentPage">
            <a class="page-link" (click)="goToPage(p)">{{ p }}</a>
          </li>
          <li class="page-item" [class.disabled]="currentPage===totalPages">
            <a class="page-link" (click)="goToPage(currentPage+1)"><i class="bi bi-chevron-right"></i></a>
          </li>
        </ul></nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal Crear / Editar -->
<ng-container *ngIf="showModal">
  <div class="modal-backdrop fade show"></div>
  <div class="modal fade show d-block" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="bi me-2" [class.bi-person-plus]="!editing" [class.bi-pencil-square]="editing"></i>
            {{ editing ? 'Editar cliente' : 'Nuevo cliente' }}
          </h5>
          <button type="button" class="btn-close btn-close-white" (click)="showModal=false"></button>
        </div>
        <div class="modal-body">
          <form [formGroup]="form">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold">Nombre / Razón social *</label>
                <input class="form-control" formControlName="name"
                  [class.is-invalid]="isInvalid('name')" placeholder="Juan Pérez">
                <div class="invalid-feedback">El nombre es requerido.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">RUC / Cédula *</label>
                <input class="form-control" formControlName="identification"
                  [class.is-invalid]="isInvalid('identification')" placeholder="0912345678">
                <div class="invalid-feedback">Requerido y único.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Teléfono</label>
                <input class="form-control" formControlName="phone" placeholder="0991234567">
              </div>
              <div class="col-md-8">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" formControlName="email"
                  [class.is-invalid]="isInvalid('email')" placeholder="cliente@email.com">
                <div class="invalid-feedback">Ingresa un email válido.</div>
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch"
                    id="clientActive" formControlName="active">
                  <label class="form-check-label" for="clientActive">Activo</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Dirección</label>
                <textarea class="form-control" formControlName="address" rows="2"
                  placeholder="Guayaquil, Av. Principal 123"></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" (click)="showModal=false">Cancelar</button>
          <button class="btn btn-primary" (click)="submit()" [disabled]="saving">
            <span *ngIf="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ saving ? 'Guardando...' : (editing ? 'Actualizar' : 'Crear cliente') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</ng-container>
  `,
})
export class ClientListComponent implements OnInit {
  clients: Client[] = [];
  loading = false; saving = false; showModal = false; editing = false;
  total = 0; totalPages = 1; currentPage = 1;
  searchName = ''; searchId = '';
  selectedClient: Client | null = null;
  form!: FormGroup;

  constructor(
    private svc:    ClientService,
    private notify: NotificationService,
    private fb:     FormBuilder,
  ) {}

  ngOnInit(): void { this.load(); }

  buildForm(c?: Client): void {
    this.form = this.fb.group({
      name:           [c?.name           ?? '', Validators.required],
      identification: [c?.identification ?? '', Validators.required],
      email:          [c?.email          ?? '', Validators.email],
      phone:          [c?.phone          ?? ''],
      address:        [c?.address        ?? ''],
      active:         [c?.active         ?? true],
    });
  }

  load(): void {
    this.loading = true;
    this.svc.getAll({ name: this.searchName, identification: this.searchId, page: this.currentPage }).subscribe({
      next:  r => { this.clients = r.data; this.total = r.total; this.totalPages = r.last_page; this.loading = false; },
      error: () => { this.notify.error('Error al cargar clientes.'); this.loading = false; },
    });
  }

  clearFilters(): void { this.searchName = ''; this.searchId = ''; this.currentPage = 1; this.load(); }
  goToPage(p: number): void { if (p < 1 || p > this.totalPages) return; this.currentPage = p; this.load(); }
  get pages(): number[] { return Array.from({ length: this.totalPages }, (_, i) => i + 1); }

  openCreate(): void { this.editing = false; this.selectedClient = null; this.buildForm(); this.showModal = true; }
  openEdit(c: Client): void { this.editing = true; this.selectedClient = c; this.buildForm(c); this.showModal = true; }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving = true;
    const req$ = this.editing
      ? this.svc.update(this.selectedClient!.id, this.form.value)
      : this.svc.create(this.form.value);
    req$.subscribe({
      next: () => { this.saving = false; this.showModal = false; this.load(); this.notify.success(this.editing ? 'Cliente actualizado.' : 'Cliente creado.'); },
      error: (err) => { this.saving = false; this.notify.error(err.error?.message ?? 'Error al guardar.'); },
    });
  }

  delete(c: Client): void {
    this.notify.confirmDelete(c.name).then(r => {
      if (!r.isConfirmed) return;
      this.svc.delete(c.id).subscribe({
        next:  () => { this.notify.success('Cliente eliminado.'); this.load(); },
        error: (err) => this.notify.error(err.error?.message ?? 'No se pudo eliminar.'),
      });
    });
  }

  isInvalid(f: string): boolean { const c = this.form.get(f); return !!(c?.invalid && c?.touched); }
}
