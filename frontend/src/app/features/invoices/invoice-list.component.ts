import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { InvoiceService } from '../../core/services/api.services';
import { NotificationService } from '../../core/services/notification.service';
import { Invoice } from '../../core/models/models';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-invoice-list',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './invoice-list.component.html',
})
export class InvoiceListComponent implements OnInit {
  invoices:    Invoice[] = [];
  loading      = false;
  total        = 0;
  totalPages   = 1;
  currentPage  = 1;
  filterStatus = '';
  filterNumber = '';

  // Detalle modal
  showDetail  = false;
  detailInvoice: Invoice | null = null;
  loadingDetail = false;

  constructor(
    private svc:    InvoiceService,
    private notify: NotificationService,
    private http:   HttpClient,
  ) {}

  ngOnInit(): void { this.load(); }

  load(): void {
    this.loading = true;
    this.svc.getAll({ status: this.filterStatus, number: this.filterNumber, page: this.currentPage }).subscribe({
      next:  r => { this.invoices = r.data; this.total = r.total; this.totalPages = r.last_page; this.loading = false; },
      error: () => { this.notify.error('Error al cargar facturas.'); this.loading = false; },
    });
  }

  clearFilters(): void { this.filterStatus = ''; this.filterNumber = ''; this.currentPage = 1; this.load(); }
  goToPage(p: number): void { if (p < 1 || p > this.totalPages) return; this.currentPage = p; this.load(); }
  get pages(): number[] { return Array.from({ length: this.totalPages }, (_, i) => i + 1); }

  openDetail(inv: Invoice): void {
    this.showDetail    = true;
    this.loadingDetail = true;
    this.svc.getOne(inv.id).subscribe(full => {
      this.detailInvoice = full;
      this.loadingDetail  = false;
    });
  }

  downloadPdf(inv: Invoice, download = false): void {
    this.http.get(`${environment.apiUrl}/invoices/${inv.id}/pdf`, {
      responseType: 'blob',
    }).subscribe({
      next: blob => {
        const blobUrl = URL.createObjectURL(blob);
        if (download) {
          const a = document.createElement('a');
          a.href = blobUrl;
          a.download = `${inv.invoice_number}.pdf`;
          a.click();
        } else {
          window.open(blobUrl, '_blank');
        }
        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);
      },
      error: () => this.notify.error('Error al generar el PDF.'),
    });
  }

  cancelInvoice(inv: Invoice): void {
    this.notify.confirm(
      `¿Cancelar factura ${inv.invoice_number}?`,
      'El stock de los productos será revertido.',
      'Sí, cancelar',
      'warning'
    ).then(r => {
      if (!r.isConfirmed) return;
      this.svc.cancel(inv.id).subscribe({
        next:  () => { this.notify.success('Factura cancelada y stock revertido.'); this.load(); this.showDetail = false; },
        error: (err) => this.notify.error(err.error?.message ?? 'Error al cancelar.'),
      });
    });
  }

  statusBadge(status: string): string {
    const map: Record<string, string> = { issued: 'bg-success', draft: 'bg-warning', cancelled: 'bg-danger' };
    return map[status] ?? 'bg-secondary';
  }

  statusLabel(status: string): string {
    const map: Record<string, string> = { issued: 'Emitida', draft: 'Borrador', cancelled: 'Cancelada' };
    return map[status] ?? status;
  }
}
