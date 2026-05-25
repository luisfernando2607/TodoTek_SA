import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { SidebarComponent } from './sidebar/sidebar.component';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterModule, SidebarComponent],
  template: `
<div class="d-flex" style="min-height:100vh">
  <app-sidebar></app-sidebar>
  <main class="flex-grow-1 bg-light overflow-auto">
    <router-outlet></router-outlet>
  </main>
</div>
  `,
})
export class MainLayoutComponent {}
