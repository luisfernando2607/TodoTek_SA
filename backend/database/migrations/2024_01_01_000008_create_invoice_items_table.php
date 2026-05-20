<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');           // Snapshot del nombre al momento de venta
            $table->decimal('unit_price', 10, 2);     // Snapshot del precio
            $table->decimal('tax_rate', 5, 2);        // Snapshot del IVA
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);       // unit_price * quantity
            $table->decimal('tax_amount', 10, 2);     // subtotal * tax_rate / 100
            $table->decimal('total', 10, 2);          // subtotal + tax_amount
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('invoice_items'); }
};
