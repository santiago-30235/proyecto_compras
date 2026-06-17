@extends('layouts.app')

@section('title','Editar Proveedor')

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
                        <form method="POST" action="{{ route('proveedores.update', $proveedor->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">Nombre <strong style="color:red;">(*)</strong></label>
                                            <input type="text" class="form-control" name="nombre" placeholder="Nombre del proveedor" autocomplete="off" value="{{ $proveedor->nombre }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">Documento <strong style="color:red;">(*)</strong></label>
                                            <input type="text" class="form-control" name="documento" placeholder="Número de documento" autocomplete="off" value="{{ $proveedor->documento }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">Teléfono <strong style="color:red;">(*)</strong></label>
                                            <input type="text" class="form-control" name="telefono" placeholder="Teléfono" autocomplete="off" value="{{ $proveedor->telefono }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">Email <strong style="color:red;">(*)</strong></label>
                                            <input type="email" class="form-control" name="email" placeholder="Correo electrónico" autocomplete="off" value="{{ $proveedor->email }}">
                                        </div>
                                    </div>
                                </div>
                                <!-- DIRECCIÓN: AHORA OCUPA SOLO LA MITAD (col-lg-6) -->
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6 col-md-6 col-xs-12">
                                        <div class="form-group label-floating">
                                            <label class="control-label">Dirección</label>
                                            <input type="text" class="form-control" name="direccion" placeholder="Dirección" autocomplete="off" value="{{ $proveedor->direccion }}">
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
                                        <a href="{{ route('proveedores.index') }}" class="btn btn-danger btn-block btn-flat">Atras</a>
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