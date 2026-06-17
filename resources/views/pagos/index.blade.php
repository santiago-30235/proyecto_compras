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
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-credit-card mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <div class="mt-2 mt-md-0">
                                    <a href="{{ route('pagos.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Nuevo Pago
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- TABLA CON DATATABLES (IGUAL QUE PROVEEDORES) -->
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
                                            <a href="{{ route('ordencompras.show', $pago->ordenCompra->id ?? 0) }}" class="text-primary">
                                                Orden #{{ $pago->ordenCompra->id ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $pago->ordenCompra->proveedor->nombre ?? 'N/A' }}</td>
                                        <td>{{ date('d/m/Y H:i', strtotime($pago->fechapago)) }}</td>
                                        <td>
                                            <span class="badge badge-success" style="padding:8px 12px; font-size: 14px;">
                                                ${{ number_format($pago->monto, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info" style="padding:6px 12px; font-size: 13px;">
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
                                                <form class="d-inline delete-form" action="{{ route('pagos.destroy', $pago->id) }}" method="POST" onsubmit="return confirmarEliminacion(event, this, 'Pago #{{ $pago->id }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
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

<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalConfirmarEliminarLabel">¿Está seguro?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción no se puede deshacer.</p>
                <p class="text-muted" id="nombreElementoAEliminar"></p>
                <div id="mensajeErrorEliminar" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Sí, eliminar</button>
            </div>
        </div>
    </div>
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

    let formularioAEliminar = null;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));

    function confirmarEliminacion(event, form, nombre) {
        event.preventDefault();
        formularioAEliminar = form;
        document.getElementById('nombreElementoAEliminar').innerHTML = 'Elemento: <strong>' + nombre + '</strong>';
        document.getElementById('mensajeErrorEliminar').classList.add('d-none');
        modal.show();
        return false;
    }

    document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
        if (formularioAEliminar) {
            this.disabled = true;
            this.innerHTML = 'Eliminando...';
            formularioAEliminar.submit();
        }
    });

    document.getElementById('modalConfirmarEliminar').addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btnConfirmarEliminar');
        btn.disabled = false;
        btn.innerHTML = 'Sí, eliminar';
        formularioAEliminar = null;
        document.getElementById('mensajeErrorEliminar').classList.add('d-none');
    });

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@endsection