<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);           // Precio base sin IVA
            $table->decimal('tax_rate', 5, 2)->default(15.00); // IVA configurable %
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(5);  // Alerta de stock mínimo
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
