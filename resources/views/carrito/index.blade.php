@extends('layouts.app')

@section('title', 'KoKo Market | Carrito de Compras')

@section('content')
    <main class="container my-5">
        <div class="row">

            {{-- IZQUIERDA --}}
            <div class="col-lg-8 mb-4">
                <h2 class="mb-4">Carrito de Compras</h2>

                @if(session('mensaje_stock'))
                    <div class="mb-3">
                        <strong>⚠ Atención:</strong>
                        {{ session('mensaje_stock') }}
                    </div>
                @endif

                {{-- Encabezado --}}
                <div class="card mb-3" style="background-color:#f6e6a5;">
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
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center text-center">

                                    {{-- Producto --}}
                                    <div class="col-5 d-flex align-items-center text-start">
                                        <form method="POST"
                                              action="{{ route('carrito.destroy', $item->id_producto) }}"
                                              class="me-2">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-link text-danger p-0">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>

                                        <img src="{{ asset($item->imagen) }}"
                                             class="rounded me-3"
                                             style="width:60px;height:60px;object-fit:cover;">

                                        <div>
                                            <h6 class="mb-1">{{ $item->descripcion }}</h6>
                                            <small class="text-muted">
                                                Stock disponible: {{ $item->stock }}
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Precio --}}
                                    <div class="col-2 fw-semibold">
                                        ${{ number_format($item->precio, 2) }}
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="col-3">
                                        <div class="d-flex justify-content-center align-items-center gap-1">

                                            {{-- − --}}
                                            <form method="POST"
                                                  action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="cantidad"
                                                       value="{{ $item->cantidad - 1 }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                    @disabled($item->cantidad <= 1)>
                                                    −
                                                </button>
                                            </form>

                                            {{-- Input manual (CAMBIO CORRECTO AQUÍ) --}}
                                            <form method="POST"
                                                  action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="number"
                                                       name="cantidad"
                                                       value="{{ $item->cantidad }}"
                                                       min="1"
                                                       max="{{ $item->stock }}"
                                                       class="form-control form-control-sm text-center"
                                                       style="width:70px;"
                                                       onblur="if(this.value != this.defaultValue) this.form.submit()"
                                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); if(this.value != this.defaultValue) this.form.submit(); }">

                                            </form>

                                            {{-- + --}}
                                            <form method="POST"
                                                  action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="cantidad"
                                                       value="{{ $item->cantidad + 1 }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary"
                                                    @disabled($item->cantidad >= $item->stock)>
                                                    +
                                                </button>
                                            </form>

                                        </div>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="col-2 fw-bold">
                                        ${{ number_format($item->subtotal, 2) }}
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Vaciar --}}
                <div class="text-end mt-3">
                    <form method="POST" action="{{ route('carrito.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i>
                            Vaciar Carrito de Compras
                        </button>
                    </form>
                </div>
            </div>

            {{-- DERECHA --}}
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Resumen del Pedido</h5>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Artículos</span>
                            <span class="fw-semibold">{{ $items->sum('cantidad') }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">${{ number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Impuestos (12%)</span>
                            <span class="fw-semibold">${{ number_format($subtotal * 0.12, 2) }}</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5">${{ number_format($total, 2) }}</span>
                        </div>

                        <button class="btn btn-success w-100 py-3 fw-semibold">
                            Proceder al Pago
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection
