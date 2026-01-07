@extends('layouts.app')

@section('title', 'KoKo Market | ' . $producto->pro_descripcion)

@section('content')

    {{-- MENSAJES --}}
    @if(session('mensaje_stock'))
        <div class="container mt-3">
            <div class="alert alert-warning">
                <strong>⚠ Atención:</strong> {{ session('mensaje_stock') }}
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif

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
                            {{ $producto->categoria_nombre }}
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

            {{-- IMÁGENES --}}
            <div class="col-md-5 mb-4">
                <img src="{{ $producto->image_url }}" class="product-image-main mb-3" alt="{{ $producto->pro_descripcion }}">
            </div>

            {{-- INFO --}}
            <div class="col-md-4 mb-4">
                <h1 class="h3 fw-bold">{{ $producto->pro_descripcion }}</h1>

                <div class="mb-2">
                    <span class="fw-semibold">Categoría:</span> {{ $producto->categoria_nombre }}
                </div>

                <div class="mb-3">
                    <span class="h3 fw-bold product-price">${{ number_format($producto->precio,2) }}</span>
                    <div class="small text-muted">
                        Precio anterior: ${{ number_format($producto->precio_anterior,2) }}
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Descripción:</h6>
                    <p class="small">{{ $producto->pro_descripcion }}</p>
                </div>

                <div class="p-3">
                    @if($producto->stock > 0)
                        <span class="badge-stock">En stock ({{ $producto->stock }})</span>
                    @else
                        <span class="badge bg-danger">Agotado</span>
                    @endif
                </div>
            </div>

            {{-- COMPRA --}}
            <div class="col-md-3">
                <div class="sticky-top" style="top:80px">

                    <div class="h4 fw-bold mb-3">${{ number_format($producto->precio,2) }}</div>

                    <form method="POST"
                          action="{{ route('carrito.store') }}"
                          id="formAgregarCarrito">
                        @csrf

                        <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">

                        <label class="form-label small fw-semibold">Cantidad</label>
                        <select name="cantidad"
                                class="form-select mb-3"
                            {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                            @foreach([1,2,3,4,5,10] as $cant)
                                @if($cant <= $producto->stock)
                                    <option value="{{ $cant }}">{{ $cant }}</option>
                                @endif
                            @endforeach
                        </select>

                        {{-- AGREGAR --}}
                        <button type="submit"
                                class="btn btn-add-cart w-100 mb-2"
                            {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-1"></i>
                            Agregar al carrito
                        </button>

                        {{-- COMPRAR AHORA --}}
                        <button type="submit"
                                name="redirect"
                                value="1"
                                class="btn btn-buy-now w-100"
                            {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-lightning-fill me-1"></i>
                            Comprar ahora
                        </button>
                    </form>

                </div>
            </div>

            {{-- RELACIONADOS --}}
            <div class="row mt-5">
                <div class="col-12">
                    <h4 class="mb-4 fw-bold product-section-title">Productos relacionados</h4>

                    <div class="row row-cols-2 row-cols-md-4 g-3">

                        @forelse($relacionados as $rel)

                            <div class="col">
                                <div class="card related-product h-100 shadow-sm">
                                    <a href="{{ route('productos.show', $rel->token) }}"
                                       class="text-decoration-none text-reset">
                                        <img src="{{ $rel->image_url }}" class="card-img-top related-product-img">
                                    </a>

                                    <div class="card-body">
                                        <h6 class="small fw-semibold mb-1">
                                            <a href="{{ route('productos.show', $rel->token) }}"
                                               class="text-decoration-none text-reset">
                                                {{ $rel->pro_descripcion }}
                                            </a>
                                        </h6>

                                        <p class="text-muted small mb-2">
                                            {{ $rel->categoria_nombre }}
                                        </p>

                                        <p class="fw-bold mb-0 product-price">
                                            ${{ number_format($rel->precio,2) }}
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

        </div>
    </main>

@endsection
