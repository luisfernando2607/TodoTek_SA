<?php
namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    private static int $counter = 1;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 5000);
        $tax      = round($subtotal * 0.15, 2);

        return [
            'client_id'      => Client::inRandomOrder()->first()?->id ?? 1,
            'user_id'        => User::inRandomOrder()->first()?->id ?? 1,
            'invoice_number' => 'FAC-' . str_pad(self::$counter++, 5, '0', STR_PAD_LEFT),
            'subtotal'       => $subtotal,
            'tax_amount'     => $tax,
            'total'          => round($subtotal + $tax, 2),
            'status'         => fake()->randomElement(['issued', 'issued', 'issued', 'cancelled']),
            'notes'          => fake()->optional()->sentence(),
            'issued_at'      => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
