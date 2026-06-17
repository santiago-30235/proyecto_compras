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
                                    <form method="GET" action="{{ route('pagos.index') }}" class="form-inline">
                                        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                        <div class="input-group input-group-sm w-100">
                                            <input type="text" class="form-control" name="search" 
                                                   placeholder="Buscar por ID, orden, monto..." 
                                                   value="{{ request('search') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                                @if(request('search'))
                                                    <a href="{{ route('pagos.index', ['per_page' => request('per_page', 10)]) }}" 
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
                                        @forelse($pagos as $pago)
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
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No hay pagos registrados</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- PAGINACIÓN Y CONTADOR -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <strong>
                                        Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} 
                                        de {{ $pagos->total() }} entradas
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-right">
                                        {{ $pagos->links('pagination::bootstrap-4') }}
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

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@endsection