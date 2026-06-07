<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stockmaximo = $this->faker->numberBetween(10, 100); 
        $producto = $this->faker->randomElement(config('datos.productos'));

        return [
            'nombre' => $producto['nombre'],
            'preciocompra' => $producto['precio'],
            'descripcion' => $this->faker->sentence(),
            'stockmaximo' => $stockmaximo,
            'stock' => $this->faker->numberBetween(0, $stockmaximo),
            'imagen' =>  $producto['imagen'],
            'estado' => '1',
            'registradopor' => $this->faker->randomElement([
                'Santiago Álvarez',
                'Juan Manuel Garrido',
                'John Stiven Santiago',
                'Javier Mauricio Serna'
            ]),
        ];
    }
}