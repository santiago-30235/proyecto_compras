@extends('layouts.app')

@section('title', 'Órdenes de Compra')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Gestión de Órdenes de Compra</h1>
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
                                    <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <div>
                                    <a href="{{ route('ordenes.exportar') }}" class="btn btn-success mr-2" title="Exportar todas las órdenes a Excel">
                                        <i class="fas fa-file-excel mr-1"></i> Exportar Excel General
                                    </a>
                                    <a href="{{ route('ordencompras.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Nueva Orden
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Tipo Pago</th>
                                        <th>Saldo</th>
                                        <th>Estado</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ordencompras as $orden)
                                    <tr>
                                        <td>#{{ $orden->id }}</td>
                                        <td>{{ $orden->proveedor->nombre ?? 'N/A' }}</td>
                                        <td>{{ date('d/m/Y H:i', strtotime($orden->fecha)) }}</td>
                                        <td>${{ number_format($orden->total, 2) }}</td>
                                        <td>{{ ucfirst($orden->tipopago) }}</td>
                                        <td>
                                            @if($orden->saldopendiente > 0)
                                                <span class="badge badge-danger">${{ number_format($orden->saldopendiente, 2) }}</span>
                                            @else
                                                <span class="badge badge-success">Pagado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <input data-type="ordencompra" data-id="{{ $orden->id }}" class="toggle-class" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="Activo" data-off="Inactivo" {{ $orden->estado == '1' ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $orden->registradopor }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ordencompras.show', $orden->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('ordencompras.pdf', $orden->id) }}" class="btn btn-danger" title="Generar PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <a href="{{ route('ordencompras.edit', $orden->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <form class="d-inline delete-form" action="{{ route('ordencompras.destroy', $orden->id) }}" method="POST">
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
                lengthMenu: "Mostrar _MENU_ registros",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros)",
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