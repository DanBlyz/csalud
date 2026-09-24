<?php

namespace Database\Factories;

use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'marca_id' => Marca::factory(),
            'nombre' => fake()->unique()->words(2, true).' '.fake()->randomElement(['500mg', '250mg', '100ml', 'Jarabe', 'Comprimidos']),
            'descripcion' => fake()->sentence(),
            'unidad_medida' => fake()->randomElement(['Caja', 'Blister', 'Frasco', 'Ampolla', 'Unidad']),
            'ultimo_precio_venta' => fake()->randomFloat(2, 5, 150),
            'stock_minimo' => fake()->numberBetween(5, 20),
        ];
    }
}
