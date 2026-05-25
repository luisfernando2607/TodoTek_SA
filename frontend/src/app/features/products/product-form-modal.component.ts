import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ProductService } from '../../core/services/product.service';
import { NotificationService } from '../../core/services/notification.service';
import { Category, Product } from '../../core/models/models';

@Component({
  selector: 'app-product-form-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './product-form-modal.component.html',
})
export class ProductFormModalComponent implements OnInit {
  @Input()  product:    Product | null = null;
  @Input()  categories: Category[]    = [];
  @Output() saved  = new EventEmitter<void>();
  @Output() closed = new EventEmitter<void>();

  form!: FormGroup;
  loading     = false;
  isEdit      = false;

  // Imágenes
  selectedFiles: File[]    = [];
  previews:      string[]  = [];
  existingImages: any[]    = [];

  constructor(
    private fb:         FormBuilder,
    private productSvc: ProductService,
    private notify:     NotificationService,
  ) {}

  ngOnInit(): void {
    this.isEdit         = !!this.product;
    this.existingImages = this.product?.images ?? [];

    this.form = this.fb.group({
      category_id:  [this.product?.category_id ?? '', Validators.required],
      name:         [this.product?.name         ?? '', Validators.required],
      sku:          [this.product?.sku          ?? '', Validators.required],
      description:  [this.product?.description  ?? ''],
      price:        [this.product?.price        ?? 0,  [Validators.required, Validators.min(0)]],
      tax_rate:     [this.product?.tax_rate     ?? 15, [Validators.required, Validators.min(0), Validators.max(100)]],
      stock:        [this.product?.stock        ?? 0,  [Validators.required, Validators.min(0)]],
      stock_min:    [this.product?.stock_min    ?? 5,  [Validators.required, Validators.min(0)]],
      active:       [this.product?.active       ?? true],
    });
  }

  onFilesSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;

    this.selectedFiles = Array.from(input.files);
    this.previews = [];

    this.selectedFiles.forEach(file => {
      const reader = new FileReader();
      reader.onload = e => this.previews.push(e.target?.result as string);
      reader.readAsDataURL(file);
    });
  }

  removeExistingImage(imageId: number): void {
    if (!this.product) return;
    this.notify.confirm('¿Eliminar imagen?', 'Se borrará permanentemente.', 'Sí, eliminar').then(res => {
      if (!res.isConfirmed) return;
      this.productSvc.deleteImage(this.product!.id, imageId).subscribe({
        next: () => {
          this.existingImages = this.existingImages.filter(i => i.id !== imageId);
          this.notify.success('Imagen eliminada.');
        },
        error: () => this.notify.error('No se pudo eliminar la imagen.'),
      });
    });
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.loading = true;

    const request$ = this.isEdit
      ? this.productSvc.update(this.product!.id, this.form.value)
      : this.productSvc.create(this.form.value);

    request$.subscribe({
      next: (product) => {
        // Subir imágenes si hay seleccionadas
        if (this.selectedFiles.length > 0) {
          this.productSvc.uploadImages(product.id, this.selectedFiles).subscribe({
            next:     () => { this.loading = false; this.saved.emit(); },
            error:    () => { this.loading = false; this.saved.emit(); }, // guardado igual aunque fallen imágenes
          });
        } else {
          this.loading = false;
          this.saved.emit();
        }
      },
      error: (err) => {
        this.loading = false;
        const errors = err.error?.errors;
        if (errors) {
          const first = Object.values(errors)[0] as string[];
          this.notify.error(first[0]);
        } else {
          this.notify.error(err.error?.message ?? 'Error al guardar el producto.');
        }
      },
    });
  }

  isInvalid(field: string): boolean {
    const c = this.form.get(field);
    return !!(c?.invalid && c?.touched);
  }
}
