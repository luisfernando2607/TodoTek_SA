import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { NotificationService } from '../../core/services/notification.service';

interface NavItem {
  label: string;
  icon:  string;
  route: string;
}

@Component({
  selector: 'app-sidebar',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './sidebar.component.html',
})
export class SidebarComponent {
  collapsed = false;

  navItems: NavItem[] = [
    { label: 'Productos',   icon: 'bi-box-seam',       route: '/products'  },
    { label: 'Categorías',  icon: 'bi-tags',            route: '/categories'},
    { label: 'Stock',       icon: 'bi-graph-up-arrow',  route: '/stock'     },
    { label: 'Clientes',    icon: 'bi-people',          route: '/clients'   },
    { label: 'Facturas',    icon: 'bi-receipt',         route: '/invoices'  },
  ];

  constructor(
    public  auth:   AuthService,
    private notify: NotificationService,
  ) {}

  logout(): void {
    this.notify.confirm(
      '¿Cerrar sesión?',
      'Tu sesión será terminada.',
      'Sí, salir',
      'question'
    ).then(res => {
      if (res.isConfirmed) this.auth.logout();
    });
  }
}
