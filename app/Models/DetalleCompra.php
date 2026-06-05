<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detallescompras';

    protected $fillable = [
        'ordencompra_id',
        'producto_id',
        'cantidad',
        'subtotal',
        'registradopor',
    ];

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'ordencompra_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
