import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Category, Client, CreateInvoicePayload, Invoice, PaginatedResponse } from '../models/models';

// ─── CategoryService ─────────────────────────────────────────────────────────
@Injectable({ providedIn: 'root' })
export class CategoryService {
  private base = `${environment.apiUrl}/categories`;
  constructor(private http: HttpClient) {}

  getAll(): Observable<Category[]> {
    return this.http.get<Category[]>(this.base);
  }
}

// ─── ClientService ───────────────────────────────────────────────────────────
@Injectable({ providedIn: 'root' })
export class ClientService {
  private base = `${environment.apiUrl}/clients`;
  constructor(private http: HttpClient) {}

  getAll(filters: any = {}): Observable<PaginatedResponse<Client>> {
    let params = new HttpParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v !== null && v !== undefined && v !== '') params = params.set(k, String(v));
    });
    return this.http.get<PaginatedResponse<Client>>(this.base, { params });
  }

  getOne(id: number): Observable<Client> {
    return this.http.get<Client>(`${this.base}/${id}`);
  }

  create(data: Partial<Client>): Observable<Client> {
    return this.http.post<Client>(this.base, data);
  }

  update(id: number, data: Partial<Client>): Observable<Client> {
    return this.http.put<Client>(`${this.base}/${id}`, data);
  }

  delete(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.base}/${id}`);
  }
}

// ─── InvoiceService ──────────────────────────────────────────────────────────
@Injectable({ providedIn: 'root' })
export class InvoiceService {
  private base = `${environment.apiUrl}/invoices`;
  constructor(private http: HttpClient) {}

  getAll(filters: any = {}): Observable<PaginatedResponse<Invoice>> {
    let params = new HttpParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v !== null && v !== undefined && v !== '') params = params.set(k, String(v));
    });
    return this.http.get<PaginatedResponse<Invoice>>(this.base, { params });
  }

  getOne(id: number): Observable<Invoice> {
    return this.http.get<Invoice>(`${this.base}/${id}`);
  }

  create(payload: CreateInvoicePayload): Observable<Invoice> {
    return this.http.post<Invoice>(this.base, payload);
  }

  cancel(id: number): Observable<{ message: string; invoice: Invoice }> {
    return this.http.patch<any>(`${this.base}/${id}/cancel`, {});
  }
}
