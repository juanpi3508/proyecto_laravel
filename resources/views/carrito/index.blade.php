@extends('layouts.app')

@section('title', 'KoKo Market | Carrito de Compras')

@php
    use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
    <main class="container pt-5 mt-5 mb-5">

        <div class="row">

            <div class="col-lg-8 mb-4">
                <h2 class="mb-4">Carrito de Compras</h2>

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
                        <strong>⚠ Error de stock:</strong> {{ session('mensaje_stock') }}
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
                        @php
                            $token = Crypt::encryptString($item->id_producto);
                        @endphp

                        <div class="card mb-3">
                            <div class="card-body">
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

                                        <a href="{{ route('productos.show', $token) }}"
                                           class="d-flex align-items-center text-decoration-none text-dark">

                                            <img src="{{ Storage::url($item->imagen) }}"
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
                                        ${{ number_format($item->subtotal, 2) }}
                                    </div>

                                </div>
                                <div class="d-md-none">

                                    <div class="d-flex align-items-center gap-1 w-100 overflow-hidden">

                                        {{-- ELIMINAR --}}
                                        <button type="button"
                                                class="btn btn-link text-danger p-0 lh-1"
                                                onclick="confirmarEliminacion(this)">
                                            <i class="bi bi-x-lg" style="font-size:0.85rem;"></i>
                                        </button>

                                        {{-- IMAGEN --}}
                                        <img src="{{ Storage::url($item->imagen) }}"
                                             class="rounded"
                                             style="width:36px;height:36px;object-fit:cover;flex-shrink:0;">

                                        {{-- DESCRIPCIÓN (ULTRA COMPACTA) --}}
                                        <div class="flex-grow-1 overflow-hidden" style="line-height:1.05;">
                                            <small class="text-truncate d-block">
                                                <small>{{ $item->descripcion }}</small>
                                            </small>
                                            <small class="text-muted d-block">
                                                <small>${{ number_format($item->precio, 2) }}</small>
                                            </small>
                                        </div>

                                        {{-- CANTIDAD --}}
                                        <div class="d-flex align-items-center gap-1 flex-shrink-0 lh-1">

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad - 1 }}">
                                                <button type="submit"
                                                        class="btn btn-outline-secondary btn-sm px-1 py-0"
                                                    @disabled($item->cantidad <= 1)>−</button>
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="number"
                                                       name="cantidad"
                                                       value="{{ $item->cantidad }}"
                                                       min="1"
                                                       max="{{ $item->stock }}"
                                                       class="form-control form-control-sm text-center py-0"
                                                       style="width:40px;"
                                                       onblur="if(this.value!=this.defaultValue)this.form.submit()">
                                            </form>

                                            <form method="POST" action="{{ route('carrito.update', $item->id_producto) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="cantidad" value="{{ $item->cantidad + 1 }}">
                                                <button type="submit"
                                                        class="btn btn-outline-secondary btn-sm px-1 py-0"
                                                    @disabled($item->cantidad >= $item->stock)>+</button>
                                            </form>

                                        </div>

                                        {{-- SUBTOTAL --}}
                                        <small class="fw-bold text-nowrap flex-shrink-0">
                                            ${{ number_format($item->subtotal, 2) }}
                                        </small>

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

                        <form method="POST" action="{{ route('factura.generar') }}">
                            @csrf
                            <button class="btn btn-success w-100 py-3 fw-semibold"
                                @disabled($items->isEmpty())>
                                Proceder al Pago
                            </button>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </main>

    @if(session('factura_confirmada'))
        <div class="modal fade"
             id="modalConfirmacion"
             tabindex="-1"
             data-bs-backdrop="static"
             data-bs-keyboard="false">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Pasarela de Pago</h5>
                    </div>

                    <div class="modal-body text-center">
                        <p class="mb-2">Tu compra se realizó correctamente.</p>
                        <p class="fw-bold">
                            Factura Nº {{ session('id_factura') }}
                        </p>
                    </div>

                    <div class="modal-footer">
                        <form method="POST"
                              action="{{ route('factura.aprobar', session('id_factura')) }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                Aceptar
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                </div>
                <div class="modal-body text-center">
                    ¿Estás seguro de eliminar este producto del carrito?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-danger" id="btnConfirmDelete">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmClearModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vaciar carrito</h5>
                </div>
                <div class="modal-body text-center">
                    ¿Estás seguro de vaciar todo el carrito de compras?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-danger" id="btnConfirmClear">
                        Vaciar carrito
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let formAEliminar = null;
        let formAVaciar = null;

        function confirmarEliminacion(btn) {
            formAEliminar = btn.closest('form');
            new bootstrap.Modal(
                document.getElementById('confirmDeleteModal')
            ).show();
        }

        function confirmarVaciado(btn) {
            formAVaciar = btn.closest('form');
            new bootstrap.Modal(
                document.getElementById('confirmClearModal')
            ).show();
        }

        document.getElementById('btnConfirmDelete')
            .addEventListener('click', function () {
                if (formAEliminar) {
                    formAEliminar.submit();
                }
            });

        document.getElementById('btnConfirmClear')
            .addEventListener('click', function () {
                if (formAVaciar) {
                    formAVaciar.submit();
                }
            });

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('factura_confirmada'))
            const modalElement = document.getElementById('modalConfirmacion');
            if (modalElement) {
                new bootstrap.Modal(modalElement).show();
            }
            @endif
        });
    </script>

@endsection
