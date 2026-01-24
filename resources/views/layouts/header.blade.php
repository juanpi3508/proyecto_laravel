<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/logo.jpg') }}"
                 alt="KoKo Market"
                 loading="eager"
                 fetchpriority="high"
                 decoding="sync"
                 style="height: 40px; width: auto; border-radius: 6px;">
            <span class="ms-2">KoKo Market</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">

                {{-- ✅ INICIO --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'fw-bold active text-white' : '' }}"
                       href="{{ route('home') }}">
                        Inicio
                    </a>
                </li>

                {{-- ✅ CATÁLOGO --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('catalogo.index') ? 'fw-bold active text-white' : '' }}"
                       href="{{ route('catalogo.index') }}">
                        Catálogo
                    </a>
                </li>

                {{-- ✅ CARRITO --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('carrito.*') ? 'fw-bold active text-white' : '' }}"
                       href="{{ route('carrito.index') }}">
                        <i class="bi bi-cart3 me-1"></i> Carrito

                        {{-- ✅ Badge contador (solo si hay productos) --}}
                        @if($cartCount > 0)
                            <span id="cart-count" class="badge bg-warning text-dark ms-1">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </li>

                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ auth()->user()->usu_usuario }}
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            {{-- ✅ Historial de Compras --}}
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('facturas.historial') ? 'active fw-bold' : '' }}"
                                   href="{{ route('facturas.historial') }}">
                                    <i class="bi bi-receipt me-2"></i>
                                    Historial de Compras
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            {{-- ✅ Cerrar sesión --}}
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'fw-bold active text-white' : '' }}"
                           href="{{ route('login') }}">
                            <i class="bi bi-person-circle me-1"></i> Ingresar
                        </a>
                    </li>
                @endauth


            </ul>
        </div>
    </div>
</nav>
