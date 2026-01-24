@extends('layouts.app')

@section('title', 'KoKo Market | Carrito de Compras')

@section('content')

    {{-- ✅ OVERLAY SOLO UNA VEZ, DESPUÉS DEL LOGIN --}}
    @if(session()->pull('login_success'))
        <div id="login-success-overlay"
             class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center bg-dark bg-opacity-75"
             style="z-index: 9999; display: none;">
            <div class="bg-white rounded-4 shadow p-4 text-center" style="max-width: 320px; width: 90%;">
                <div class="spinner-border mb-3" role="status"></div>
                <h2 class="h5 mb-2">
                    {{ session('login_success_message', '¡Inicio de sesión exitoso!') }}
                </h2>
                <p class="mb-0 text-muted">Bienvenido a KoKo Market, preparando tu carrito...</p>
            </div>
        </div>
    @endif

    <main class="container pt-5 mt-5 mb-5 carrito-container">

        <div class="row">

            <div class="col-lg-8 mb-4">
                <h2 class="mb-4">Carrito de Compras</h2>
                <div id="js-cart-warning" class="alert alert-danger mb-3 d-none"></div>

                @if(session('warning'))
                    <div class="alert alert-warning mb-3">
                        <strong>⚠ Atención:</strong> {{ session('warning') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mb-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('mensaje_stock'))
                    <div class="alert alert-danger mb-3">
                        <strong>⚠ Error:</strong> {{ session('mensaje_stock') }}
                    </div>
                @endif

                <div class="card mb-3 d-none d-md-block" style="background-color:#f6e6a5;">
                    <div class="card-body py-2">
                        <div class="row text-center fw-bold">
                            <div class="col-5">Producto</div>
                            <div class="col-2">Precio</div>
                            <div class="col-3">Cantidad</div>
                            <div class="col-2">Subtotal</div>
                        </div>
                    </div>
                </div>

                @if($items->isEmpty())
                    <div class="alert alert-warning text-center">
                        🛒 Tu carrito está vacío.
                        <a href="{{ route('catalogo.index') }}" class="alert-link">
                            Ver productos
                        </a>
                    </div>
                @else
                    @foreach($items as $item)

                        <div class="card mb-3 carrito-item" data-product-id="{{ $item->id_producto }}">
                            <div class="card-body">

                                {{-- DESKTOP --}}
                                <div class="row align-items-center text-center d-none d-md-flex">

                                    <div class="col-5 d-flex align-items-center text-start">

                                        <form method="POST"
                                              action="{{ route('carrito.destroy', $item->id_producto) }}"
                                              class="me-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="btn btn-link text-danger p-0"
                                                    onclick="confirmarEliminacion(this)">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>

                                        <a href="{{ route('productos.show', $item->token) }}"
                                           class="d-flex align-items-center text-decoration-none text-dark">

                                            <img src="{{ $item->imagen }}"
                                                 class="rounded me-3"
                                                 style="width:60px;height:60px;object-fit:cover;"
                                                 alt="{{ $item->descripcion }}">

                                            <div>
                                                <h6 class="mb-1">{{ $item->descripcion }}</h6>
                                                <small class="text-muted">
                                                    Stock disponible: {{ $item->stock }}
                                                </small>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-2 fw-semibold">
                                        ${{ number_format($item->precio, 2) }}
                                    </div>

                                    <div class="col-3">
                                        <div class="d-flex justify-content-center align-items-center gap-1">

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad - 1 }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                    @disabled($item->cantidad <= 1)>
                                                    −
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="number"
                                                       name="cantidad"
                                                       value="{{ $item->cantidad }}"
                                                       min="1"
                                                       max="{{ $item->stock }}"
                                                       step="1"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                                       class="form-control form-control-sm text-center"
                                                       style="width:70px;"
                                                       onblur="if(this.value==''||this.value<1)this.value=1;if(this.value!=this.defaultValue)this.form.submit()"
                                                       onkeydown="if(event.key==='Enter'){event.preventDefault();if(this.value!=this.defaultValue)this.form.submit();}">
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad + 1 }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                    @disabled($item->cantidad >= $item->stock)>
                                                    +
                                                </button>
                                            </form>

                                        </div>
                                    </div>

                                    <div class="col-2 fw-bold">
                                        $<span class="js-item-subtotal">{{ number_format($item->subtotal, 2) }}</span>
                                    </div>

                                </div>

                                {{-- MÓVIL --}}
                                <div class="d-md-none">

                                    {{-- Top: imagen + info --}}
                                    <div class="d-flex gap-2 align-items-start">

                                        <a href="{{ route('productos.show', $item->token) }}"
                                           class="flex-shrink-0 text-decoration-none">
                                            <img src="{{ $item->imagen }}"
                                                 class="rounded"
                                                 style="width:64px;height:64px;object-fit:cover;"
                                                 alt="{{ $item->descripcion }}">
                                        </a>

                                        <div class="flex-grow-1">

                                            <a href="{{ route('productos.show', $item->token) }}"
                                               class="text-decoration-none text-dark d-block">
                                                <h6 class="mb-1" style="line-height:1.2;">
                                                    {{ $item->descripcion }}
                                                </h6>
                                            </a>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Stock: {{ $item->stock }}
                                                </small>
                                                <small class="fw-semibold">
                                                    ${{ number_format($item->precio, 2) }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom: eliminar + controles + subtotal --}}
                                    <div class="d-flex align-items-center justify-content-between mt-2">

                                        <form method="POST"
                                              action="{{ route('carrito.destroy', $item->id_producto) }}"
                                              class="me-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    onclick="confirmarEliminacion(this)"
                                                    aria-label="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                        <div class="d-flex align-items-center gap-1">

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad - 1 }}">
                                                <button type="submit"
                                                        class="btn btn-outline-secondary btn-sm px-2 py-1"
                                                    @disabled($item->cantidad <= 1)>−</button>
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="number"
                                                       name="cantidad"
                                                       value="{{ $item->cantidad }}"
                                                       min="1"
                                                       max="{{ $item->stock }}"
                                                       class="form-control form-control-sm text-center"
                                                       style="width:56px;"
                                                       onblur="if(this.value==''||this.value<1)this.value=1;if(this.value!=this.defaultValue)this.form.submit()"
                                                       onkeydown="if(event.key==='Enter'){event.preventDefault();if(this.value!=this.defaultValue)this.form.submit();}">
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad + 1 }}">
                                                <button type="submit"
                                                        class="btn btn-outline-secondary btn-sm px-2 py-1"
                                                    @disabled($item->cantidad >= $item->stock)>+</button>
                                            </form>

                                        </div>

                                        <div class="text-end ms-2" style="min-width:86px;">
                                            <small class="text-muted d-block" style="line-height:1;">
                                                Subtotal
                                            </small>
                                            <span class="fw-bold text-nowrap">
                                                $<span class="js-item-subtotal">{{ number_format($item->subtotal, 2) }}</span>
                                            </span>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                <div class="text-end mt-3">
                    <form method="POST" action="{{ route('carrito.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-outline-danger w-100 w-md-auto"
                                onclick="confirmarVaciado(this)">
                            <i class="bi bi-trash me-2"></i>
                            Vaciar Carrito de Compras
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Resumen del Pedido</h5>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Artículos</span>
                            <span class="fw-semibold" id="js-cart-articulos">{{ $articulos }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">
                                $<span id="js-cart-subtotal">{{ number_format($subtotal, 2) }}</span>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Impuestos (15%)</span>
                            <span class="fw-semibold">
                                $<span id="js-cart-impuestos">{{ number_format($impuestos, 2) }}</span>
                            </span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5">
                                $<span id="js-cart-total">{{ number_format($total, 2) }}</span>
                            </span>
                        </div>

                        @auth
                            <button type="button"
                                    class="btn btn-success w-100 py-3 fw-semibold"
                                    onclick="abrirModalPago()"
                                @disabled($items->isEmpty())>
                                Proceder al Pago
                            </button>
                        @endauth

                        @guest
                            <button type="button"
                                    class="btn btn-success w-100 py-3 fw-semibold"
                                    onclick="mostrarLoginModal()"
                                @disabled($items->isEmpty())>
                                Proceder al Pago
                            </button>
                        @endguest
                    </div>
                </div>
            </div>

        </div>
    </main>

    @include('carrito.modals')

    @push('scripts')
        {{-- jQuery (si no lo cargas ya en el layout) --}}
        {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

        {{-- JS animación post-login --}}
        <script src="{{ asset('assets/js/login-success.js') }}?v={{ filemtime(public_path('assets/js/login-success.js')) }}"></script>

        {{-- JS del carrito --}}
        <script src="{{ asset('assets/js/carrito.js') }}?v={{ filemtime(public_path('assets/js/carrito.js')) }}"></script>
        <script src="{{ asset('assets/js/factura-exito.js') }}?v={{ filemtime(public_path('assets/js/factura-exito.js')) }}"></script>
    @endpush
@endsection
