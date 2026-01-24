<?php

/**
 * Mensajes y configuración del formulario de registro
 */

return [
    // ==================== MENSAJES DE ERROR ====================
    'errors' => [
        // Cliente - RUC/Cédula
        'ruc_vacio'          => 'La cédula/RUC es obligatoria.',
        'ruc_solo_numeros'   => 'La cédula/RUC debe contener solo números.',
        'ruc_longitud'       => 'La cédula debe tener 10 dígitos o el RUC 13 dígitos.',
        'ruc_formato_cedula' => 'La cédula debe tener exactamente 10 dígitos numéricos.',
        'ruc_formato_ruc'    => 'El RUC debe tener 13 dígitos y terminar en 001.',

        // Cliente - Nombre
        'nombre_vacio'   => 'El nombre o razón social es obligatorio.',
        'nombre_formato' => 'El nombre solo puede contener letras, espacios y puntos.',
        'nombre_max'     => 'El nombre o razón social no debe exceder 40 caracteres.',

        // Cliente - Email
        'email_vacio'   => 'El correo electrónico es obligatorio.',
        'email_formato' => 'El correo electrónico no tiene un formato válido.',
        'email_max'     => 'El correo electrónico no debe exceder 60 caracteres.',

        // Cliente - Celular
        'celular_vacio'   => 'El celular es obligatorio.',
        'celular_formato' => 'El celular debe empezar con 09 y tener 10 dígitos.',
        'celular_max'     => 'El celular no debe exceder 10 caracteres.',

        // Cliente - Dirección
        'direccion_vacio' => 'La dirección es obligatoria.',
        'direccion_max'   => 'La dirección no debe exceder 60 caracteres.',

        // Cliente - Ciudad
        'ciudad_requerida' => 'La ciudad es obligatoria.',
        'ciudad_invalida'  => 'La ciudad seleccionada no es válida.',

        // Usuario
        'usuario_vacio'    => 'El nombre de usuario es obligatorio.',
        'usuario_max'      => 'El nombre de usuario no debe exceder 50 caracteres.',
        'usuario_en_uso'   => 'Este nombre de usuario ya está en uso.',

        // Contraseña
        'password_vacio'     => 'La contraseña es obligatoria.',
        'password_min'       => 'La contraseña debe tener al menos 8 caracteres.',
        'password_confirmar' => 'Las contraseñas no coinciden.',

        // Escenarios de cliente
        'cliente_inactivo'    => 'Tu cuenta de cliente está inactiva. Por favor contáctate con nosotros.',
        'cliente_con_usuario' => 'Esta cédula/RUC ya tiene un usuario asociado. Por favor inicia sesión.',
    ],

    // ==================== PLACEHOLDERS / EJEMPLOS ====================
    'placeholders' => [
        'ruc_cedula'   => 'Ej: 1712345678 o 1712345678001',
        'nombre'       => 'Ej: Juan Pérez o Mi Empresa S.A.',
        'email'        => 'Ej: correo@ejemplo.com',
        'celular'      => 'Ej: 0991234567',
        'direccion'    => 'Ej: Av. Amazonas N34-56 y Colón',
        'ciudad'       => 'Selecciona una ciudad...',
        'usuario'      => 'Ej: juanperez2024',
        'password'     => 'Mínimo 8 caracteres',
        'password_c'   => 'Repite tu contraseña',
    ],

    // ==================== LÍMITES DE CARACTERES ====================
    'maxlength' => [
        'ruc_cedula' => 13,
        'nombre'     => 40,
        'email'      => 60,
        'celular'    => 10,
        'direccion'  => 60,
        'usuario'    => 50,
        'password'   => 100,
    ],
];
