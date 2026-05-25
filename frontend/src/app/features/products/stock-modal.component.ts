import { Component, EventEmitter, Input, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ProductService } from '../../core/services/product.service';
import { NotificationService } from '../../core/services/notification.service';
import { Product } from '../../core/models/models';

@Component({
  selector: 'app-stock-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './stock-modal.component.html',
})
export class StockModalComponent {
  @Input()  product!: Product;
  @Output() updated = new EventEmitter<{ stock: number; low_stock: boolean; warning?: string }>();
  @Output() closed  = new EventEmitter<void>();

  form: FormGroup;
  loading = false;

  constructor(
    private fb:         FormBuilder,
    private productSvc: ProductService,
    private notify:     NotificationService,
  ) {
    this.form = this.fb.group({
      type:     ['entry',   Validators.required],
      quantity: [1,         [Validators.required, Validators.min(1)]],
      reason:   ['',        Validators.maxLength(500)],
    });
  }

  get typeLabel(): string {
    const map: Record<string, string> = {
      entry: 'Entrada', exit: 'Salida', adjustment: 'Ajuste',
    };
    return map[this.form.value.type] ?? '';
  }

  get resultingStock(): number {
    const { type, quantity } = this.form.value;
    const qty = Number(quantity) || 0;
    if (type === 'entry')      return this.product.stock + qty;
    if (type === 'exit')       return this.product.stock - qty;
    if (type === 'adjustment') return qty;
    return this.product.stock;
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.loading = true;
    this.productSvc.moveStock(this.product.id, this.form.value).subscribe({
      next: (res) => {
        this.loading = false;
        this.updated.emit({ stock: res.stock, low_stock: res.low_stock, warning: res.warning });
      },
      error: (err) => {
        this.loading = false;
        this.notify.error(err.error?.message ?? 'Error al registrar el movimiento.');
      },
    });
  }
}
