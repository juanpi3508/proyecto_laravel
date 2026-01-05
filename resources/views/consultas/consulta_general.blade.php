@extends('layouts.app')

@section('title', 'Historial de Compras')

@section('content')

    {{-- HEADER DE SECCIÓN --}}
    <header class="bg-light py-4 border-bottom mb-4">
        <div class="container">
            <h1 class="h3 mb-1">Historial de Compras</h1>
            <p class="text-muted mb-0">
                Revisa todas las compras que has realizado en KoKo Market.
            </p>
        </div>
    </header>

    {{-- CONTENIDO --}}
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
                            <th>Subtotal</th>
                            <th>IVA</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($facturas as $factura)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $factura->id_factura }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($factura->fac_fecha_hora)->format('d/m/Y H:i') }}
                                </td>
                                <td>${{ number_format($factura->fac_subtotal, 2) }}</td>
                                <td>${{ number_format($factura->fac_iva, 2) }}</td>
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
@endsection
