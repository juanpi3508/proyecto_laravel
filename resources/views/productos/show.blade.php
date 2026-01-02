@extends('layouts.app')

@section('title', 'KoKo Market | ' . $producto->pro_descripcion)

@php
    use Illuminate\Support\Facades\Crypt;

    /**
     * Convierte el path guardado en BD (ej: productos/img.png)
     * en URL pública /storage/productos/img.png
     */
    function productImageUrl($path) {
        if (!$path) return 'https://via.placeholder.com/600x600?text=Sin+imagen';

        $path = trim($path);

        // si ya es URL completa
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        return asset('storage/' . $path);
    }

    // Imagen principal
    $img = productImageUrl($producto->pro_imagen);

    // Precio y descuento (misma lógica tuya)
    $precio = $producto->pro_precio_venta ?? 0;
    $precioAnterior = $precio > 0 ? $precio / 0.85 : 0;

    // Categoría
    $categoriaNombre = $producto->categoria->cat_descripcion ?? 'Sin categoría';

    // Stock
    $stock = $producto->pro_saldo_fin ?? 0;
@endphp

@section('content')

    <!-- BREADCRUMB -->
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

                <li class="breadcrumb-item active" aria-current="page">
                    {{ $producto->pro_descripcion }}
                </li>
            </ol>
        </nav>
    </div>

    <!-- SECCIÓN PRINCIPAL DEL PRODUCTO -->
    <main class="container my-4">
        <div class="row">

            <!-- COLUMNA DE IMÁGENES -->
            <div class="col-md-5 mb-4">
                <div class="mb-3">
                    <img id="mainImage"
                         src="{{ $img }}"
                         class="product-image-main"
                         alt="{{ $producto->pro_descripcion }}">
                </div>

                <div class="d-flex gap-2 flex-wrap" id="thumbnailsContainer">
                    {{-- Miniaturas (por ahora repetimos la misma imagen principal) --}}
                    @for($i = 0; $i < 4; $i++)
                        <img src="{{ $img }}"
                             class="product-thumbnail {{ $i == 0 ? 'active' : '' }}"
                             alt="Miniatura {{ $i+1 }}">
                    @endfor
                </div>
            </div>

            <!-- COLUMNA DE INFORMACIÓN -->
            <div class="col-md-4 mb-4">
                <h1 class="h3 mb-2 fw-bold" id="productName">
                    {{ $producto->pro_descripcion }}
                </h1>

                <!-- Rating y reseñas (fake como tu demo) -->
                <div class="d-flex align-items-center mb-3">
                    <div class="star-rating me-2">★★★★☆</div>
                    <span class="fw-bold me-2">4.5</span>
                    <span class="small text-muted">(287 calificaciones)</span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small">Categoría: </span>
                    <span class="fw-semibold" id="productCategory">
                    {{ $categoriaNombre }}
                </span>
                </div>

                <hr>

                <!-- Precio -->
                <div class="mb-3">
                    <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                        <span class="badge badge-discount">-15% OFF</span>
                        <span class="h3 mb-0 fw-bold product-price" id="productPrice">
                        ${{ number_format($precio, 2) }}
                    </span>
                    </div>
                    <div class="text-muted small">
                    <span class="price-old" id="productPriceOld">
                        Precio anterior: ${{ number_format($precioAnterior, 2) }}
                    </span>
                    </div>
                </div>

                <!-- Descripción breve -->
                <div class="mb-3">
                    <h5 class="h6 fw-bold">Descripción del producto:</h5>
                    <p class="small" id="productDescription">
                        {{ $producto->pro_descripcion }}
                    </p>
                </div>

                <!-- Disponibilidad -->
                <div class="mb-3 p-3 bg-light rounded">
                    @if($stock > 0)
                        <p class="mb-2"><span class="badge-stock">En Stock</span></p>
                    @else
                        <p class="mb-2"><span class="badge bg-danger">Agotado</span></p>
                    @endif

                    <p class="small mb-1">📦 <strong>Envío GRATIS</strong> en compras mayores a $20</p>
                    <p class="small mb-0 text-muted">📍 Disponible en <strong>Quito, Pichincha</strong></p>

                    <p class="small mt-2 mb-0">
                        Stock actual: <strong>{{ $stock }}</strong>
                    </p>
                </div>
            </div>

            <!-- COLUMNA DE COMPRA -->
            <div class="col-md-3">
                <div class="price-section sticky-top" style="top: 80px;">
                    <div class="h4 fw-bold mb-3 product-price" id="productPriceSidebar">
                        ${{ number_format($precio, 2) }}
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <p class="small mb-1 fw-semibold">🚚 Envío GRATIS</p>
                        <p class="small text-muted mb-2">En compras mayores a $20</p>
                        <p class="small mb-0 text-muted">
                            📍 Disponible en <strong>Quito</strong>
                        </p>
                    </div>

                    <!-- FORM LARAVEL - Agregar al carrito -->
                    <form method="POST" action="{{ route('carrito.store') }}">
                        @csrf

                        <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">

                        <div class="mb-3">
                            @if($stock > 0)
                                <p class="mb-2"><span class="badge-stock">Disponible</span></p>
                            @else
                                <p class="mb-2"><span class="badge bg-danger">Agotado</span></p>
                            @endif

                            <label for="quantity" class="form-label small fw-semibold">Cantidad:</label>
                            <select id="quantity" name="cantidad" class="form-select mb-3" {{ $stock <= 0 ? 'disabled' : '' }}>
                                @foreach([1,2,3,4,5,10] as $cant)
                                    <option value="{{ $cant }}">{{ $cant }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                                class="btn btn-add-cart w-100 mb-2 py-2"
                            {{ $stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-1"></i>
                            Agregar al carrito
                        </button>
                    </form>

                    <a href="{{ route('carrito.index') }}" class="btn btn-buy-now w-100 py-2 mb-3">
                        <i class="bi bi-lightning-fill me-1"></i>
                        Comprar ahora
                    </a>

                    <div class="small text-muted">
                        <p class="mb-2">🔒 Transacción segura</p>
                        <p class="mb-2">📦 Envío por KoKo Market</p>
                        <p class="mb-0">🔄 Devolución gratis 15 días</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- PRODUCTOS RELACIONADOS -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4 fw-bold product-section-title">Productos relacionados</h4>

                <div class="row row-cols-2 row-cols-md-4 g-3" id="relatedProductsContainer">

                    @forelse($relacionados as $rel)
                        @php
                            $tokenRel = Crypt::encryptString($rel->id_producto);
                            $imgRel = productImageUrl($rel->pro_imagen);
                        @endphp

                        <div class="col">
                            <div class="card related-product h-100 shadow-sm">

                                <a href="{{ route('productos.show', $tokenRel) }}" class="text-decoration-none text-reset">
                                    <img src="{{ $imgRel }}"
                                         class="card-img-top"
                                         alt="{{ $rel->pro_descripcion }}">
                                </a>

                                <div class="card-body">
                                    <h6 class="card-title small mb-1 fw-semibold">
                                        <a href="{{ route('productos.show', $tokenRel) }}"
                                           class="text-reset text-decoration-none">
                                            {{ $rel->pro_descripcion }}
                                        </a>
                                    </h6>

                                    <p class="text-muted small mb-2">
                                        {{ $rel->categoria->cat_descripcion ?? '' }}
                                    </p>

                                    <p class="fw-bold mb-0 product-price">
                                        ${{ number_format($rel->pro_precio_venta, 2) }}
                                    </p>
                                </div>

                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No hay productos relacionados.</p>
                    @endforelse

                </div>
            </div>
        </div>
    </main>

@endsection
