@extends('layouts.app')

@php
    use App\Constants\ClienteColumns as CliCol;
    use App\Constants\UsuarioColumns as UsuCol;

    // Configuración del formulario
    $placeholders = config('register_messages.placeholders');
    $maxlength = config('register_messages.maxlength');
@endphp


@section('title', 'KoKo Market | Crear cuenta')

@section('content')
    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- COLUMNA IZQUIERDA (Imagen / marketing) -->
            <div class="col-md-6 d-none d-md-flex p-0">
                <div class="register-image-wrapper w-100 h-100">
                    <div class="register-image"></div>
                    <div class="register-text">
                        <h1 class="display-6 fw-bold text-white mb-0">Donde comprar</h1>
                        <h1 class="display-6 fw-bold text-white">es disfrutar.</h1>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA (Formulario) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">
                <div class="register-form-wrapper w-100" style="max-width: 460px;">

                    <h1 class="display-5 fw-bold mb-2">Crea tu cuenta</h1>
                    <p class="text-muted mb-4">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="register-link-accent">Log in</a>
                    </p>

                    {{-- Errores globales --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="register-form" method="POST" action="{{ route('register.store') }}" novalidate>
                        @csrf

                        {{-- id_cliente (para escenario cliente existente sin usuario) --}}
                        <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id') }}">

                        {{-- Cédula / RUC --}}
                        <div class="mb-3">
                            <label class="form-label">Cédula / RUC</label>
                            <input
                                type="text"
                                class="form-control register-input @error(CliCol::RUC_CED) is-invalid @enderror"
                                name="{{ CliCol::RUC_CED }}"
                                id="ruc_cedula"
                                value="{{ old(CliCol::RUC_CED) }}"
                                placeholder="{{ $placeholders['ruc_cedula'] }}"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="{{ $maxlength['ruc_cedula'] }}"
                                required
                                data-buscar-url="{{ route('clientes.buscarPorRuc') }}"
                            >
                            @error(CliCol::RUC_CED)
                            <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre / Razón social --}}
                        <div class="mb-3">
                            <label class="form-label">Nombre o razón social</label>
                            <input
                                type="text"
                                class="form-control register-input @error(CliCol::NOMBRE) is-invalid @enderror"
                                name="{{ CliCol::NOMBRE }}"
                                id="cli_nombre"
                                value="{{ old(CliCol::NOMBRE) }}"
                                placeholder="{{ $placeholders['nombre'] }}"
                                maxlength="{{ $maxlength['nombre'] }}"
                                required
                            >
                            @error(CliCol::NOMBRE)
                            <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 🔽 Campos extra de cliente: ocultos por defecto, solo se muestran en NO_CLIENTE --}}
                        <div
                            id="cliente-extra-fields"
                            class="{{ $mostrarCamposExtra ? '' : 'd-none' }}"
                            data-initial-visible="{{ $mostrarCamposExtra ? '1' : '0' }}"
                        >
                            {{-- Email --}}
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    class="form-control register-input @error(CliCol::MAIL) is-invalid @enderror"
                                    name="{{ CliCol::MAIL }}"
                                    id="cli_mail"
                                    value="{{ old(CliCol::MAIL) }}"
                                    placeholder="{{ $placeholders['email'] }}"
                                    maxlength="{{ $maxlength['email'] }}"
                                >
                                @error(CliCol::MAIL)
                                <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Celular --}}
                            <div class="mb-3">
                                <label class="form-label">Celular</label>
                                <input
                                    type="text"
                                    class="form-control register-input @error(CliCol::TELEFONO) is-invalid @enderror"
                                    name="{{ CliCol::TELEFONO }}"
                                    id="cli_telefono"
                                    value="{{ old(CliCol::TELEFONO) }}"
                                    placeholder="{{ $placeholders['celular'] }}"
                                    inputmode="numeric"
                                    maxlength="{{ $maxlength['celular'] }}"
                                >
                                @error(CliCol::TELEFONO)
                                <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Ciudad --}}
                            <div class="mb-3">
                                <label class="form-label">Ciudad</label>
                                <select
                                    class="form-select register-input @error(CliCol::CIUDAD_ID) is-invalid @enderror"
                                    name="{{ CliCol::CIUDAD_ID }}"
                                    id="cli_ciudad"
                                >
                                    <option value="">{{ $placeholders['ciudad'] }}</option>
                                    @foreach ($ciudades as $ciudad)
                                        <option
                                            value="{{ $ciudad->id_ciudad }}"
                                            {{ old(CliCol::CIUDAD_ID) == $ciudad->id_ciudad ? 'selected' : '' }}
                                        >
                                            {{ $ciudad->ciu_descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error(CliCol::CIUDAD_ID)
                                <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Dirección --}}
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <input
                                    type="text"
                                    class="form-control register-input @error(CliCol::DIRECCION) is-invalid @enderror"
                                    name="{{ CliCol::DIRECCION }}"
                                    id="cli_direccion"
                                    value="{{ old(CliCol::DIRECCION) }}"
                                    placeholder="{{ $placeholders['direccion'] }}"
                                    maxlength="{{ $maxlength['direccion'] }}"
                                >
                                @error(CliCol::DIRECCION)
                                <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ======== DATOS DE ACCESO (USUARIO) ======== --}}
                        <h5 class="mt-4 mb-3">Datos de acceso</h5>

                        {{-- Usuario --}}
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input
                                type="text"
                                class="form-control register-input @error(\App\Constants\UsuarioColumns::USERNAME) is-invalid @enderror"
                                name="{{ \App\Constants\UsuarioColumns::USERNAME }}"
                                id="usu_usuario"
                                value="{{ old(\App\Constants\UsuarioColumns::USERNAME) }}"
                                placeholder="{{ $placeholders['usuario'] }}"
                                maxlength="{{ $maxlength['usuario'] }}"
                                required
                            >
                            @error(\App\Constants\UsuarioColumns::USERNAME)
                            <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Contraseña --}}
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control register-input @error(\App\Constants\UsuarioColumns::PASSWORD) is-invalid @enderror"
                                    name="{{ \App\Constants\UsuarioColumns::PASSWORD }}"
                                    id="usu_contrasena"
                                    placeholder="{{ $placeholders['password'] }}"
                                    maxlength="{{ $maxlength['password'] }}"
                                    required
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="usu_contrasena">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error(\App\Constants\UsuarioColumns::PASSWORD)
                            <div class="invalid-feedback d-block" data-blade>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirmar contraseña --}}
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="form-control register-input"
                                    name="{{ \App\Constants\UsuarioColumns::PASSWORD }}_confirmation"
                                    id="usu_contrasena_confirm"
                                    placeholder="{{ $placeholders['password_c'] }}"
                                    maxlength="{{ $maxlength['password'] }}"
                                    required
                                >
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="usu_contrasena_confirm">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-register w-100 mb-4" id="btn_submit_register">Sign up</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- Pasar mensajes de configuración al JS --}}
    <script>
        window.REGISTER_MESSAGES = @json(config('register_messages.errors'));
    </script>
    <script src="{{ asset('assets/js/register.js') }}"></script>
    <script src="{{ asset('assets/js/toggle-password.js') }}"></script>
@endpush
