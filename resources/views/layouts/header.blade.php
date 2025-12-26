<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
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
                <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="/catalogo">Catálogo</a></li>

                <li class="nav-item">
                    <a class="nav-link" href="/carrito">
                        <i class="bi bi-cart3 me-1"></i> Carrito
                        <span id="cart-count" class="badge bg-warning text-dark ms-1"></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/login">
                        <i class="bi bi-person-circle me-1"></i> Ingresar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
