<?php

namespace Database\Factories;

use App\Models\DetalleCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;    
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetalleCompra>
 */
class DetalleCompraFactory extends Factory
{
    protected $model = DetalleCompra::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = $this->faker->numberBetween(1, 5);
        $precio = $this->faker->randomFloat(2, 100, 500);
        return [
            'ordencompra_id' => $this->faker->randomElement(\App\Models\OrdenCompra::pluck('id')->toArray()),
            'producto_id' => $this->faker->randomElement(\App\Models\Producto::pluck('id')->toArray()),
            'cantidad' => $cantidad,
            'subtotal' => $cantidad * $precio,
            'registradopor' => $this->faker->randomElement(config('datos.registradopor')),
        ];
    }
}
