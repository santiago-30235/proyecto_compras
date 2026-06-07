@extends('layouts.app')

@section('title', 'Pagos')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Gestión de Pagos</h1>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.partial.msg')

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-credit-card mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <a href="{{ route('pagos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nuevo Pago
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Orden #</th>
                                        <th>Proveedor</th>
                                        <th>Fecha Pago</th>
                                        <th>Monto</th>
                                        <th>Método Pago</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                    <tr>
                                        <td>#{{ $pago->id }}</td>
                                        <td>
                                            <a href="{{ route('ordencompras.show', $pago->ordenCompra->id) }}" class="text-primary">
                                                Orden #{{ $pago->ordenCompra->id }}
                                            </a>
                                        </td>
                                        <td>{{ $pago->ordenCompra->proveedor->nombre ?? 'N/A' }}</td>
                                        <td>{{ date('d/m/Y H:i', strtotime($pago->fechapago)) }}</td>
                                        <td>
                                            <span class="badge badge-success" style="padding:8px 12px;">
                                                ${{ number_format($pago->monto, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst($pago->metodoPago->nombre ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>{{ $pago->registradopor }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('pagos.show', $pago->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('pagos.edit', $pago->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <form class="d-inline delete-form" action="{{ route('pagos.destroy', $pago->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@section('js')
<script>
    $(function() {
        $("#example1").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                lengthMenu: "Mostrar _MENU_ entradas",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });
    });
</script>
@endsection