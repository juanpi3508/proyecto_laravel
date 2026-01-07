@extends('layouts.app')

@section('title', 'KoKo Market | Inicio - Tu Súper de Barrio')

@section('content')

    <!-- SECCIÓN PRINCIPAL -->
    <header class="py-5 fade-in">
        <div class="container">
            <div class="row align-items-center g-4">

                <!-- Texto principal -->
                <div class="col-lg-6">
                    <div class="mb-3">
                        <span class="badge-cultural">🇪🇨 100% Ecuatoriano</span>
                    </div>

                    <h1 class="display-5 fw-bold mb-3">
                        Todo lo que necesitas, a tu manera
                    </h1>

                    <p class="lead mb-4">
                        Productos frescos, snacks de moda y todo lo esencial.
                        <strong>Tu súper de barrio, ahora más cerca.</strong>
                    </p>

                    <a href="{{ route('catalogo.index') }}" class="btn btn-primary btn-lg">
                        Explorar Productos
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>

                <!-- Imagen -->
                <div class="col-lg-6">
                    <div class="img-wrapper" style="height: 400px; overflow: hidden;">
                        <img
                            src="https://images.unsplash.com/photo-1542838132-92c53300491e"
                            alt="Productos frescos en KoKo Market"
                            style="width:100%; height:100%; object-fit:cover;"
                            loading="lazy"
                        >
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- NÚMEROS IMPORTANTES -->
    <section class="py-4 bg-white border-bottom fade-in">
        <div class="container">
            <div class="row row-cols-2 row-cols-md-4 g-3 text-center">

                @foreach($stats as [$icon, $value, $label])
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <span class="stat-icon">{{ $icon }}</span>
                                <h3 class="h2 mb-0">{{ $value }}</h3>
                                <p class="text-muted small mb-0">{{ $label }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    <!-- LO MÁS VENDIDO -->
    <section class="py-5 fade-in">
        <div class="container">

            <div class="text-center mb-4">
                <h2 class="mb-2">Lo Más Vendido</h2>
                <div class="decorative-line"></div>
                <p class="text-muted">Los favoritos de nuestros clientes</p>
            </div>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3 justify-content-center text-center">

                @forelse($masVendidos as $producto)

                    <div class="col d-flex justify-content-center">

                        <a href="{{ route('productos.show', $producto->token) }}"
                           class="text-decoration-none text-dark w-100">

                            <div class="card h-100 product-card shadow-sm">

                                <img
                                    src="{{ $producto->image_url }}"
                                    class="card-img-top mx-auto"
                                    alt="{{ $producto->pro_descripcion }}"
                                    loading="lazy"
                                >

                                <div class="card-body">
                                    <h6 class="card-title mb-1">
                                        {{ $producto->pro_descripcion }}
                                    </h6>

                                    <p class="fw-bold mb-1">
                                        ${{ number_format($producto->precio, 2) }}
                                    </p>

                                    <small class="text-muted">
                                        Vendidos: {{ $producto->total_vendido }}
                                    </small>
                                </div>

                            </div>

                        </a>

                    </div>
                @empty
                    <div class="col-12 text-muted text-center">
                        Aún no existen productos vendidos.
                    </div>
                @endforelse

            </div>

            <div class="text-center mt-4">
                <a href="{{ route('catalogo.index') }}" class="product-link fw-bold">
                    Ver todos los productos →
                </a>
            </div>

        </div>
    </section>

    <!-- SOBRE NOSOTROS -->
    <section class="py-5 about-section fade-in">
        <div class="container">
            <div class="row align-items-center g-4">

                <div class="col-lg-5">
                    <div class="img-wrapper" style="height: 450px; overflow: hidden;">
                        <img
                            src="https://media.istockphoto.com/id/1223013236/es/foto/vendedor-de-frutas-en-el-mercado-de-otavalo-ecuador.jpg?s=612x612&w=0&k=20&c=bP72WZXp12pOvZUXFF2dGzhZ8VFOGuZP9pJU8VlvNaM="
                            alt="Mercado tradicional de Quito"
                            style="width:100%; height:100%; object-fit:cover;"
                            loading="lazy"
                        >
                    </div>
                </div>

                <div class="col-lg-7">
                    <span class="badge-cultural">🏔️ Tradición Quiteña</span>

                    <h2 class="mt-3 mb-4">Sobre Nosotros</h2>
                    <div class="decorative-line" style="margin-left:0;"></div>

                    <p class="mb-3">
                        KoKo Market surge de una profunda herencia familiar inspirada
                        en los mercados tradicionales de Quito, transmitida de generación en generación.
                    </p>

                    <p class="mb-4">
                        Hoy, KoKo Market honra ese legado ofreciendo productos frescos,
                        precios justos y una experiencia cercana, como el mercado de barrio.
                    </p>

                    <div class="stats-wrapper d-flex gap-4">
                        <div>
                            <h4>2025</h4>
                            <p class="text-muted small mb-0">Fundación</p>
                        </div>
                        <div>
                            <h4>1+</h4>
                            <p class="text-muted small mb-0">Semana de experiencia</p>
                        </div>
                        <div>
                            <h4>3+</h4>
                            <p class="text-muted small mb-0">Clientes satisfechos</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- POR QUÉ KOKO MARKET -->
    <section class="py-5 bg-white fade-in">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="mb-2">¿Por qué KoKo Market?</h2>
                <div class="decorative-line"></div>
                <p class="text-muted">Tu aliado de confianza para el día a día</p>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">

                <div class="col">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">🚀</div>
                            <h5 class="card-title">Entrega Rápida</h5>
                            <p class="text-muted">
                                Tu pedido en <strong>30 minutos</strong> o es gratis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">🌱</div>
                            <h5 class="card-title">Productos Frescos</h5>
                            <p class="text-muted">
                                Directo de productores locales, garantizando frescura.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="feature-icon">💰</div>
                            <h5 class="card-title">Precios Justos</h5>
                            <p class="text-muted">
                                Calidad sin sobreprecios, con total transparencia.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
