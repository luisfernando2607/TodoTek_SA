import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import {
  PaginatedResponse, Product, ProductFilters,
  StockMovePayload, StockMovement
} from '../models/models';

@Injectable({ providedIn: 'root' })
export class ProductService {

  private base = `${environment.apiUrl}/products`;

  constructor(private http: HttpClient) {}

  getAll(filters: ProductFilters = {}): Observable<PaginatedResponse<Product>> {
    let params = new HttpParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v !== null && v !== undefined && v !== '') {
        params = params.set(k, String(v));
      }
    });
    return this.http.get<PaginatedResponse<Product>>(this.base, { params });
  }

  getOne(id: number): Observable<Product> {
    return this.http.get<Product>(`${this.base}/${id}`);
  }

  create(data: Partial<Product>): Observable<Product> {
    return this.http.post<Product>(this.base, data);
  }

  update(id: number, data: Partial<Product>): Observable<Product> {
    return this.http.put<Product>(`${this.base}/${id}`, data);
  }

  delete(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.base}/${id}`);
  }

  // ── Imágenes ─────────────────────────────────────────────────
  uploadImages(id: number, files: File[]): Observable<any> {
    const form = new FormData();
    files.forEach(f => form.append('images[]', f));
    return this.http.post(`${this.base}/${id}/images`, form);
  }

  deleteImage(productId: number, imageId: number): Observable<any> {
    return this.http.delete(`${this.base}/${productId}/images/${imageId}`);
  }

  // ── Stock ─────────────────────────────────────────────────────
  moveStock(id: number, payload: StockMovePayload): Observable<{
    message: string; movement: StockMovement; stock: number;
    low_stock: boolean; warning?: string;
  }> {
    return this.http.post<any>(`${this.base}/${id}/stock`, payload);
  }
}
