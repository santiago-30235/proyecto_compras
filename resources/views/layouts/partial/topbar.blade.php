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
            <div data-toggle="modal" data-target="#modalCambiarFoto" data-bs-toggle="modal" data-bs-target="#modalCambiarFoto" style="
                display: flex; align-items: center; gap: 10px;
                background: #f7fafc;
                border: 0.5px solid #e2e8f0;
                border-radius: 30px;
                padding: 5px 14px 5px 6px;
                cursor: pointer;
                transition: all 0.2s;
            "
            onmouseover="this.style.background='#edf2f7'; this.style.borderColor='#cbd5e0';"
            onmouseout="this.style.background='#f7fafc'; this.style.borderColor='#e2e8f0';">
                
                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; position: relative;">
                    @php
                        $photo = Auth::user()->photo;
                    @endphp
                    @if ($photo)
                        <img src="{{ asset($photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random&color=ffffff&size=128" style="width: 100%; height: 100%; object-fit: cover;">
                    @endif
                </div>
                
                <div style="line-height: 1.3;">
                    <div style="font-size: 13px; font-weight: 600; color: #2d3748;">
                        {{ Auth::user()->name }} <i class="fas fa-camera text-muted" style="font-size: 10px; margin-left: 2px;"></i>
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

<div class="modal fade" id="modalCambiarFoto" tabindex="-1" role="dialog" aria-labelledby="modalCambiarFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #edf2f7; background: #f7fafc; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title" id="modalCambiarFotoLabel" style="font-weight: 600; color: #2d3748;">
                    <i class="fas fa-user-circle text-primary mr-2"></i> Actualizar Foto de Perfil
                </h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('profile.upload.photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body text-center p-4">
                    <div class="mb-3 mx-auto" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        @if ($photo)
                            <img src="{{ asset($photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random&color=ffffff&size=128" style="width: 100%; height: 100%; object-fit: cover;">
                        @endif
                    </div>
                    <p class="text-muted small mb-4">Selecciona una imagen de tu computadora (.jpg, .jpeg o .png)</p>
                    
                    <div class="custom-file text-left">
                        <input type="file" name="photo" class="custom-file-input" id="inputFoto" accept="image/*" required>
                        <label class="custom-file-label" id="labelFoto" for="inputFoto">Seleccionar archivo...</label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #edf2f7; background: #f7fafc; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 8px; background: #3b82f6; border: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('inputFoto');
        const label = document.getElementById('labelFoto');
        if(input) {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                label.textContent = fileName;
            });
        }
    });
</script>