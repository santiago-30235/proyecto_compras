{{-- =========================================================
     SIDEBAR PRINCIPAL
========================================================= --}}
<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- =========================================================
         LOGO
    ========================================================= --}}
<a href="{{ url('/home') }}" class="brand-link logo-container"
   style="display: flex; align-items: center; justify-content: center; height: 120px; padding: 10px 15px; overflow: hidden;">

    <img
        src="{{ asset('backend/dist/img/image_login.jpeg') }}"
        alt="Logo"
        class="logo-sidebar"
        style="max-height: 105px; max-width: 230px; width: auto; height: auto; object-fit: contain;"
    >

</a>

    {{-- =========================================================
         CONTENIDO SIDEBAR
    ========================================================= --}}
    <div class="sidebar">

        {{-- =========================================================
             MENÚ
        ========================================================= --}}
        <nav class="mt-2">

            <ul
                class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false"
            >

                {{-- =========================================================
                     PANEL DE CONTROL
                ========================================================= --}}
                <li class="nav-item">

                    <a href="{{ url('/home') }}" class="nav-link">

                        <i class="nav-icon fas fa-th"></i>

                        <p>
                            Panel De Control
                        </p>

                    </a>

                </li>


                {{-- =========================================================
                     MENÚ COMPRAS
                ========================================================= --}}
                <li class="nav-item">

                    <a href="#" class="nav-link">

                        <i class="nav-icon fa fa-shopping-cart"></i>

                        <p>
                            Compras
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>


                    {{-- =========================================================
                         SUBMENÚ COMPRAS
                    ========================================================= --}}
                    <ul class="nav nav-treeview">


                        {{-- =========================================================
                             PROVEEDORES
                        ========================================================= --}}
                        <li class="nav-item">

                            <a href="{{ route('proveedores.index') }}" class="nav-link">

                                <i class="nav-icon fas fa-people-carry"></i>

                                <p>Proveedores</p>

                            </a>

                        </li>


                        {{-- =========================================================
                             PRODUCTOS
                        ========================================================= --}}
                        <li class="nav-item">

                            <a href="{{ route('productos.index') }}" class="nav-link">

                                <i class="nav-icon fas fa-box"></i>

                                <p>Productos</p>

                            </a>

                        </li>


                        {{-- =========================================================
                             ÓRDENES
                        ========================================================= --}}
                        <li class="nav-item">

                            <a href="{{ route('ordencompras.index') }}" class="nav-link">

                                <i class="nav-icon fas fa-file-invoice"></i>

                                <p>Órdenes de Compra</p>

                            </a>

                        </li>


                        {{-- =========================================================
                             MÉTODOS DE PAGO
                        ========================================================= --}}
                        <li class="nav-item">

                            <a href="{{ route('metodopagos.index') }}" class="nav-link">

                                <i class="nav-icon fas fa-credit-card"></i>

                                <p>Métodos de Pago</p>

                            </a>

                        </li>


                        {{-- =========================================================
                             PAGOS
                        ========================================================= --}}
                        <li class="nav-item">

                            <a href="{{ route('pagos.index') }}" class="nav-link">

                                <i class="nav-icon fas fa-check-circle"></i>

                                <p>Pagos</p>

                            </a>
                        </li>
                    </ul>

                </li>

            </ul>

        </nav>

    </div>

</aside>