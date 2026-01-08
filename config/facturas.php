<?php

return [

    'session_carrito' => config('carrito.session_key'),

    'iva' => 0.15,

    'tipos' => [
        'eco' => 'ECO',
    ],

    'estados' => [
        'abierta'   => 'ABI',
        'aprobada'  => 'APR',
    ],

    'mensajes' => [
        'sin_cliente' => 'Debe completar sus datos de cliente para facturar.',
        'carrito_vacio' => 'El carrito está vacío.',
        'producto_invalido' => 'Producto inválido en el carrito.',
        'aprobacion_error' => 'No se pudo aprobar la factura.',
        'cantidad_invalida_detalle' => 'Cantidad inválida en detalle de factura.',
        'sin_compras' => 'No existen compras registradas.',
    ],

    'db' => [
        'fn_generar_codigo' => 'GenerarCodigoFactura',
        'fn_aprobar' => 'fn_aprobar_factura_json',
    ],

    'top_vendidos' => 6,
];
