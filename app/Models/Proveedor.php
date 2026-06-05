<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrdenCompra;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'documento',
        'direccion',
        'telefono',
        'email',
        'estado',
        'registradopor',
    ];

    public function ordenCompras()
    {
        return $this->hasMany(OrdenCompra::class, 'proveedor_id');
    }
}