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
                            <input
                                type="password"
                                class="form-control login-input @error('usu_contrasena') is-invalid @enderror"
                                id="usu_contrasena"
                                name="usu_contrasena"
                                placeholder="********************"
                                required
                                autocomplete="current-password"
                            >

                            @error('usu_contrasena')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-login w-100">Log In</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
