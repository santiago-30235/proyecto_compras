<?php

namespace Database\Factories;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrdenCompra>
 */
class OrdenCompraFactory extends Factory
{
    protected $model = OrdenCompra::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fecha' => $this->faker->datetime(),
            'proveedor_id' => $this->faker->randomElement(\App\Models\Proveedor::pluck('id')->toArray()),
            'total' => $this->faker->randomFloat(2, 100, 1000),
            'tipopago' => $this->faker->randomElement(['contado', 'crédito']),
            'saldopendiente' => $this->faker->randomFloat(2, 0, 500),
            'estado' => $this->faker->randomElement(['pendiente', 'pagado', 'cancelado']),
            'registradopor' => $this->faker->randomElement(config('datos.registradopor')),
        ];
    }
}
