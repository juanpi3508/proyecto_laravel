@extends('layouts.app')

@section('title', 'KoKo Market | Catálogo')

@php
    use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')

    <!-- HEADER -->
    <header class="bg-light py-5 text-center border-bottom mb-5">
        <div class="container">
            <h1 class="display-5 fw-bold mb-2">Bienvenido a KoKo Market</h1>
            <p class="lead text-muted mb-0">
                Encuentra los mejores productos al mejor precio
            </p>
        </div>
    </header>

    <!-- CATÁLOGO -->
    <main class="container mb-5">

        <!-- FILTROS -->
        <form method="GET"
              action="{{ route('catalogo.index') }}"
              id="filtersForm"
              class="d-flex flex-column flex-md-row gap-3 align-items-md-end align-items-stretch mb-4">

            <!-- BUSCAR -->
            <div class="flex-grow-1">
                <label class="form-label mb-1">Buscar</label>
                <input type="search"
                       id="q"
                       name="q"
                       value="{{ old('q', $q) }}"
                       class="form-control"
                       placeholder="Nombre, descripción, marca…">
            </div>

            <!-- CATEGORÍA -->
            <div style="min-width:220px">
                <label class="form-label mb-1">Categoría</label>
                <select name="cat" id="cat" class="form-select">
                    <option value="">Todas</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id_categoria }}"
                            @selected($cat == $c->id_categoria)>
                            {{ $c->cat_descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ORDEN -->
            <div style="min-width:220px">
                <label class="form-label mb-1">Ordenar por</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="relevance" @selected($sort=='relevance')>Relevancia</option>
                    <option value="price-asc"  @selected($sort=='price-asc')>Precio ↑</option>
                    <option value="price-desc" @selected($sort=='price-desc')>Precio ↓</option>
                    <option value="name-asc"   @selected($sort=='name-asc')>Nombre A–Z</option>
                    <option value="name-desc"  @selected($sort=='name-desc')>Nombre Z–A</option>
                </select>
            </div>
        </form>

        <!-- GRID DE PRODUCTOS -->
        @if($productos->count())

            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-4">

                @foreach($productos as $p)
                    @php
                        $token = Crypt::encryptString($p->id_producto);
                    @endphp

                    <div class="col">
                        <a href="{{ route('productos.show', $token) }}"
                           class="card h-100 shadow-sm border-0 text-decoration-none text-dark"
                           style="transition: transform .15s ease-in-out;"
                           onmouseover="this.style.transform='translateY(-2px)'"
                           onmouseout="this.style.transform='translateY(0)'">

                            <!-- IMAGEN -->
                            <img src="{{ $p->pro_imagen
                                        ? Storage::url($p->pro_imagen)
                                        : 'https://via.placeholder.com/600x600?text=Sin+imagen' }}"
                                 class="card-img-top"
                                 style="height:220px; object-fit:cover"
                                 alt="{{ $p->pro_descripcion }}"
                                 loading="lazy">

                            <div class="card-body d-flex flex-column">

                                <!-- NOMBRE -->
                                <h6 class="fw-semibold mb-1">
                                    {{ $p->pro_descripcion }}
                                </h6>

                                <!-- CATEGORÍA -->
                                @if($p->categoria)
                                    <p class="text-muted small mb-2">
                                        {{ $p->categoria->cat_descripcion }}
                                    </p>
                                @endif

                                <!-- STOCK -->
                                @if($p->pro_saldo_fin <= 0)
                                    <span class="badge bg-secondary mb-2 align-self-start">
                                        Agotado
                                    </span>
                                @endif

                                <!-- PRECIO -->
                                <p class="fw-bold fs-6 mt-auto mb-0">
                                    ${{ number_format($p->pro_precio_venta, 2) }}
                                </p>

                            </div>
                        </a>
                    </div>
                @endforeach

            </div>

        @else
            <!-- MENSAJE SIN RESULTADOS -->
            <div class="d-flex justify-content-center align-items-center"
                 style="min-height:300px;">
                <div class="alert alert-warning text-center px-5 py-4 shadow-sm">
                    <h5 class="mb-2">😕 Sin resultados</h5>
                    <p class="mb-0">
                        No se encontraron productos con los filtros aplicados.
                    </p>
                </div>
            </div>
        @endif

    </main>

@endsection


@push('scripts')
    <script>
        const form = document.getElementById('filtersForm');
        const searchInput = document.getElementById('q');
        const selects = document.querySelectorAll('#cat, #sort');

        let typingTimer;
        const delay = 500;

        searchInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                form.submit();
            }, delay);
        });

        selects.forEach(select => {
            select.addEventListener('change', () => {
                form.submit();
            });
        });
    </script>
@endpush
