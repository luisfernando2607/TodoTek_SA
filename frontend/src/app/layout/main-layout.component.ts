import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { SidebarComponent } from './sidebar/sidebar.component';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterModule, SidebarComponent],
  template: `
<div class="layout-wrapper" style="min-height:100vh">
  <div
    class="sidebar-backdrop d-md-none"
    [class.show]="mobileOpen"
    (click)="mobileOpen = false"
  ></div>

  <app-sidebar
    [class.mobile-open]="mobileOpen"
    (closeMobile)="mobileOpen = false"
    (collapsedChange)="onCollapsedChange($event)"
  ></app-sidebar>

  <main class="main-content bg-light overflow-auto" [class.main-content-expanded]="sidebarCollapsed">
    <div class="mobile-topbar d-md-none px-3 py-2 bg-white border-bottom sticky-top">
      <button class="btn btn-sm btn-outline-primary" (click)="mobileOpen = !mobileOpen">
        <i class="bi bi-list fs-5"></i>
      </button>
      <span class="ms-2 fw-bold"><i class="bi bi-boxes text-primary me-1"></i>Todostock</span>
    </div>
    <router-outlet></router-outlet>
  </main>
</div>
  `,
})
export class MainLayoutComponent {
  mobileOpen = false;
  sidebarCollapsed = false;

  onCollapsedChange(collapsed: boolean): void {
    this.sidebarCollapsed = collapsed;
  }
}
