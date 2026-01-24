<?php

return [
    // ==================== VALIDACIÓN ====================
    'validation' => [
        'username_required' => 'Debes ingresar tu usuario.',
        'password_required' => 'Debes ingresar tu contraseña.',
    ],

    // ==================== ERRORES ====================
    'errors' => [
        'invalid_credentials' => 'Usuario o contraseña incorrectos.',
        'inactive_user'       => 'Tu usuario está bloqueado.',
        'session_required'    => 'Debes iniciar sesión para realizar el pago.',
        'cliente_id_error'    => 'No se pudo obtener el ID de cliente desde la base de datos.',
        'ruc_required'        => 'El campo RUC/Cédula es obligatorio.',
    ],

    // ==================== MENSAJES DE ÉXITO ====================
    'success' => [
        'login'    => '¡Bienvenido de nuevo a KoKo Market!',
        'register' => '¡Tu cuenta se creó con éxito, bienvenido a KoKo Market!',
    ],
];
