@extends('layouts.app')

@section('title', 'Editar Orden de Compra')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
        </div>
    </section>

    @include('layouts.partial.msg')

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3>@yield('title')</h3>
                        </div>
                        <form method="POST" action="{{ route('ordencompras.update', $orden->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proveedor <strong style="color:red;">(*)</strong></label>
                                            <select name="proveedor_id" class="form-control select2-busqueda rounded-lg border-gray-300" required>
                                                @foreach($proveedores as $proveedor)
                                                    <option value="{{ $proveedor->id }}" {{ $orden->proveedor_id == $proveedor->id ? 'selected' : '' }}>
                                                        {{ $proveedor->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha y Hora <strong style="color:red;">(*)</strong></label>
                                            <input type="datetime-local" name="fecha" class="form-control rounded-lg border-gray-300" value="{{ date('Y-m-d\TH:i', strtotime($orden->fecha)) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tipo de Pago <strong style="color:red;">(*)</strong></label>
                                            <select name="tipopago" class="form-control select2-busqueda rounded-lg border-gray-300" required>
                                                <option value="contado" {{ $orden->tipopago == 'contado' ? 'selected' : '' }}>Contado</option>
                                                <option value="credito" {{ $orden->tipopago == 'credito' ? 'selected' : '' }}>Crédito</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Método de Pago</label>
                                            <select name="metodopago_id" class="form-control select2-busqueda rounded-lg border-gray-300">
                                                <option value="">Seleccione un método</option>
                                                @foreach($metodosPago as $metodo)
                                                    <option value="{{ $metodo->id }}" {{ optional($orden->pagos->first())->metodopago_id == $metodo->id ? 'selected' : '' }}>
                                                        {{ ucfirst($metodo->nombre) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Número de comprobante</label>
                                            <input type="text" name="numero_comprobante" class="form-control rounded-lg border-gray-300" value="{{ $orden->numero_comprobante ?? '' }}" placeholder="Nro. factura o comprobante">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Observaciones</label>
                                            <textarea name="observaciones" rows="4" class="form-control rounded-lg border-gray-300" placeholder="Notas internas o detalles adicionales">{{ $orden->observaciones ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <strong>Nota:</strong> El estado de la orden y del proveedor no se edita desde este formulario.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-lg-2 col-xs-4">
                                        <button type="submit" class="btn btn-primary btn-block btn-flat">Actualizar</button>
                                    </div>
                                    <div class="col-lg-2 col-xs-4">
                                        <a href="{{ route('ordencompras.index') }}" class="btn btn-danger btn-block btn-flat">Atras</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection