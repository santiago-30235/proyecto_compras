<?php
$repls = [
    'c:\xampp\htdocs\proyecto\app\Http\Controllers\DetalleCompraController.php' => [
        ['use App\\Models\\Detallecompra;', 'use App\\Models\\DetalleCompra;'],
        ['use App\\Models\\Ordencompra;', 'use App\\Models\\OrdenCompra;'],
        ['Detallecompra::', 'DetalleCompra::'],
        ['Ordencompra::', 'OrdenCompra::'],
    ],
    'c:\xampp\htdocs\proyecto\app\Http\Controllers\HomeController.php' => [
        ['Detallecompra::count();', 'DetalleCompra::count();'],
    ],
    'c:\xampp\htdocs\proyecto\app\Models\Proveedor.php' => [
        ['use Illuminate\\Database\\Eloquent\\Model;\n\nclass Proveedor extends Model', 'use Illuminate\\Database\\Eloquent\\Model;\nuse App\\Models\\OrdenCompra;\n\nclass Proveedor extends Model'],
        ['return $this->hasMany(Ordencompra::class, \'proveedor_id\');', 'return $this->hasMany(OrdenCompra::class, \'proveedor_id\');'],
    ],
    'c:\xampp\htdocs\proyecto\app\Models\Producto.php' => [
        ['use Illuminate\\Database\\Eloquent\\Model;\n\nclass Producto extends Model', 'use Illuminate\\Database\\Eloquent\\Model;\nuse App\\Models\\DetalleCompra;\n\nclass Producto extends Model'],
        ['return $this->hasMany(Detallecompra::class, \'producto_id\');', 'return $this->hasMany(DetalleCompra::class, \'producto_id\');'],
    ],
    'c:\xampp\htdocs\proyecto\database\seeders\DatabaseSeeder.php' => [
        ['use App\\Models\\Ordencompra;', 'use App\\Models\\OrdenCompra;'],
        ['use App\\Models\\Detallecompra;', 'use App\\Models\\DetalleCompra;'],
        ['use App\\Models\\Metodopago;', 'use App\\Models\\MetodoPago;'],
        ['Ordencompra::factory(10)->create();', 'OrdenCompra::factory(10)->create();'],
        ['Detallecompra::factory(10)->create();', 'DetalleCompra::factory(10)->create();'],
        ['Metodopago::factory(3)->create();', 'MetodoPago::factory(3)->create();'],
    ],
    'c:\xampp\htdocs\proyecto\database\factories\PagoFactory.php' => [
        ['use App\\Models\\Ordencompra;', 'use App\\Models\\OrdenCompra;'],
        ['use App\\Models\\Metodopago;', 'use App\\Models\\MetodoPago;'],
        ['\\App\\Models\\Ordencompra::pluck(\'id\')', '\\App\\Models\\OrdenCompra::pluck(\'id\')'],
        ['\\App\\Models\\Metodopago::pluck(\'id\')', '\\App\\Models\\MetodoPago::pluck(\'id\')'],
    ],
    'c:\xampp\htdocs\proyecto\database\factories\OrdencompraFactory.php' => [
        ['use App\\Models\\Ordencompra;', 'use App\\Models\\OrdenCompra;'],
        ['@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\Ordencompra>', '@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\OrdenCompra>'],
        ['class OrdencompraFactory extends Factory', 'class OrdenCompraFactory extends Factory'],
        ['protected $model = Ordencompra::class;', 'protected $model = OrdenCompra::class;'],
    ],
    'c:\xampp\htdocs\proyecto\database\factories\MetodopagoFactory.php' => [
        ['use App\\Models\\Metodopago;', 'use App\\Models\\MetodoPago;'],
        ['@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\Metodopago>', '@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\MetodoPago>'],
        ['class MetodopagoFactory extends Factory', 'class MetodoPagoFactory extends Factory'],
    ],
    'c:\xampp\htdocs\proyecto\database\factories\DetallecompraFactory.php' => [
        ['use App\\Models\\Detallecompra;', 'use App\\Models\\DetalleCompra;'],
        ['use App\\Models\\Ordencompra;', 'use App\\Models\\OrdenCompra;'],
        ['@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\Detallecompra>', '@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<\\App\\Models\\DetalleCompra>'],
        ['class DetallecompraFactory extends Factory', 'class DetalleCompraFactory extends Factory'],
        ['protected $model = Detallecompra::class;', 'protected $model = DetalleCompra::class;'],
        ['\\App\\Models\\Ordencompra::pluck(\'id\')', '\\App\\Models\\OrdenCompra::pluck(\'id\')'],
    ],
];
foreach ($repls as $path => $pairs) {
    if (!file_exists($path)) {
        fwrite(STDERR, "Missing file: $path\n");
        exit(1);
    }
    $text = file_get_contents($path);
    foreach ($pairs as $pair) {
        list($old, $new) = $pair;
        if (strpos($text, $old) === false) {
            fwrite(STDERR, "Missing expected text in $path: $old\n");
            exit(1);
        }
        $text = str_replace($old, $new, $text);
    }
    file_put_contents($path, $text);
}
$renames = [
    'c:\xampp\htdocs\proyecto\database\factories\OrdencompraFactory.php' => 'c:\xampp\htdocs\proyecto\database\factories\OrdenCompraFactory.php',
    'c:\xampp\htdocs\proyecto\database\factories\MetodopagoFactory.php' => 'c:\xampp\htdocs\proyecto\database\factories\MetodoPagoFactory.php',
    'c:\xampp\htdocs\proyecto\database\factories\DetallecompraFactory.php' => 'c:\xampp\htdocs\proyecto\database\factories\DetalleCompraFactory.php',
];
foreach ($renames as $old => $new) {
    if (!file_exists($old)) {
        fwrite(STDERR, "File not found for rename: $old\n");
        exit(1);
    }
    if (!rename($old, $new)) {
        fwrite(STDERR, "Failed to rename $old to $new\n");
        exit(1);
    }
}
echo "patched and renamed files successfully\n";
?>
