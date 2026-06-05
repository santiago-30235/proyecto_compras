<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\OrdenCompra;
use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pago>
 */
class PagoFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ordencompra_id' => $this->faker->randomElement(\App\Models\OrdenCompra::pluck('id')->toArray()),
            'fechapago' => $this->faker->datetime(),
            'monto' => $this->faker->randomFloat(2, 50, 500),
            'metodopago_id' => $this->faker->randomElement(\App\Models\MetodoPago::pluck('id')->toArray()),
            'registradopor' => $this->faker->randomElement(config('datos.registradopor')),
        ];
    }
}
