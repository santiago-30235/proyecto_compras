@extends('layouts.app')

@section('title','Crear Producto')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Crear Producto</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary">
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
                                <i class="fas fa-box mr-2"></i> Información del Producto
                            </h3>
                        </div>
                        <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Nombre <strong style="color:red;">(*)</strong></label>
                                            <input type="text" name="nombre" class="form-control" placeholder="Ingrese el nombre" value="{{ old('nombre') }}" required>
                                            @error('nombre')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Precio de Compra <strong style="color:red;">(*)</strong></label>
                                            <input type="number" step="0.01" name="preciocompra" class="form-control" placeholder="Ingrese el precio" value="{{ old('preciocompra') }}" required>
                                            @error('preciocompra')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Stock Máximo <strong style="color:red;">(*)</strong></label>
                                            <input type="number" name="stockmaximo" class="form-control" placeholder="Ingrese el stock" value="{{ old('stockmaximo') }}" required>
                                            @error('stockmaximo')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Imagen</label>
                                            <input type="file" name="imagen" class="form-control-file" accept="image/*">
                                            @error('imagen')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label">Descripción</label>
                                            <textarea name="descripcion" rows="4" class="form-control" placeholder="Ingrese una descripción">{{ old('descripcion') }}</textarea>
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
                                        <a href="{{ route('productos.index') }}" class="btn btn-danger btn-block btn-flat">Atras</a>
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