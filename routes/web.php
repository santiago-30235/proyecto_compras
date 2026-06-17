<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\DetalleCompraController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;

Route::pattern('proveedor', '[0-9]+');

Route::get('/favicon.ico', function () {
    return response()->file(public_path('favicon.ico'));
});

// =========================
// RUTA PRINCIPAL
// =========================
Route::get('/', function () {
    return view('welcome');
});

// =========================
// RUTAS DE PRUEBA (ERRORES)
// =========================
Route::get('/test-404', function () {
    throw new App\Exceptions\NotFoundHttpException('Recurso no encontrado');
});

Route::get('/test-403', function () {
    throw new App\Exceptions\ForbiddenException('Acceso denegado');
});

Route::get('/test-419', function () {
    throw new App\Exceptions\TokenMismatchException('Token expirado');
});

Route::get('/test-500', function () {
    throw new App\Exceptions\InternalServerErrorException('Error interno');
});

// =========================
// LOGIN
// =========================
Auth::routes();

// =========================
// RUTAS PROTEGIDAS
// =========================
Route::middleware(['auth'])->group(function () {

    // =========================
    // HOME
    // =========================
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // =========================
    // PROVEEDORES
    // =========================
    Route::resource('proveedores', ProveedorController::class);
    Route::get('cambioestadoproveedor', [ProveedorController::class, 'cambioestado'])->name('cambioestadoproveedor');

    // =========================
    // PRODUCTOS
    // =========================
    Route::resource('productos', ProductoController::class);
    Route::get('cambioestadoproducto', [ProductoController::class, 'cambioestado'])->name('cambioestadoproducto');

    // =========================
    // ORDENES DE COMPRA
    // =========================
    Route::resource('ordencompras', OrdenCompraController::class);
    
    // CAMBIO DE ESTADO (POST para el switch)
    Route::post('cambioestadoordencompra', [OrdenCompraController::class, 'cambioestado'])->name('ordencompras.cambioestado');
    
    // PDF y EXCEL
    Route::get('ordencompras/pdf/{id}', [OrdenCompraController::class, 'generarPDF'])->name('ordencompras.pdf')->whereNumber('id');
    Route::get('ordencompras/excel/{id}', [OrdenCompraController::class, 'generarExcel'])->name('ordencompras.excel')->whereNumber('id');
    Route::get('ordenes/exportar', [OrdenCompraController::class, 'exportarExcel'])->name('ordenes.exportar');

    // =========================
    // DETALLE COMPRAS
    // =========================
    Route::resource('detallecompras', DetalleCompraController::class);

    // =========================
    // METODOS DE PAGO
    // =========================
    Route::resource('metodopagos', MetodoPagoController::class);
    Route::get('cambioestadometodopago', [MetodoPagoController::class, 'cambioestado'])->name('cambioestadometodopago');

    // =========================
    // PAGOS
    // =========================
    Route::resource('pagos', PagoController::class);
    Route::get('cambioestadopago', [PagoController::class, 'cambioestado'])->name('cambioestadopago');

    // =========================
    // PERFIL / FOTO DE PERFIL
    // =========================
    Route::post('/profile/upload-photo', [HomeController::class, 'uploadPhoto'])->name('profile.upload.photo');
});