<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\OrdenCompra;
use App\Models\Pago;
use App\Models\MetodoPago;
use App\Models\DetalleCompra;

class HomeController extends Controller
{
    public function index()
    {
        // =========================
        // Contar registros
        // =========================
        $totalProveedores = Proveedor::count();

        $totalProductos = Producto::count();

        $totalOrdenes = OrdenCompra::count();

        $totalPagos = Pago::count();

        $totalMetodos = MetodoPago::count();

        $totalDetalles = DetalleCompra::count();

        // =========================
        // Retornar vista
        // =========================
        return view('home', compact(
            'totalProveedores',
            'totalProductos',
            'totalOrdenes',
            'totalPagos',
            'totalMetodos',
            'totalDetalles'
        ));
    }
}
