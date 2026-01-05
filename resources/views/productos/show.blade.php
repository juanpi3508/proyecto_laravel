@extends('layouts.app')

@section('title', 'KoKo Market | ' . $producto->pro_descripcion)

@php
    use Illuminate\Support\Facades\Crypt;

    function productImageUrl($path) {
        if (!$path) return 'https://via.placeholder.com/600x600?text=Sin+imagen';

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        return asset('storage/' . $path);
    }

    $img = productImageUrl($producto->pro_imagen);
    $precio = $producto->pro_precio_venta ?? 0;
    $precioAnterior = $precio > 0 ? $precio / 0.85 : 0;
    $categoriaNombre = $producto->categoria->cat_descripcion ?? 'Sin categoría';
    $stock = $producto->pro_saldo_fin ?? 0;
@endphp

@section('content')

    <div class="container mt-3">
        <nav aria-label="breadcrumb">
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
                            {{ $categoriaNombre }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active">
                    {{ $producto->pro_descripcion }}
                </li>
            </ol>
        </nav>
    </div>

    <main class="container my-4">
        <div class="row">

            <!-- IMÁGENES -->
            <div class="col-md-5 mb-4">
                <img src="{{ $img }}" class="product-image-main mb-3" alt="{{ $producto->pro_descripcion }}">
                <div class="d-flex gap-2">
                    @for($i=0;$i<4;$i++)
                        <img src="{{ $img }}" class="product-thumbnail {{ $i==0?'active':'' }}">
                    @endfor
                </div>
            </div>

            <!-- INFO -->
            <div class="col-md-4 mb-4">
                <h1 class="h3 fw-bold">{{ $producto->pro_descripcion }}</h1>

                <div class="mb-2">
                    <span class="fw-semibold">Categoría:</span> {{ $categoriaNombre }}
                </div>

                <div class="mb-3">
                    <span class="h3 fw-bold product-price">${{ number_format($precio,2) }}</span>
                    <div class="small text-muted">
                        Precio anterior: ${{ number_format($precioAnterior,2) }}
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Descripción:</h6>
                    <p class="small">{{ $producto->pro_descripcion }}</p>
                </div>

                <div class="p-3 bg-light rounded">
                    @if($stock > 0)
                        <span class="badge-stock">En stock ({{ $stock }})</span>
                    @else
                        <span class="badge bg-danger">Agotado</span>
                    @endif
                </div>
            </div>

            <!-- COMPRA -->
            <div class="col-md-3">
                <div class="sticky-top" style="top:80px">

                    <div class="h4 fw-bold mb-3">${{ number_format($precio,2) }}</div>

                    <form method="POST"
                          action="{{ route('carrito.store') }}"
                          id="formAgregarCarrito">
                        @csrf

                        <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">

                        <label class="form-label small fw-semibold">Cantidad</label>
                        <select name="cantidad"
                                id="quantity"
                                class="form-select mb-3"
                            {{ $stock <= 0 ? 'disabled' : '' }}>
                            @foreach([1,2,3,4,5,10] as $cant)
                                <option value="{{ $cant }}">{{ $cant }}</option>
                            @endforeach
                        </select>

                        <!-- AGREGAR -->
                        <button type="submit"
                                class="btn btn-add-cart w-100 mb-2"
                            {{ $stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-1"></i>
                            Agregar al carrito
                        </button>

                        <!-- COMPRAR AHORA -->
                        <button type="button"
                                class="btn btn-buy-now w-100"
                                onclick="comprarAhora()"
                            {{ $stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-lightning-fill me-1"></i>
                            Comprar ahora
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </main>

    <script>
        function comprarAhora() {
            const form = document.getElementById('formAgregarCarrito');
            form.submit();

            setTimeout(() => {
                window.location.href = "{{ route('carrito.index') }}";
            }, 300);
        }
    </script>

@endsection
