@extends('layouts.app')

@section('title', 'Crear Proveedor')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Crear Proveedor</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.partial.msg')

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-2"></i> Información del Proveedor
                            </h3>
                        </div>

                        <form method="POST" action="{{ route('proveedores.store') }}">
                            @csrf

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Nombre <strong style="color:red;">(*)</strong></label>
                                            <input type="text" name="nombre" class="form-control" placeholder="Ingrese el nombre" autocomplete="off" value="{{ old('nombre') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Documento <strong style="color:red;">(*)</strong></label>
                                            <input type="text" name="documento" class="form-control" placeholder="Ingrese el documento" autocomplete="off" value="{{ old('documento') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Teléfono <strong style="color:red;">(*)</strong></label>
                                            <input type="text" name="telefono" class="form-control" placeholder="Ingrese el teléfono" autocomplete="off" value="{{ old('telefono') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Email <strong style="color:red;">(*)</strong></label>
                                            <input type="email" name="email" class="form-control" placeholder="Ingrese el correo" autocomplete="off" value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Dirección</label>
                                            <input type="text" name="direccion" class="form-control" placeholder="Ingrese la dirección" autocomplete="off" value="{{ old('direccion') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-lg-2 col-xs-4">
                                        <button type="submit" class="btn btn-primary btn-block btn-flat">Registrar</button>
                                    </div>
                                    <div class="col-lg-2 col-xs-4">
                                        <a href="{{ route('proveedores.index') }}" class="btn btn-danger btn-block btn-flat">Atrás</a>
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