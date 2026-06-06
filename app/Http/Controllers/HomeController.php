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

    // ===================================
    // METODO PARA SUBIR LA FOTO DE PERFIL
    // ===================================
    public function uploadPhoto(\Illuminate\Http\Request $request)
    {
        // 1. Validar que el archivo cargado cumpla las condiciones de imagen
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $user = \Illuminate\Support\Facades\Auth::user();

            // 2. Limpieza del servidor: Borrar la foto anterior si ya existía una
            if ($user->photo && \Illuminate\Support\Facades\File::exists(public_path($user->photo))) {
                \Illuminate\Support\Facades\File::delete(public_path($user->photo));
            }

            // 3. Obtener el archivo y generar un nombre único e irrepetible
            $file = $request->file('photo');
            $filename = time() . '_user_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // 4. Guardar físicamente el archivo dentro del servidor (public/uploads/users/)
            $file->move(public_path('uploads/users'), $filename);

            // 5. Modificar y guardar el campo 'photo' en el registro correspondiente en la BD
            $user->photo = 'uploads/users/' . $filename;
            $user->save();
        }

        // Redireccionar al usuario de vuelta a donde estaba
        return back()->with('success', '¡Foto de perfil actualizada con éxito!');
    }
}