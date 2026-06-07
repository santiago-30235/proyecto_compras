<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetodoPago>
 */
class MetodoPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metodos = [
            'Transferencia',
            'Efectivo',
            'Tarjeta'
        ];

        $descripciones = [
            'Pago rápido y seguro',
            'Transferencia bancaria autorizada',
            'Transacción con tarjeta de crédito o débito',
            'Pago en efectivo validado en el punto de venta'
        ];

        return [
            'nombre' => $this->faker->randomElement($metodos),
            'descripcion' => $this->faker->randomElement($descripciones),
            'estado' => '1',
            'registradopor' => $this->faker->randomElement(config('datos.registradopor')),
        ];
    }
}
