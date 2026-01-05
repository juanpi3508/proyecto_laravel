<div class="mb-3">
    <strong>Factura:</strong> {{ $factura->id_factura }}<br>
    <strong>Fecha:</strong>
    {{ \Carbon\Carbon::parse($factura->fac_fecha_hora)->format('d/m/Y H:i') }}
</div>

<table class="table table-sm table-hover align-middle">
    <thead class="table-light">
    <tr>
        <th>Producto</th>
        <th class="text-center">Cantidad</th>
        <th class="text-end">Precio</th>
        <th class="text-end">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    @foreach($factura->detalles as $detalle)
        <tr>
            <td>{{ $detalle->producto->pro_descripcion }}</td>
            <td class="text-center">{{ $detalle->pxf_cantidad }}</td>
            <td class="text-end">
                ${{ number_format($detalle->pxf_precio_venta, 2) }}
            </td>
            <td class="text-end fw-semibold">
                ${{ number_format($detalle->pxf_subtotal_producto, 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="row justify-content-end">
    <div class="col-md-4">
        <div class="border p-3 rounded">
            <div class="d-flex justify-content-between">
                <span>Subtotal</span>
                <span>${{ number_format($factura->fac_subtotal, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>IVA</span>
                <span>${{ number_format($factura->fac_iva, 2) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span class="text-success">
                    ${{ number_format($factura->fac_total, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>
