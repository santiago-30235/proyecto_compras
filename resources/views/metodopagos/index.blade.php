@extends('layouts.app')

@section('title', 'Métodos de Pago')

@section('content')

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-weight: bold; color: #343a40;">Gestión de Métodos de Pago</h1>
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
                                <a href="{{ route('metodopagos.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Nuevo Método
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metodopagos as $metodopago)
                                    <tr>
                                        <td>{{ $metodopago->id }}</td>
                                        <td>
                                            <strong>{{ ucfirst($metodopago->nombre) }}</strong>
                                        </td>
                                        <td>{{ ucfirst($metodopago->descripcion ?? 'Sin descripción') }}</td>
                                        <td>
                                            <input 
                                                data-type="metodopago" 
                                                data-id="{{ $metodopago->id }}" 
                                                class="toggle-class" 
                                                type="checkbox" 
                                                data-toggle="toggle" 
                                                data-onstyle="success" 
                                                data-offstyle="danger" 
                                                data-on="Activo" 
                                                data-off="Inactivo" 
                                                {{ $metodopago->estado == '1' ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $metodopago->registradopor }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('metodopagos.show', $metodopago->id) }}" class="btn btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('metodopagos.edit', $metodopago->id) }}" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <form class="d-inline delete-form" action="{{ route('metodopagos.destroy', $metodopago->id) }}" method="POST">
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