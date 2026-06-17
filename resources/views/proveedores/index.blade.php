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

    @include('layouts.partial.msg')

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-people-carry mr-2"></i>
                                    @yield('title')
                                </h3>
                                <a href="{{ route('proveedores.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-hover">
                                <thead class="text-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Documento</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Dirección</th>
                                        <th>Estado</th>
                                        <th>Registrado por</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proveedores as $proveedor)
                                    <tr>
                                        <td>{{ $proveedor->id }}</td>
                                        <td>{{ $proveedor->nombre }}</td>
                                        <td>{{ $proveedor->documento }}</td>
                                        <td>{{ $proveedor->telefono }}</td>
                                        <td>{{ $proveedor->email }}</td>
                                        <td>{{ $proveedor->direccion ?? '—' }}</td>
                                        <td>
                                            <input
                                                data-type="proveedor"
                                                data-id="{{ $proveedor->id }}"
                                                class="toggle-class"
                                                type="checkbox"
                                                data-onstyle="success"
                                                data-offstyle="danger"
                                                data-toggle="toggle"
                                                data-on="Activo"
                                                data-off="Inactivo"
                                                {{ $proveedor->estado == '1' ? 'checked' : '' }}
                                            >
                                        </td>
                                        <td>{{ $proveedor->registradopor }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('proveedores.show', $proveedor->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-eliminar-proveedor" 
                                                        data-id="{{ $proveedor->id }}"
                                                        data-nombre="{{ $proveedor->nombre }}"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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
<div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">¿Está seguro?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción no se puede deshacer.</p>
                <p class="text-muted" id="nombreProveedorAEliminar"></p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarProveedor'));
    let proveedorId = null;

    document.querySelectorAll('.btn-eliminar-proveedor').forEach(button => {
        button.addEventListener('click', function() {
            proveedorId = this.dataset.id;
            document.getElementById('nombreProveedorAEliminar').innerHTML = 
                'Proveedor: <strong>' + this.dataset.nombre + '</strong>';
            document.getElementById('mensajeErrorEliminar').classList.add('d-none');
            modalEliminar.show();
        });
    });

    document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
        const btn = this;
        const mensajeError = document.getElementById('mensajeErrorEliminar');
        
        btn.disabled = true;
        btn.innerHTML = 'Eliminando...';

        fetch('/proveedores/' + proveedorId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalEliminar.hide();
                location.reload();
            } else {
                mensajeError.classList.remove('d-none');
                mensajeError.textContent = data.message;
                btn.disabled = false;
                btn.innerHTML = 'Sí, eliminar';
            }
        })
        .catch(error => {
            mensajeError.classList.remove('d-none');
            mensajeError.textContent = 'Error al procesar la solicitud.';
            btn.disabled = false;
            btn.innerHTML = 'Sí, eliminar';
        });
    });

    document.getElementById('modalEliminarProveedor').addEventListener('hidden.bs.modal', function() {
        const btn = document.getElementById('btnConfirmarEliminar');
        btn.disabled = false;
        btn.innerHTML = 'Sí, eliminar';
        document.getElementById('mensajeErrorEliminar').classList.add('d-none');
    });
});
</script>
@endpush