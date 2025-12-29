@extends('layouts.app')

@section('title', $producto->pro_descripcion)

@section('content')

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/" class="text-decoration-none">Inicio</a>
            </li>

            <li class="breadcrumb-item">
                <a href="/catalogo" class="text-decoration-none">Catálogo</a>
            </li>

            <li class="breadcrumb-item">
                <a href="/categorias/{{ $producto->categoria->id_categoria }}" class="text-decoration-none">
                    {{ $producto->categoria->cat_descripcion }}
                </a>
            </li>

            <li class="breadcrumb-item active" aria-current="page">
                {{ $producto->pro_descripcion }}
            </li>
        </ol>
    </nav>

    <div class="row mt-4">

        <!-- IMAGEN PRINCIPAL + MINIATURAS -->
        <div class="col-md-5 mb-4">

            <!-- Imagen principal -->
            <div class="mb-3">
                <img src="{{ asset($producto->imagen) }}"
                     class="product-image-main img-fluid rounded shadow-sm"
                     alt="{{ $producto->pro_descripcion }}">
            </div>

            <!-- Miniaturas (sin JS, solo repito la misma imagen) -->
            <div class="d-flex gap-2 flex-wrap">
                @for($i = 0; $i < 4; $i++)
                    <div class="col-3">
                        <img src="{{ asset($producto->imagen) }}"
                             class="img-fluid rounded shadow-sm"
                             style="cursor:pointer;">
                    </div>
                @endfor
            </div>
        </div>

        <!-- INFORMACIÓN DEL PRODUCTO -->
        <div class="col-md-4">

            <h1 class="h3 fw-bold">{{ $producto->pro_descripcion }}</h1>

            <!-- Rating fake (como tu HTML original) -->
            <div class="d-flex align-items-center mb-3">
                <div class="star-rating me-2">★★★★☆</div>
                <span class="fw-bold me-2">4.5</span>
                <span class="small text-muted">(287 calificaciones)</span>
            </div>

            <div class="mb-3">
                <span class="text-muted small">Categoría:</span>
                <span class="fw-semibold">{{ $producto->categoria->nombre }}</span>
            </div>

            <hr>

            <!-- Precio -->
            @php
                $precio = $producto->pro_precio_venta;
                $precioAnterior = $precio / 0.85;
            @endphp

            <div class="mb-3">
                <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                    <span class="badge badge-discount">-15% OFF</span>
                    <span class="h3 mb-0 fw-bold product-price">
                    ${{ number_format($precio, 2) }}
                </span>
                </div>
                <div class="text-muted small">
                    <span class="price-old">Precio anterior: ${{ number_format($precioAnterior, 2) }}</span>
                </div>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <h5 class="h6 fw-bold">Descripción del producto:</h5>
                <p class="small">{{ $producto->pro_descripcion }}</p>
            </div>

            <!-- Stock -->
            <div class="mb-3 p-3 bg-light rounded">
                @if($producto->pro_saldo_final > 0)
                    <p class="mb-2"><span class="badge-stock">En Stock</span></p>
                @else
                    <p class="mb-2"><span class="badge bg-danger">Agotado</span></p>
                @endif

                <p class="small mb-0">
                    📦 Stock actual: <strong>{{ $producto->pro_saldo_final }}</strong>
                </p>
            </div>

        </div>

        <!-- COLUMNA DE COMPRA -->
        <div class="col-md-3">
            <div class="price-section" style="top: 80px;">

                <div class="h4 fw-bold mb-3 product-price">
                    ${{ number_format($precio, 2) }}
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <p class="small mb-1 fw-semibold">🚚 Envío GRATIS</p>
                    <p class="small text-muted mb-2">En compras mayores a $20</p>
                    <p class="small mb-0 text-muted">
                        📍 Disponible en <strong>Quito</strong>
                    </p>
                </div>

                <div class="mb-3">
                    <p class="mb-2"><span class="badge-stock">Disponible</span></p>

                    <label class="form-label small fw-semibold">Cantidad:</label>
                    <select class="form-select mb-3">
                        @foreach([1,2,3,4,5,10] as $cant)
                            <option>{{ $cant }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn btn-add-cart w-100 mb-2 py-2">
                    <i class="bi bi-cart-plus me-1"></i>
                    Agregar al carrito
                </button>

                <button class="btn btn-buy-now w-100 py-2 mb-3">
                    <i class="bi bi-lightning-fill me-1"></i>
                    Comprar ahora
                </button>

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

            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach($relacionados as $rel)
                    <div class="col">
                        <div class="card h-100 shadow-sm">

                            <a href="/productos/{{ $rel->id_producto }}" class="text-decoration-none text-reset">
                                <img src="{{ asset($rel->imagen) }}"
                                     class="card-img-top"
                                     style="height:180px; object-fit:cover;"
                                     alt="{{ $rel->pro_descripcion }}">
                            </a>

                            <div class="card-body">
                                <h6 class="card-title small mb-1 fw-semibold">
                                    <a href="/productos/{{ $rel->id_producto }}" class="text-reset">
                                        {{ $rel->pro_descripcion }}
                                    </a>
                                </h6>

                                <p class="text-muted small mb-2">{{ $rel->categoria->nombre }}</p>

                                <p class="fw-bold mb-0 product-price">
                                    ${{ number_format($rel->pro_precio_venta, 2) }}
                                </p>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
