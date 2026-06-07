@extends('layouts.app')

@section('title', 'Crear Orden de Compra')

@section('content')
<style>
    .container-custom {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }
    .card-custom {
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .two-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .two-columns {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }
    .form-group {
        margin-bottom: 1rem;
    }
</style>

<div class="container-custom">
    <div class="card card-custom">
        <div class="card-header bg-secondary">
            <h3>Crear Orden de Compra</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('ordencompras.store') }}" id="ordenForm">
                @csrf
                
                <!-- Proveedor y Fecha en dos columnas -->
                <div class="two-columns">
                    <div class="form-group">
                        <label>Proveedor <strong style="color:red;">(*)</strong></label>
                        <select name="proveedor_id" class="form-control select2-busqueda" required>
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha y Hora <strong style="color:red;">(*)</strong></label>
                        <input type="datetime-local" name="fecha" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                
                <!-- Tipo Pago y Método Pago en dos columnas -->
                <div class="two-columns">
                    <div class="form-group">
                        <label>Tipo de Pago <strong style="color:red;">(*)</strong></label>
                        <select name="tipopago" id="tipopagoSelect" class="form-control" required>
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="metodopagoDiv">
                        <label>Método de Pago <strong style="color:red;">(*)</strong></label>
                        <select name="metodopago_id" class="form-control select2-busqueda" required>
                            <option value="">Seleccione un método</option>
                            @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo->id }}">{{ ucfirst($metodo->nombre) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Abono inicial (solo para crédito) -->
                <div id="abonoDiv" class="two-columns" style="display: none; margin-bottom: 20px;">
                    <div class="form-group">
                        <label>Abono inicial</label>
                        <input type="number" step="0.01" name="abono_inicial" id="abonoInicial" class="form-control" placeholder="0.00" min="0" value="0">
                        <small class="text-muted">Monto que pagará ahora</small>
                    </div>
                    <div class="form-group">
                        <label>Saldo pendiente después del abono</label>
                        <h4 id="saldoPendientePreview" class="text-warning">$0.00</h4>
                    </div>
                </div>
                
                <hr>
                
                <h4>Productos</h4>
                
                <!-- Producto y Cantidad en dos columnas -->
                <div class="two-columns">
                    <div class="form-group">
                        <label>Producto 1</label>
                        <select name="productos[0][id]" id="producto0" class="form-control select2-busqueda" required>
                            <option value="">Seleccione un producto</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" 
                                        data-precio="{{ $producto->preciocompra }}"
                                        data-stock="{{ $producto->stock }}"
                                        data-stockmaximo="{{ $producto->stockmaximo }}">
                                    {{ $producto->nombre }} - ${{ number_format($producto->preciocompra, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Cantidad 1</label>
                        <input type="number" name="productos[0][cantidad]" id="cantidad0" class="form-control" min="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Subtotal 1:</label>
                    <h4 id="subtotal0">$0.00</h4>
                </div>
                
                <!-- Más productos (opcional) -->
                <div id="productos-adicionales"></div>
                
                <button type="button" id="addProductoBtn" class="btn btn-sm btn-info">+ Agregar otro producto</button>
                
                <hr>
                
                <div class="form-group">
                    <label><strong>TOTAL:</strong></label>
                    <h2 id="totalGeneral" class="text-primary">$0.00</h2>
                    <input type="hidden" name="total" id="totalHidden" value="0">
                </div>
                
                <button type="submit" class="btn btn-primary">Registrar</button>
                <a href="{{ route('ordencompras.index') }}" class="btn btn-danger">Atras</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Elementos principales
    const producto1 = document.getElementById('producto0');
    const cantidad1 = document.getElementById('cantidad0');
    const subtotal1 = document.getElementById('subtotal0');
    const totalGeneral = document.getElementById('totalGeneral');
    const totalHidden = document.getElementById('totalHidden');
    const tipopagoSelect = document.getElementById('tipopagoSelect');
    const abonoDiv = document.getElementById('abonoDiv');
    const abonoInicial = document.getElementById('abonoInicial');
    const saldoPendientePreview = document.getElementById('saldoPendientePreview');
    
    let contadorProductos = 1;
    
    // Función para calcular todo
    function calcularTodo() {
        let total = 0;
        
        // Calcular producto 0
        let precio0 = producto1.options[producto1.selectedIndex]?.getAttribute('data-precio') || 0;
        let cant0 = parseInt(cantidad1.value) || 0;
        let subtotal0Valor = precio0 * cant0;
        subtotal1.innerHTML = '$' + subtotal0Valor.toFixed(2);
        total += subtotal0Valor;
        
        // Calcular productos adicionales
        for (let i = 1; i < contadorProductos; i++) {
            let productoSelect = document.getElementById(`producto${i}`);
            let cantidadInput = document.getElementById(`cantidad${i}`);
            let subtotalSpan = document.getElementById(`subtotal${i}`);
            
            if (productoSelect && cantidadInput && subtotalSpan) {
                let precio = productoSelect.options[productoSelect.selectedIndex]?.getAttribute('data-precio') || 0;
                let cantidad = parseInt(cantidadInput.value) || 0;
                let subtotal = precio * cantidad;
                subtotalSpan.innerHTML = '$' + subtotal.toFixed(2);
                total += subtotal;
            }
        }
        
        // Actualizar total
        totalGeneral.innerHTML = '$' + total.toFixed(2);
        totalHidden.value = total;
        
        // Actualizar saldo pendiente si es crédito
        if (tipopagoSelect.value === 'credito') {
            let abono = parseFloat(abonoInicial?.value) || 0;
            let saldo = total - abono;
            if (saldo < 0) saldo = 0;
            if (saldoPendientePreview) saldoPendientePreview.innerHTML = '$' + saldo.toFixed(2);
        }
    }
    
    // Mostrar/ocultar abono según tipo de pago
    tipopagoSelect.addEventListener('change', function() {
        if (this.value === 'credito') {
            abonoDiv.style.display = 'grid';
        } else {
            abonoDiv.style.display = 'none';
            if (abonoInicial) abonoInicial.value = 0;
            calcularTodo();
        }
    });
    
    // Eventos para producto 0
    producto1.addEventListener('change', calcularTodo);
    cantidad1.addEventListener('keyup', calcularTodo);
    cantidad1.addEventListener('change', calcularTodo);
    
    // Evento para abono
    if (abonoInicial) {
        abonoInicial.addEventListener('keyup', calcularTodo);
        abonoInicial.addEventListener('change', calcularTodo);
    }
    
    // Agregar producto adicional
    document.getElementById('addProductoBtn').addEventListener('click', function() {
        let container = document.getElementById('productos-adicionales');
        let nuevoIndice = contadorProductos;
        
        let newRow = document.createElement('div');
        newRow.className = 'producto-adicional';
        newRow.style.border = '1px solid #ddd';
        newRow.style.padding = '10px';
        newRow.style.marginTop = '10px';
        newRow.style.borderRadius = '5px';
        newRow.innerHTML = `
            <h5>Producto ${nuevoIndice + 1}</h5>
            <div class="two-columns">
                <div class="form-group">
                    <label>Producto ${nuevoIndice + 1}</label>
                    <select name="productos[${nuevoIndice}][id]" id="producto${nuevoIndice}" class="form-control select2-busqueda" required>
                        <option value="">Seleccione un producto</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" 
                                    data-precio="{{ $producto->preciocompra }}"
                                    data-stock="{{ $producto->stock }}"
                                    data-stockmaximo="{{ $producto->stockmaximo }}">
                                {{ $producto->nombre }} - ${{ number_format($producto->preciocompra, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantidad ${nuevoIndice + 1}</label>
                    <input type="number" name="productos[${nuevoIndice}][cantidad]" id="cantidad${nuevoIndice}" class="form-control" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Subtotal ${nuevoIndice + 1}:</label>
                <h5 id="subtotal${nuevoIndice}" class="subtotal-adicional">$0.00</h5>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-producto" data-indice="${nuevoIndice}">Eliminar</button>
            <hr>
        `;
        
        container.appendChild(newRow);
        
        // Inicializar Select2 en el nuevo producto
        let $nuevoSelect = $(newRow).find('select.select2-busqueda');
        if ($nuevoSelect.length) {
            $nuevoSelect.each(function() {
                initSelect2($(this));
            });
        }
        
        // Eventos para el nuevo producto
        let productoSelect = document.getElementById(`producto${nuevoIndice}`);
        let cantidadInput = document.getElementById(`cantidad${nuevoIndice}`);
        
        productoSelect.addEventListener('change', calcularTodo);
        cantidadInput.addEventListener('keyup', calcularTodo);
        cantidadInput.addEventListener('change', calcularTodo);
        
        // Botón eliminar
        newRow.querySelector('.remove-producto').addEventListener('click', function() {
            newRow.remove();
            calcularTodo();
        });
        
        contadorProductos++;
        calcularTodo();
    });
    
    // Calcular al inicio
    calcularTodo();
</script>
@push('scripts')
<script>
    function initSelect2($select) {
        $select.select2({
            width: '100%',
            dropdownParent: $select.parent()
        });
    }

    $(document).ready(function() {
        $('.select2-busqueda').each(function() {
            initSelect2($(this));
        });
    });
</script>
@endpush
@endsection