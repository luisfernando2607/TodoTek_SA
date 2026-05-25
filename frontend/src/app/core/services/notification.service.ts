import { Injectable } from '@angular/core';
import Swal, { SweetAlertResult } from 'sweetalert2';

@Injectable({ providedIn: 'root' })
export class NotificationService {

  // ── Toasts rápidos ───────────────────────────────────────────
  success(message: string, title = 'Éxito'): void {
    Swal.fire({
      toast: true, position: 'top-end', icon: 'success',
      title, text: message,
      showConfirmButton: false, timer: 3000, timerProgressBar: true,
    });
  }

  error(message: string, title = 'Error'): void {
    Swal.fire({
      toast: true, position: 'top-end', icon: 'error',
      title, text: message,
      showConfirmButton: false, timer: 4000, timerProgressBar: true,
    });
  }

  warning(message: string, title = 'Advertencia'): void {
    Swal.fire({
      toast: true, position: 'top-end', icon: 'warning',
      title, text: message,
      showConfirmButton: false, timer: 4000, timerProgressBar: true,
    });
  }

  info(message: string): void {
    Swal.fire({
      toast: true, position: 'top-end', icon: 'info',
      text: message, showConfirmButton: false, timer: 3000,
    });
  }

  // ── Confirmaciones modales ───────────────────────────────────
  confirm(
    title: string,
    text: string,
    confirmText = 'Sí, continuar',
    icon: 'warning' | 'error' | 'question' = 'warning'
  ): Promise<SweetAlertResult> {
    return Swal.fire({
      title, text, icon,
      showCancelButton:      true,
      confirmButtonText:     confirmText,
      cancelButtonText:      'Cancelar',
      confirmButtonColor:    '#d33',
      cancelButtonColor:     '#6c757d',
      reverseButtons:        true,
    });
  }

  // ── Confirmación de eliminación (shortcut) ───────────────────
  confirmDelete(itemName: string): Promise<SweetAlertResult> {
    return this.confirm(
      `¿Eliminar "${itemName}"?`,
      'Esta acción no se puede deshacer.',
      'Sí, eliminar',
      'warning'
    );
  }

  // ── Alerta de stock bajo ─────────────────────────────────────
  stockWarning(productName: string, currentStock: number): void {
    Swal.fire({
      icon:  'warning',
      title: '⚠️ Stock bajo',
      html:  `<b>${productName}</b><br>Stock actual: <b>${currentStock}</b> unidades`,
      confirmButtonText: 'Entendido',
      confirmButtonColor: '#f39c12',
    });
  }
}
