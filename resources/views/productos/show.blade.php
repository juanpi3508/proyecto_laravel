@extends('layouts.app')

@section('title', $producto->pro_descripcion)

@php
    use Illuminate\Support\Facades\Crypt;

    $img = $producto->pro_imagen
        ? asset($producto->pro_imagen)
        : 'https://via.placeholder.com/600x600?text=Sin+imagen';

    $precio = $producto->pro_precio_venta;
    $precioAnterior = $precio / 0.85;
@endphp

@section('content')

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none">Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('catalogo.index') }}" class="text-decoration-none">Catálogo</a>
            </li>

            @if($producto->categoria)
                <li class="breadcrumb-item">
                    <a href="{{ route('catalogo.index', ['cat' => $producto->categoria->id_categoria]) }}"
                       class="text-decoration-none">
                        {{ $producto->categoria->cat_descripcion }}
                    </a>
                </li>
            @endif

            <li class="breadcrumb-item active">{{ $producto->pro_descripcion }}</li>
        </ol>
    </nav>

    <div class="row mt-4">

        <!-- IMAGEN -->
        <div class="col-md-5 mb-4">
            <img src="{{ $img }}"
                 class="img-fluid rounded shadow-sm mb-3"
                 alt="{{ $producto->pro_descripcion }}">

            <div class="d-flex gap-2">
                @for($i = 0; $i < 4; $i++)
                    <img src="{{ $img }}" class="img-fluid rounded shadow-sm" style="width:72px;">
                @endfor
            </div>
        </div>

        <!-- INFO -->
        <div class="col-md-4">
            <h1 class="h3 fw-bold">{{ $producto->pro_descripcion }}</h1>

            <div class="mb-3">
                <span class="badge badge-discount">-15% OFF</span>
                <span class="h3 fw-bold product-price">
                ${{ number_format($precio, 2) }}
            </span>
                <div class="small text-muted">
                    Precio anterior: ${{ number_format($precioAnterior, 2) }}
                </div>
            </div>

            <div class="mb-3">
                <strong>Categoría:</strong>
                {{ $producto->categoria->cat_descripcion ?? 'Sin categoría' }}
            </div>

            <div class="mb-3">
                <p class="small">{{ $producto->pro_descripcion }}</p>
            </div>

            <div class="p-3 bg-light rounded">
                @if(($producto->pro_saldo_fin ?? 0) > 0)
                    <span class="badge-stock">En stock</span>
                @else
                    <span class="badge bg-danger">Agotado</span>
                @endif
                <p class="small mt-2 mb-0">
                    Stock: <strong>{{ $producto->pro_saldo_fin ?? 0 }}</strong>
                </p>
            </div>
        </div>

        <!-- COMPRA -->
        <div class="col-md-3">
            <div class="price-section">

                {{-- AGREGAR AL CARRITO (NO REDIRIGE) --}}
                <form method="POST" action="{{ route('carrito.store') }}">
                    @csrf
                    <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                    <input type="hidden" name="redirect" value="0">

                    <label class="form-label small fw-semibold">Cantidad:</label>
                    <select name="cantidad" class="form-select mb-3">
                        @foreach([1,2,3,4,5,10] as $cant)
                            <option value="{{ $cant }}">{{ $cant }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-add-cart w-100 mb-2 py-2">
                        <i class="bi bi-cart-plus me-1"></i>
                        Agregar al carrito
                    </button>
                </form>

                {{-- COMPRAR AHORA (SÍ REDIRIGE) --}}
                <form method="POST" action="{{ route('carrito.store') }}">
                    @csrf
                    <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                    <input type="hidden" name="cantidad" value="1">
                    <input type="hidden" name="redirect" value="1">

                    <button type="submit" class="btn btn-buy-now w-100 py-2 mb-3">
                        <i class="bi bi-lightning-fill me-1"></i>
                        Comprar ahora
                    </button>
                </form>

                <div class="small text-muted">
                    <p class="mb-1">🔒 Transacción segura</p>
                    <p class="mb-1">📦 Envío KoKo Market</p>
                    <p class="mb-0">🔄 Devolución 15 días</p>
                </div>
            </div>
        </div>
    </div>

@endsection
