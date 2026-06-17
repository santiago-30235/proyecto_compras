@extends('layouts.app')

@section('title','Detalles de Compras')

@section('content')

<div class="content-wrapper">

    {{-- ========================= --}}
    {{-- ENCABEZADO --}}
    {{-- ========================= --}}
    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-3">

                <div class="col-sm-6">

                    <h1 class="page-title">

                        <i class="fas fa-clipboard-list mr-2"></i>
                        Gestión de Detalles de Compra

                    </h1>

                </div>

                <div class="col-sm-6 text-right">

                    <a
                        href="{{ route('detallecompras.create') }}"
                        class="btn btn-primary btn-modern"
                    >

                        <i class="fas fa-plus mr-1"></i>
                        Nuevo Detalle

                    </a>

                </div>

            </div>

        </div>

    </section>

    {{-- ========================= --}}
    {{-- MENSAJES --}}
    {{-- ========================= --}}
    @include('layouts.partial.msg')

    {{-- ========================= --}}
    {{-- CONTENIDO --}}
    {{-- ========================= --}}
    <section class="content">

        <div class="container-fluid">

            <div class="row">

                <div class="col-12">

                    <div class="card modern-card">

                        {{-- ========================= --}}
                        {{-- HEADER --}}
                        {{-- ========================= --}}
                        <div class="card-header modern-card-header">

                            <h3 class="card-title">

                                <i class="fas fa-shopping-cart mr-2"></i>
                                Lista de Detalles

                            </h3>

                        </div>

                        {{-- ========================= --}}
                        {{-- TABLA --}}
                        {{-- ========================= --}}
                        <div class="card-body">

                            <div class="table-responsive">

                                <table
                                    id="example1"
                                    class="table custom-table table-hover datatable"
                                >

                                    <thead>

                                        <tr>

                                            <th>ID</th>

                                            <th>Orden</th>

                                            <th>Producto</th>

                                            <th>Cantidad</th>

                                            <th>Subtotal</th>

                                            <th>Registrado por</th>

                                            <th width="120px">Acciones</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach($detalles as $detalle)

                                            <tr>

                                                {{-- ID --}}
                                                <td>

                                                    <strong>

                                                        #{{ $detalle->id }}

                                                    </strong>

                                                </td>

                                                {{-- ORDEN --}}
                                                <td>

                                                    <span class="badge badge-info px-3 py-2">

                                                        Orden #{{ $detalle->ordencompra_id }}
<br>
<small>
Proveedor: {{ $detalle->ordenCompra->proveedor->nombre ?? 'N/A' }}
</small>
                                                    </span>

                                                </td>

                                                {{-- PRODUCTO --}}
                                                <td>

                                                    <strong>

                                                        {{ $detalle->producto->nombre ?? 'Producto no encontrado' }}

                                                    </strong>

                                                </td>

                                                {{-- CANTIDAD --}}
                                                <td>

                                                    <span class="badge badge-primary px-3 py-2">

                                                        {{ $detalle->cantidad }}

                                                    </span>

                                                </td>

                                                {{-- SUBTOTAL --}}
                                                <td>

                                                    <span class="badge badge-success px-3 py-2">

                                                        ${{ number_format($detalle->subtotal, 2) }}

                                                    </span>

                                                </td>

                                                {{-- REGISTRADO --}}
                                                <td>

                                                    <span class="badge badge-secondary px-3 py-2">

                                                        {{ $detalle->registradopor }}

                                                    </span>

                                                </td>

                                                {{-- ACCIONES --}}
                                                <td class="text-center">

                                                    <div class="btn-group btn-group-sm">

                                                        {{-- VER --}}
                                                        <a
                                                            href="{{ route('detallecompras.show', $detalle->id) }}"
                                                            class="btn btn-info btn-action"
                                                            title="Ver detalle"
                                                        >

                                                            <i class="fas fa-eye"></i>

                                                        </a>

                                                        {{-- EDITAR --}}
                                                        <a
                                                            href="{{ route('detallecompras.edit', $detalle->id) }}"
                                                            class="btn btn-primary btn-action"
                                                            title="Editar detalle"
                                                        >

                                                            <i class="fas fa-pencil-alt"></i>

                                                        </a>

                                                        {{-- ELIMINAR --}}
                                                        <form
                                                            class="d-inline delete-form"
                                                            action="{{ route('detallecompras.destroy', $detalle->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirmarEliminacion(event, this, 'Detalle #{{ $detalle->id }}')"
                                                        >

                                                            @csrf
                                                            @method('DELETE')

                                                            <button
                                                                type="submit"
                                                                class="btn btn-danger btn-action"
                                                                title="Eliminar detalle"
                                                            >

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

                            {{-- ========================= --}}
                            {{-- PAGINACIÓN --}}
                            {{-- ========================= --}}
                            <div class="mt-4 d-flex justify-content-center">

                                {{ $detalles->links() }}

                            </div>

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
</script>
@endsection