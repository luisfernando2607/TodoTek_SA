// ─── Auth ────────────────────────────────────────────────────────────────────
export interface LoginPayload  { email: string; password: string; }
export interface AuthResponse  { token: string; user: User; }
export interface User          { id: number; name: string; email: string; }

// ─── Shared ──────────────────────────────────────────────────────────────────
export interface PaginatedResponse<T> {
  data:          T[];
  current_page:  number;
  last_page:     number;
  per_page:      number;
  total:         number;
}

// ─── Category ────────────────────────────────────────────────────────────────
export interface Category {
  id:             number;
  name:           string;
  slug:           string;
  description?:   string;
  products_count?: number;
}

// ─── Product ─────────────────────────────────────────────────────────────────
export interface ProductImage {
  id:         number;
  path:       string;
  url:        string;
  is_main:    boolean;
  sort_order: number;
}

export interface Product {
  id:              number;
  category_id:     number;
  category?:       Category;
  name:            string;
  sku:             string;
  description?:    string;
  price:           number;
  tax_rate:        number;
  price_with_tax:  number;
  tax_amount:      number;
  stock:           number;
  stock_min:       number;
  is_low_stock:    boolean;
  active:          boolean;
  images?:         ProductImage[];
  main_image?:     ProductImage;
  created_at?:     string;
}

export interface ProductFilters {
  name?:        string;
  category_id?: number | string;
  sku?:         string;
  active?:      boolean | string;
  low_stock?:   boolean;
  page?:        number;
  per_page?:    number;
  sort_by?:     string;
  sort_dir?:    'asc' | 'desc';
}

// ─── Stock ───────────────────────────────────────────────────────────────────
export interface StockMovement {
  id:           number;
  product_id:   number;
  user?:        User;
  type:         'entry' | 'exit' | 'adjustment';
  quantity:     number;
  stock_before: number;
  stock_after:  number;
  reason:       string;
  created_at:   string;
}

export interface StockMovePayload {
  type:     'entry' | 'exit' | 'adjustment';
  quantity: number;
  reason?:  string;
}

// ─── Client ──────────────────────────────────────────────────────────────────
export interface Client {
  id:             number;
  name:           string;
  identification: string;
  email?:         string;
  phone?:         string;
  address?:       string;
  active:         boolean;
}

// ─── Invoice ─────────────────────────────────────────────────────────────────
export interface InvoiceItem {
  id:           number;
  product_id:   number;
  product?:     Product;
  product_name: string;
  unit_price:   number;
  tax_rate:     number;
  quantity:     number;
  subtotal:     number;
  tax_amount:   number;
  total:        number;
}

export interface Invoice {
  id:             number;
  client_id:      number;
  client?:        Client;
  user?:          User;
  invoice_number: string;
  subtotal:       number;
  tax_amount:     number;
  total:          number;
  status:         'draft' | 'issued' | 'cancelled';
  notes?:         string;
  issued_at:      string;
  items?:         InvoiceItem[];
}

export interface InvoiceItemPayload { product_id: number; quantity: number; }
export interface CreateInvoicePayload {
  client_id: number;
  items:     InvoiceItemPayload[];
  notes?:    string;
}
