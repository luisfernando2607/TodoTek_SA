import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { InvoiceService, ClientService } from '../../core/services/api.services';
import { ProductService } from '../../core/services/product.service';
import { NotificationService } from '../../core/services/notification.service';
import { Client, Product, InvoiceItemPayload } from '../../core/models/models';

interface CartItem {
  product:    Product;
  quantity:   number;
  subtotal:   number;
  tax_amount: number;
  total:      number;
}

@Component({
  selector: 'app-invoice-create',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './invoice-create.component.html',
})
export class InvoiceCreateComponent implements OnInit {
  clients:  Client[]  = [];
  products: Product[] = [];
  cart:     CartItem[] = [];

  selectedClientId: number | null = null;
  selectedProductId: number | null = null;
  quantity = 1;
  notes = '';
  saving = false;
  productSearch = '';

  constructor(
    private invoiceSvc: InvoiceService,
    private clientSvc:  ClientService,
    private productSvc: ProductService,
    private notify:     NotificationService,
    private router:     Router,
  ) {}

  ngOnInit(): void {
    this.clientSvc.getAll({ per_page: 100 }).subscribe(r => this.clients = r.data);
    this.productSvc.getAll({ per_page: 100, active: 'true' }).subscribe(r => this.products = r.data);
  }

  get filteredProducts(): Product[] {
    if (!this.productSearch) return this.products;
    const q = this.productSearch.toLowerCase();
    return this.products.filter(p =>
      p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
    );
  }

  addToCart(): void {
    if (!this.selectedProductId || this.quantity < 1) return;
    const product = this.products.find(p => p.id === Number(this.selectedProductId));
    if (!product) return;

    if (this.quantity > product.stock) {
      this.notify.warning(`Stock insuficiente. Disponible: ${product.stock}`);
      return;
    }

    const existing = this.cart.find(i => i.product.id === product.id);
    if (existing) {
      existing.quantity += this.quantity;
      this.recalcItem(existing);
    } else {
      const item: CartItem = { product, quantity: this.quantity, subtotal: 0, tax_amount: 0, total: 0 };
      this.recalcItem(item);
      this.cart.push(item);
    }

    this.selectedProductId = null;
    this.quantity = 1;
    this.productSearch = '';
  }

  recalcItem(item: CartItem): void {
    item.subtotal   = round2(item.product.price * item.quantity);
    item.tax_amount = round2(item.subtotal * item.product.tax_rate / 100);
    item.total      = round2(item.subtotal + item.tax_amount);
  }

  updateQty(item: CartItem, qty: number): void {
    if (qty < 1) return;
    item.quantity = qty;
    this.recalcItem(item);
  }

  removeFromCart(index: number): void { this.cart.splice(index, 1); }

  get subtotal():   number { return round2(this.cart.reduce((s, i) => s + i.subtotal,   0)); }
  get taxAmount():  number { return round2(this.cart.reduce((s, i) => s + i.tax_amount, 0)); }
  get totalAmount():number { return round2(this.cart.reduce((s, i) => s + i.total,      0)); }

  submit(): void {
    if (!this.selectedClientId) { this.notify.warning('Selecciona un cliente.'); return; }
    if (this.cart.length === 0)  { this.notify.warning('Agrega al menos un producto.'); return; }

    this.saving = true;
    const items: InvoiceItemPayload[] = this.cart.map(i => ({
      product_id: i.product.id,
      quantity:   i.quantity,
    }));

    this.invoiceSvc.create({ client_id: this.selectedClientId, items, notes: this.notes }).subscribe({
      next: (inv) => {
        this.saving = false;
        this.notify.success(`Factura ${inv.invoice_number} generada correctamente.`);
        this.router.navigate(['/invoices']);
      },
      error: (err) => {
        this.saving = false;
        this.notify.error(err.error?.message ?? 'Error al generar la factura.');
      },
    });
  }
}

function round2(n: number): number { return Math.round(n * 100) / 100; }
