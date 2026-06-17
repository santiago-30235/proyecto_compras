@extends('layouts.app')

@section('title', 'Productos')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Gestión de Productos</h1>
                </div>
            </div>
        </div>
    </section>

    @include('layouts.partial.msg')

    <!-- MENSAJES DE ÉXITO Y ERROR -->
    @if(session('success'))
        <div class="container-fluid">
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #e8f5e9; color: #2e7d32; border-left: 5px solid #4caf50; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">✅</span>
                <div>
                    <strong>¡Operación Exitosa!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #2e7d32; cursor: pointer; font-weight: bold; line-height: 1;"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container-fluid">
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #ffebee; color: #c62828; border-left: 5px solid #ef5350; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">🚫</span>
                <div>
                    <strong>Atención del Sistema:</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #c62828; cursor: pointer; font-weight: bold; line-height: 1;"></button>
            </div>
        </div>
    @endif

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-box-open mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <a href="{{ route('productos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagen</th>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $producto)
                                    <tr>
                                        <td>{{ $producto->id }}</td>
                                        <td>
                                            @if($producto->imagen)
                                                <img src="{{ asset($producto->imagen) }}" style="width:50px; height:50px; object-fit:cover; border-radius:8px;">
                                            @else
                                                <div style="width:50px; height:50px; background:#f1f5f9; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $producto->nombre }}</strong><br>
                                            <small class="text-muted">{{ Str::limit($producto->descripcion, 40) }}</small>
                                        </td>
                                        <td>${{ number_format($producto->preciocompra, 2) }}</br><small class="text-muted">Compra</small></td>
                                        <td>
                                            {{ $producto->stock }} uds
                                            <br>
                                            <small class="text-muted">Máx: {{ $producto->stockmaximo }}</small>
                                        </td>
                                        <td>
                                            <input 
                                                data-type="producto" 
                                                data-id="{{ $producto->id }}" 
                                                class="toggle-class" 
                                                type="checkbox" 
                                                data-toggle="toggle" 
                                                data-onstyle="success" 
                                                data-offstyle="danger" 
                                                data-on="Activo" 
                                                data-off="Inactivo" 
                                                {{ $producto->estado == '1' ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $producto->registradopor }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <form class="d-inline delete-form" action="{{ route('productos.destroy', $producto->id) }}" method="POST" onsubmit="return confirmarEliminacion(event, this, '{{ $producto->nombre }}')">
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
        document.getElementById('nombreElementoAEliminar').innerHTML = 'Producto: <strong>' + nombre + '</strong>';
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