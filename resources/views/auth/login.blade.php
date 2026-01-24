@extends('layouts.app')

@section('title', 'KoKo Market | Iniciar sesión')

@section('content')
    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- COLUMNA IZQUIERDA (Imagen) -->
            <div class="col-md-6 d-none d-md-block p-0">
                <div class="login-image"></div>
            </div>

            <!-- COLUMNA DERECHA (Formulario) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">
                <div class="login-form-wrapper w-100" style="max-width: 420px;">

                    <h1 class="display-5 fw-bold text-center mb-2 login-title">¡Bienvenido!</h1>

                    <!-- ✅ Mensaje general -->
                    @if($errors->has('usu_usuario') || $errors->has('usu_contrasena'))
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" novalidate>
                        @csrf

                        <!-- Usuario -->
                        <div class="mb-3">
                            <label for="usu_usuario" class="form-label login-label">Usuario</label>
                            <input
                                type="text"
                                class="form-control login-input @error('usu_usuario') is-invalid @enderror"
                                id="usu_usuario"
                                name="usu_usuario"
                                value="{{ old('usu_usuario') }}"
                                placeholder="usuario"
                                required
                                autocomplete="username"
                                autofocus
                            >
                            @error('usu_usuario')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="usu_contrasena" class="form-label login-label">Contraseña</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control login-input @error('usu_contrasena') is-invalid @enderror"
                                    id="usu_contrasena"
                                    name="usu_contrasena"
                                    placeholder="********************"
                                    required
                                    autocomplete="current-password"
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="usu_contrasena">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('usu_contrasena')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-login w-100 mb-3">Log In</button>

                        <p class="text-center text-muted">
                            ¿No tienes cuenta?
                            <a href="{{ route('register') }}" class="register-link-accent">Crea una ahora</a>
                        </p>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/toggle-password.js') }}"></script>
@endpush

