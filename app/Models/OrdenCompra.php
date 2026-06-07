<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'ordencompras';

    protected $fillable = [
        'fecha',
        'proveedor_id',
        'total',
        'tipopago',
        'saldopendiente',
        'estado',
        'registradopor',
        'numero_comprobante',
        'observaciones',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'ordencompra_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'ordencompra_id');
    }
}
