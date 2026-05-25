import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { NotificationService } from '../../core/services/notification.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  form: FormGroup;
  loading = false;
  showPassword = false;

  constructor(
    private fb:      FormBuilder,
    private auth:    AuthService,
    private notify:  NotificationService,
    private router:  Router,
  ) {
    this.form = this.fb.group({
      email:    ['admin@todostock.com', [Validators.required, Validators.email]],
      password: ['password',            [Validators.required, Validators.minLength(6)]],
    });
  }

  submit(): void {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    this.loading = true;
    this.auth.login(this.form.value).subscribe({
      next:  () => this.router.navigate(['/products']),
      error: (err) => {
        this.loading = false;
        const msg = err.error?.message ?? 'Credenciales incorrectas.';
        this.notify.error(msg, 'Acceso denegado');
      },
    });
  }

  isInvalid(field: string): boolean {
    const c = this.form.get(field);
    return !!(c?.invalid && c?.touched);
  }
}
