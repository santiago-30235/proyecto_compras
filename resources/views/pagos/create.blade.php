@extends('layouts.app')

@section('title', 'Crear Pago')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Crear Pago</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
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
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">
                                <i class="fas fa-credit-card mr-2"></i> Información del Pago
                            </h3>
                        </div>
                        <form method="POST" action="{{ route('pagos.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Orden de Compra <strong style="color:red;">(*)</strong></label>
                                            <select name="ordencompra_id" class="form-control select2-busqueda" id="ordenSelect" required>
                                                <option value="">Seleccione una orden</option>
                                                @foreach($ordenes as $orden)
                                                    <option value="{{ $orden->id }}" data-saldo="{{ $orden->saldopendiente }}">
                                                        Orden #{{ $orden->id }} - {{ $orden->proveedor->nombre }} - Saldo: ${{ number_format($orden->saldopendiente, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Monto <strong style="color:red;">(*)</strong></label>
                                            <input type="number" step="0.01" name="monto" class="form-control" id="montoInput" placeholder="Ingrese el monto" min="0.01" required>
                                            <small id="saldoInfo" class="text-muted"></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Método de Pago <strong style="color:red;">(*)</strong></label>
                                            <select name="metodopago_id" class="form-control select2-busqueda" required>
                                                <option value="">Seleccione un método</option>
                                                @foreach($metodos as $metodo)
                                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                                @endforeach
                                            </select>
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
                                        <a href="{{ route('pagos.index') }}" class="btn btn-danger btn-block btn-flat">Atras</a>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-busqueda').select2({
            width: '100%'
        });

        $('#ordenSelect').change(function() {
            var saldo = $(this).find(':selected').data('saldo');
            if (saldo) {
                $('#saldoInfo').html('Saldo pendiente de la orden: $' + saldo.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                $('#montoInput').attr('max', saldo);
            } else {
                $('#saldoInfo').html('');
                $('#montoInput').removeAttr('max');
            }
        });
    });
</script>
@endpush