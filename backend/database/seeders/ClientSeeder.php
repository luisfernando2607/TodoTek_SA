<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Juan Pérez',      'identification' => '0912345678', 'email' => 'juan@email.com',    'phone' => '0991234567', 'address' => 'Guayaquil, Ecuador'],
            ['name' => 'María García',    'identification' => '0923456789', 'email' => 'maria@email.com',   'phone' => '0992345678', 'address' => 'Quito, Ecuador'],
            ['name' => 'Carlos López',    'identification' => '0934567890', 'email' => 'carlos@email.com',  'phone' => '0993456789', 'address' => 'Cuenca, Ecuador'],
            ['name' => 'Ana Martínez',    'identification' => '0945678901', 'email' => 'ana@email.com',     'phone' => '0994567890', 'address' => 'Manta, Ecuador'],
            ['name' => 'Luis Rodríguez',  'identification' => '0956789012', 'email' => 'luis@email.com',    'phone' => '0995678901', 'address' => 'Ambato, Ecuador'],
            ['name' => 'Empresa ABC S.A.','identification' => '0990012345001', 'email' => 'info@abc.com',   'phone' => '042123456',  'address' => 'Guayaquil, Ecuador'],
        ];

        foreach ($clients as $client) {
            DB::table('clients')->upsert(
                array_merge($client, ['active' => true, 'created_at' => now(), 'updated_at' => now()]),
                ['identification'],
                ['name', 'email', 'phone', 'address', 'updated_at']
            );
        }
    }
}
