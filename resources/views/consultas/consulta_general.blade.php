@extends('layouts.app')

@section('title', 'Historial de Compras')

@section('content')

    <header class="bg-light py-4 border-bottom mb-4">
        <div class="container">
            <h1 class="h3 mb-1">Historial de Compras</h1>
            <p class="text-muted mb-0">
                Consulta tus compras realizadas en KoKo Market.
            </p>
        </div>
    </header>

    <main class="container mb-5">

        @if($mensaje)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>
                {{ $mensaje }}
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-body p-0">

                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($facturas as $factura)
                            <tr>
                                <td class="fw-semibold">
                                    <a href="#"
                                       class="text-decoration-none text-primary"
                                       data-id="{{ $factura->id_factura }}"
                                       onclick="abrirDetalleFactura(this)">
                                        {{ $factura->id_factura }}
                                    </a>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($factura->fac_fecha_hora)->format('d/m/Y H:i') }}
                                </td>

                                <td class="fw-bold text-success">
                                    ${{ number_format($factura->fac_total, 2) }}
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $factura->estado_fac }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        @endif

    </main>

    <div class="modal fade" id="detalleFacturaModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="detalleFacturaContenido">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirDetalleFactura(elemento) {
            const idFactura = elemento.dataset.id;
            const modalElement = document.getElementById('detalleFacturaModal');
            const modal = new bootstrap.Modal(modalElement);
            const contenedor = document.getElementById('detalleFacturaContenido');

            contenedor.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    `;

            fetch(`/factura/${idFactura}/popup`)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    modal.show();
                })
                .catch(() => {
                    contenedor.innerHTML = `
                <div class="alert alert-danger">
                    No se pudo cargar el detalle de la factura.
                </div>
            `;
                    modal.show();
                });
        }
    </script>

@endsection
