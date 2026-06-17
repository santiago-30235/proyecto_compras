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

    <div class="container-fluid">
        @if(session('successMsg'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert" 
                 style="background-color: #e8f5e9; color: #2e7d32; border-left: 5px solid #4caf50; border-radius: 6px; padding: 14px 20px; font-size: 0.95rem; margin-bottom: 15px;">
                <span style="font-size: 1.2rem; margin-right: 12px;">✅</span>
                <div>
                    <strong>¡Operación Exitosa!</strong> {{ session('successMsg') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close" 
                        style="background: none; border: none; font-size: 1.2rem; color: #2e7d32; cursor: pointer; font-weight: bold; line-height: 1;"></button>
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
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <h3 style="font-weight: 600; color: #343a40; margin: 0;">
                                    <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>
                                    @yield('title')
                                </h3>
                                <div class="mt-2 mt-md-0">
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
                            <!-- SELECTOR DE REGISTROS POR PÁGINA -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2 mb-0">Mostrar:</label>
                                        <select class="form-control form-control-sm" style="width: auto;" onchange="window.location.href=this.value">
                                            @php
                                                $perPage = request('per_page', 10);
                                                $options = [10, 25, 50, 100];
                                            @endphp
                                            @foreach($options as $option)
                                                <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" 
                                                    {{ $perPage == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="ml-2">entradas</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-5">
                                    <!-- BARRA DE BÚSQUEDA -->
                                    <form method="GET" action="{{ route('ordencompras.index') }}" class="form-inline">
                                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                        <div class="input-group input-group-sm w-100">
                                            <input type="text" class="form-control" name="search" 
                                                   placeholder="Buscar por ID, proveedor, fecha..." 
                                                   value="{{ request('search') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                                @if(request('search'))
                                                    <a href="{{ route('ordencompras.index', ['per_page' => request('per_page', 10)]) }}" 
                                                       class="btn btn-danger">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- TABLA -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
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
                                        @forelse($ordencompras as $orden)
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
                                                    <form class="d-inline delete-form" action="{{ route('ordencompras.destroy', $orden->id) }}" method="POST" onsubmit="return confirmarEliminacion(event, this, 'Orden #{{ $orden->id }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No hay órdenes de compra registradas</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- PAGINACIÓN Y CONTADOR -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <strong>
                                        Mostrando {{ $ordencompras->firstItem() ?? 0 }} a {{ $ordencompras->lastItem() ?? 0 }} 
                                        de {{ $ordencompras->total() }} entradas
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $ordencompras->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
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

    // Inicializar los toggles de Bootstrap (para el switch de estado)
    $(function() {
        $('.toggle-class').bootstrapToggle({
            on: 'Activo',
            off: 'Inactivo',
            onstyle: 'success',
            offstyle: 'danger'
        });

        // Evento para cambiar estado via AJAX
        $('.toggle-class').on('change', function() {
            var id = $(this).data('id');
            var estado = $(this).prop('checked') ? '1' : '0';
            var $toggle = $(this);

            $.ajax({
                url: "{{ route('ordencompras.cambioestado') }}",
                method: 'POST',
                data: {
                    id: id,
                    estado: estado,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Estado actualizado correctamente');
                    }
                },
                error: function() {
                    toastr.error('Error al actualizar el estado');
                    $toggle.bootstrapToggle('toggle');
                }
            });
        });
    });

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@endsection