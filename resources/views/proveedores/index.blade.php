@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Gestión de Proveedores</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #e8f5e9; color: #2e7d32; border-left: 5px solid #4caf50; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">✅</span>
                <div>
                    <strong>¡Operación Exitosa!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #2e7d32; cursor: pointer; font-weight: bold; line-height: 1;"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #ffebee; color: #c62828; border-left: 5px solid #ef5350; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">🚫</span>
                <div>
                    <strong>Atención del Sistema:</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #c62828; cursor: pointer; font-weight: bold; line-height: 1;"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #ffebee; color: #c62828; border-left: 5px solid #ef5350; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">🚫</span>
                <div>
                    <strong>Atención del Sistema:</strong> {{ $errors->first() }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #c62828; cursor: pointer; font-weight: bold; line-height: 1;"></button>
            </div>
        @endif
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-people-carry mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th>Estado</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proveedores as $proveedor)
                                    <tr>
                                        <td>#{{ $proveedor->id }}</td>
                                        <td>{{ $proveedor->nombre }}</td>
                                        <td>{{ $proveedor->documento }}</td>
                                        <td>{{ $proveedor->telefono }}</td>
                                        <td>{{ $proveedor->email }}</td>
                                        <td>{{ $proveedor->direccion ?? '—' }}</td>
                                        <td>
                                            <input data-type="proveedor" data-id="{{ $proveedor->id }}" class="toggle-class" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="danger" data-on="Activo" data-off="Inactivo" {{ $proveedor->estado == '1' ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $proveedor->registradopor }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('proveedores.show', $proveedor->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <form class="d-inline delete-form" action="{{ route('proveedores.destroy', $proveedor->id) }}" method="POST" onsubmit="return confirmarEliminacion(event, this, '{{ $proveedor->nombre }}')">
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

<!-- Modal de Confirmación -->
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
        document.getElementById('nombreElementoAEliminar').innerHTML = 'Proveedor: <strong>' + nombre + '</strong>';
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
</script>
@endsection