<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 15mm; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #222; }
  .header { text-align: center; margin-bottom: 20px; }
  .header h1 { font-size: 18pt; margin: 0; color: #2563eb; }
  .header p { margin: 2px 0; color: #666; font-size: 8pt; }
  .info { width: 100%; margin-bottom: 20px; }
  .info td { vertical-align: top; padding: 2px 10px; }
  .info td:last-child { text-align: right; }
  table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  table.items th { background: #2563eb; color: #fff; padding: 6px 8px; font-size: 9pt; text-align: left; }
  table.items th.right { text-align: right; }
  table.items th.center { text-align: center; }
  table.items td { padding: 5px 8px; border-bottom: 1px solid #ddd; }
  table.items td.right { text-align: right; }
  table.items td.center { text-align: center; }
  .totals { width: 300px; margin-left: auto; }
  .totals td { padding: 3px 10px; }
  .totals td:last-child { text-align: right; }
  .totals .grand td { font-weight: bold; font-size: 12pt; border-top: 2px solid #2563eb; padding-top: 6px; }
  .footer { text-align: center; margin-top: 30px; color: #999; font-size: 7pt; border-top: 1px solid #ddd; padding-top: 10px; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8pt; font-weight: bold; }
  .badge-issued { background: #22c55e; color: #fff; }
  .badge-cancelled { background: #ef4444; color: #fff; }
</style>
</head>
<body>

<div class="header">
  <h1>{{ config('app.name') }}</h1>
  <p>RUC: 1234567890001 · Dirección principal</p>
  <p>Tel: (04) 234-5678 · Email: info@todostock.com</p>
</div>

<table class="info">
  <tr>
    <td>
      <strong>Cliente:</strong> {{ $invoice->client->name }}<br>
      <strong>RUC/Cédula:</strong> {{ $invoice->client->identification }}<br>
      <strong>Dirección:</strong> {{ $invoice->client->address ?? '—' }}
    </td>
    <td>
      <strong>Factura:</strong> {{ $invoice->invoice_number }}<br>
      <strong>Fecha:</strong> {{ $invoice->issued_at->format('d/m/Y H:i') }}<br>
      <strong>Estado:</strong>
      <span>
        {{ $invoice->status === 'issued' ? 'EMITIDA' : ($invoice->status === 'cancelled' ? 'CANCELADA' : 'BORRADOR') }}
      </span>
    </td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th style="width:50%">Producto</th>
      <th class="right">P. Unit.</th>
      <th class="center">Cant.</th>
      <th class="right">Subtotal</th>
      <th class="right">IVA</th>
      <th class="right">Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($invoice->items as $item)
    <tr>
      <td>{{ $item->product_name }}</td>
      <td class="right">${{ number_format($item->unit_price, 2) }}</td>
      <td class="center">{{ $item->quantity }}</td>
      <td class="right">${{ number_format($item->subtotal, 2) }}</td>
      <td class="right">${{ number_format($item->tax_amount, 2) }}</td>
      <td class="right">${{ number_format($item->total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<table class="totals">
  <tr><td>Subtotal:</td><td>${{ number_format($invoice->subtotal, 2) }}</td></tr>
  <tr><td>Impuestos (IVA):</td><td>${{ number_format($invoice->tax_amount, 2) }}</td></tr>
  <tr class="grand"><td>TOTAL:</td><td>${{ number_format($invoice->total, 2) }}</td></tr>
</table>

@if($invoice->notes)
<p style="margin-top:15px;"><strong>Notas:</strong> {{ $invoice->notes }}</p>
@endif

<div class="footer">
  {{ config('app.name') }} · Generado el {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
