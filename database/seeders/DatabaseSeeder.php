<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Proveedor;
use App\Models\OrdenCompra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\MetodoPago;
use App\Models\Pago;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        User::factory(10)->create();
        Proveedor::factory(10)->create();
        OrdenCompra::factory(10)->create();
        Producto::factory(10)->create();
        DetalleCompra::factory(10)->create();
        MetodoPago::factory(3)->create();
        Pago::factory(10)->create();
        /*

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
    }
}
