<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="
    background: #fff;
    border-bottom: 2px solid #e2e8f0;
    padding: 0 16px;
    min-height: 58px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
">

    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="
                width: 38px; height: 38px;
                display: flex; align-items: center; justify-content: center;
                border-radius: 8px;
                color: #4a5568;
                transition: background 0.15s;
            "
            onmouseover="this.style.background='#f7fafc'"
            onmouseout="this.style.background='transparent'">
                <i class="fas fa-bars" style="font-size: 16px;"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto d-flex align-items-center" style="gap: 8px;">

        <li class="nav-item">
            <div style="
                display: flex; align-items: center; gap: 10px;
                background: #f7fafc;
                border: 0.5px solid #e2e8f0;
                border-radius: 30px;
                padding: 5px 14px 5px 6px;
            ">
                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden;">
                    @php
                        $photo = Auth::user()->photo;
                    @endphp
                    @if ($photo)
                        <img src="{{ asset($photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <img src="{{ asset('backend/dist/img/icon.jpg') }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @endif
                </div>
                <div style="line-height: 1.3;">
                    <div style="font-size: 13px; font-weight: 600; color: #2d3748;">
                        {{ Auth::user()->name }}
                    </div>
                    <div style="font-size: 10px; color: #a0aec0;">
                        {{ Auth::user()->email }}
                    </div>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <div style="width: 1px; height: 28px; background: #e2e8f0;"></div>
        </li>

        <li class="nav-item">
            <a class="nav-link"
               href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               title="Cerrar Sesión"
               role="button"
               style="
                   width: 38px; height: 38px;
                   display: flex; align-items: center; justify-content: center;
                   border-radius: 8px;
                   border: 0.5px solid #fed7d7;
                   background: #fff5f5;
                   transition: background 0.15s;
               "
               onmouseover="this.style.background='#fed7d7'"
               onmouseout="this.style.background='#fff5f5'">
                <i class="fas fa-power-off" style="font-size: 15px; color: #c53030;"></i>
            </a>

            <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>
        </li>

    </ul>
</nav>