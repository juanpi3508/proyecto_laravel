<?php

return [

    'session_key' => 'carrito',

    'cantidad' => [
        'min' => 1,
    ],

    'iva' => 0.15,

    'messages' => [
        'agotado' => 'Este producto se encuentra agotado.',
        'stock_insuficiente' => 'No existe stock suficiente para agregar el producto al carrito.',
        'stock_insuficiente_disponible' => 'No existe stock suficiente para agregar el producto al carrito. Disponible: :stock.',
        'cantidad_ajustada' => 'La cantidad fue ajustada según el stock disponible.',
        'cantidad_minima' => 'La cantidad mínima permitida es 1.',
        'cantidad_actualizada' => 'La cantidad del producto fue actualizada correctamente.',
        'producto_eliminado' => 'Producto eliminado del carrito correctamente.',
        'agregado' => 'Producto agregado al carrito correctamente.',
    ],

];
