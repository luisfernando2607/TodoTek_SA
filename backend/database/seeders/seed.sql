-- =============================================================
-- Seed data para TodoTek_SA / Todostock
-- Compatible con PostgreSQL (usa ON CONFLICT para upsert)
-- =============================================================
-- Uso: psql -d Todotek -f database/seeders/seed.sql
-- =============================================================

BEGIN;

-- ─── Usuarios ───────────────────────────────────────────────
INSERT INTO users (name, email, password, created_at, updated_at)
VALUES
  ('Administrador', 'admin@todostock.com', '$2y$12$LJ3m4ys3Lk5s7Qe8Rn9TfO8RgVh0Fg5Bi1Np7X3K9s0D2f6V8bSq', NOW(), NOW()),
  ('Vendedor Demo', 'vendedor@todostock.com', '$2y$12$LJ3m4ys3Lk5s7Qe8Rn9TfO8RgVh0Fg5Bi1Np7X3K9s0D2f6V8bSq', NOW(), NOW())
ON CONFLICT (email) DO NOTHING;

-- ─── Categorías ─────────────────────────────────────────────
INSERT INTO categories (name, slug, description, created_at, updated_at)
VALUES
  ('Electrónica',  'electronica',  'Equipos y dispositivos electrónicos', NOW(), NOW()),
  ('Ropa',         'ropa',         'Prendas de vestir',                  NOW(), NOW()),
  ('Alimentos',    'alimentos',    'Productos alimenticios',             NOW(), NOW()),
  ('Herramientas', 'herramientas', 'Herramientas y ferretería',          NOW(), NOW()),
  ('Oficina',      'oficina',      'Artículos de oficina',               NOW(), NOW())
ON CONFLICT (slug) DO NOTHING;

-- ─── Productos ───────────────────────────────────────────────
INSERT INTO products (category_id, name, sku, description, price, tax_rate, stock, stock_min, active, created_at, updated_at)
VALUES
  ((SELECT id FROM categories WHERE slug = 'electronica'),  'Laptop HP 15"',       'ELEC-001', NULL, 850.00,  15, 20,  3,  true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'electronica'),  'Mouse Inalámbrico',    'ELEC-002', NULL, 25.00,   15, 50,  10, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'electronica'),  'Teclado Mecánico',     'ELEC-003', NULL, 65.00,   15, 30,  5,  true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'electronica'),  'Monitor 24" Full HD',  'ELEC-004', NULL, 320.00,  15, 15,  2,  true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'electronica'),  'Auriculares Bluetooth','ELEC-005', NULL, 45.00,   15, 40,  8,  true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'ropa'),         'Camiseta Polo',        'ROPA-001', NULL, 18.00,   12, 100, 20, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'ropa'),         'Pantalón Jean',        'ROPA-002', NULL, 35.00,   12, 80,  15, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'alimentos'),    'Arroz 25kg',           'ALIM-001', NULL, 22.00,   0,  200, 50, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'alimentos'),    'Aceite 1L',            'ALIM-002', NULL, 3.50,    0,  150, 30, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'oficina'),      'Resma de Papel A4',    'OFIC-001', NULL, 4.50,    12, 300, 50, true, NOW(), NOW()),
  ((SELECT id FROM categories WHERE slug = 'oficina'),      'Bolígrafos x12',       'OFIC-002', NULL, 2.80,    12, 200, 40, true, NOW(), NOW())
ON CONFLICT (sku) DO NOTHING;

-- ─── Clientes ───────────────────────────────────────────────
INSERT INTO clients (name, identification, email, phone, address, active, created_at, updated_at)
VALUES
  ('Juan Pérez',      '0912345678',     'juan@email.com',    '0991234567', 'Guayaquil, Ecuador',     true, NOW(), NOW()),
  ('María García',    '0923456789',     'maria@email.com',   '0992345678', 'Quito, Ecuador',         true, NOW(), NOW()),
  ('Carlos López',    '0934567890',     'carlos@email.com',  '0993456789', 'Cuenca, Ecuador',        true, NOW(), NOW()),
  ('Ana Martínez',    '0945678901',     'ana@email.com',     '0994567890', 'Manta, Ecuador',         true, NOW(), NOW()),
  ('Luis Rodríguez',  '0956789012',     'luis@email.com',    '0995678901', 'Ambato, Ecuador',        true, NOW(), NOW()),
  ('Empresa ABC S.A.','0990012345001',  'info@abc.com',      '042123456',  'Guayaquil, Ecuador',     true, NOW(), NOW())
ON CONFLICT (identification) DO NOTHING;

-- ─── Facturas ───────────────────────────────────────────────
-- Nota: Las facturas descuentan stock de los productos.
-- Ejecutar SOLO si se desea datos de demostración completos.

DO $$
DECLARE
  v_client_id  int;
  v_user_id    int;
  v_inv_num    text;
  v_subtotal   numeric;
  v_tax_total  numeric;
  v_product    record;
BEGIN
  v_user_id := (SELECT id FROM users ORDER BY id LIMIT 1);

  -- Factura 1
  v_client_id := (SELECT id FROM clients ORDER BY id LIMIT 1 OFFSET 0);
  v_subtotal  := (2 * 850.00) + (5 * 25.00);
  v_tax_total := round((2 * 850.00) * 15 / 100, 2) + round((5 * 25.00) * 15 / 100, 2);
  v_inv_num   := 'FAC-' || LPAD(COALESCE((SELECT MAX(CAST(SUBSTRING(invoice_number FROM 5) AS integer)) + 1 FROM invoices WHERE invoice_number LIKE 'FAC-%'), 1)::text, 5, '0');
  INSERT INTO invoices (client_id, user_id, invoice_number, subtotal, tax_amount, total, status, notes, issued_at, created_at, updated_at)
  VALUES (v_client_id, v_user_id, v_inv_num, v_subtotal, v_tax_total, v_subtotal + v_tax_total, 'issued', 'Factura de ejemplo 1', '2026-05-10 09:30:00', NOW(), NOW());
  INSERT INTO invoice_items (invoice_id, product_id, product_name, unit_price, tax_rate, quantity, subtotal, tax_amount, total)
  VALUES
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ELEC-001'), 'Laptop HP 15"', 850.00, 15, 2, 1700.00, 255.00, 1955.00),
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ELEC-002'), 'Mouse Inalámbrico', 25.00, 15, 5, 125.00, 18.75, 143.75);
  UPDATE products SET stock = stock - 2  WHERE sku = 'ELEC-001';
  UPDATE products SET stock = stock - 5  WHERE sku = 'ELEC-002';

  -- Factura 2
  v_client_id := (SELECT id FROM clients ORDER BY id LIMIT 1 OFFSET 1);
  v_subtotal  := (10 * 18.00) + (3 * 35.00);
  v_tax_total := round((10 * 18.00) * 12 / 100, 2) + round((3 * 35.00) * 12 / 100, 2);
  v_inv_num   := 'FAC-' || LPAD(COALESCE((SELECT MAX(CAST(SUBSTRING(invoice_number FROM 5) AS integer)) + 1 FROM invoices WHERE invoice_number LIKE 'FAC-%'), 1)::text, 5, '0');
  INSERT INTO invoices (client_id, user_id, invoice_number, subtotal, tax_amount, total, status, notes, issued_at, created_at, updated_at)
  VALUES (v_client_id, v_user_id, v_inv_num, v_subtotal, v_tax_total, v_subtotal + v_tax_total, 'issued', 'Factura de ejemplo 2', '2026-05-12 14:15:00', NOW(), NOW());
  INSERT INTO invoice_items (invoice_id, product_id, product_name, unit_price, tax_rate, quantity, subtotal, tax_amount, total)
  VALUES
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ROPA-001'), 'Camiseta Polo', 18.00, 12, 10, 180.00, 21.60, 201.60),
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ROPA-002'), 'Pantalón Jean', 35.00, 12, 3, 105.00, 12.60, 117.60);
  UPDATE products SET stock = stock - 10 WHERE sku = 'ROPA-001';
  UPDATE products SET stock = stock - 3  WHERE sku = 'ROPA-002';

  -- Factura 3
  v_client_id := (SELECT id FROM clients ORDER BY id LIMIT 1 OFFSET 2);
  v_subtotal  := (5 * 22.00) + (20 * 3.50) + (10 * 4.50);
  v_tax_total := round((10 * 4.50) * 12 / 100, 2);
  v_inv_num   := 'FAC-' || LPAD(COALESCE((SELECT MAX(CAST(SUBSTRING(invoice_number FROM 5) AS integer)) + 1 FROM invoices WHERE invoice_number LIKE 'FAC-%'), 1)::text, 5, '0');
  INSERT INTO invoices (client_id, user_id, invoice_number, subtotal, tax_amount, total, status, notes, issued_at, created_at, updated_at)
  VALUES (v_client_id, v_user_id, v_inv_num, v_subtotal, v_tax_total, v_subtotal + v_tax_total, 'issued', 'Factura de ejemplo 3 - productos varios', '2026-05-15 10:00:00', NOW(), NOW());
  INSERT INTO invoice_items (invoice_id, product_id, product_name, unit_price, tax_rate, quantity, subtotal, tax_amount, total)
  VALUES
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ALIM-001'), 'Arroz 25kg', 22.00, 0, 5, 110.00, 0, 110.00),
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'ALIM-002'), 'Aceite 1L', 3.50, 0, 20, 70.00, 0, 70.00),
    (currval('invoices_id_seq'), (SELECT id FROM products WHERE sku = 'OFIC-001'), 'Resma de Papel A4', 4.50, 12, 10, 45.00, 5.40, 50.40);
  UPDATE products SET stock = stock - 5  WHERE sku = 'ALIM-001';
  UPDATE products SET stock = stock - 20 WHERE sku = 'ALIM-002';
  UPDATE products SET stock = stock - 10 WHERE sku = 'OFIC-001';
END $$;

COMMIT;
