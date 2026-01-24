/**
 * register.js
 * Lógica de registro con validación de cédula/RUC y escenarios de cliente
 * Los mensajes vienen del backend via window.REGISTER_MESSAGES
 */
document.addEventListener('DOMContentLoaded', function () {
    // ========== ELEMENTOS DOM ==========
    const form = document.getElementById('register-form');
    const rucInput = document.getElementById('ruc_cedula');
    if (!rucInput || !form) return;

    const nombreInput = document.getElementById('cli_nombre');
    const clienteIdInput = document.getElementById('cliente_id');

    const mailInput = document.getElementById('cli_mail');
    const telInput = document.getElementById('cli_telefono');
    const dirInput = document.getElementById('cli_direccion');
    const ciudadSelect = document.getElementById('cli_ciudad');

    const usuarioInput = document.getElementById('usu_usuario');
    const passwordInput = document.getElementById('usu_contrasena');
    const passwordConfirmInput = document.getElementById('usu_contrasena_confirm');

    const extraWrapper = document.getElementById('cliente-extra-fields');
    const buscarUrl = rucInput.dataset.buscarUrl;
    const submitBtn = document.getElementById('btn_submit_register');

    // ========== MENSAJES (desde el backend) ==========
    const MSG = window.REGISTER_MESSAGES || {
        ruc_vacio: 'La cédula/RUC es obligatoria.',
        ruc_solo_numeros: 'La cédula/RUC debe contener solo números.',
        ruc_longitud: 'La cédula debe tener 10 dígitos o el RUC 13 dígitos.',
        ruc_formato_cedula: 'La cédula debe tener exactamente 10 dígitos numéricos.',
        ruc_formato_ruc: 'El RUC debe tener 13 dígitos y terminar en 001.',
        nombre_vacio: 'El nombre o razón social es obligatorio.',
        nombre_formato: 'El nombre solo puede contener letras, espacios y puntos.',
        nombre_max: 'El nombre o razón social no debe exceder 40 caracteres.',
        email_vacio: 'El correo electrónico es obligatorio.',
        email_formato: 'El correo electrónico no tiene un formato válido.',
        email_max: 'El correo electrónico no debe exceder 60 caracteres.',
        celular_vacio: 'El celular es obligatorio.',
        celular_formato: 'El celular debe empezar con 09 y tener 10 dígitos.',
        celular_max: 'El celular no debe exceder 10 caracteres.',
        direccion_vacio: 'La dirección es obligatoria.',
        direccion_max: 'La dirección no debe exceder 60 caracteres.',
        ciudad_requerida: 'La ciudad es obligatoria.',
        usuario_vacio: 'El nombre de usuario es obligatorio.',
        usuario_max: 'El nombre de usuario no debe exceder 50 caracteres.',
        password_vacio: 'La contraseña es obligatoria.',
        password_min: 'La contraseña debe tener al menos 8 caracteres.',
        password_confirmar: 'Las contraseñas no coinciden.',
        cliente_inactivo: 'Tu cuenta de cliente está inactiva. Por favor contáctate con nosotros.',
        cliente_con_usuario: 'Esta cédula/RUC ya tiene un usuario asociado. Por favor inicia sesión o usa "Olvidé mi contraseña".',
    };

    // ========== ESTADO ==========
    let escenarioResuelto = false;
    let ultimoRucConsultado = null;
    let consultandoRuc = false;

    // ========== FUNCIONES DE UI ==========

    function showExtraFields(show) {
        if (!extraWrapper) return;
        if (show) {
            extraWrapper.classList.remove('d-none');
        } else {
            extraWrapper.classList.add('d-none');
        }
    }

    function setExtraClienteEnabled(enabled, preserveValues = false) {
        if (mailInput) {
            mailInput.disabled = !enabled;
            if (!enabled && !preserveValues) mailInput.value = '';
        }
        if (telInput) {
            telInput.disabled = !enabled;
            if (!enabled && !preserveValues) telInput.value = '';
        }
        if (dirInput) {
            dirInput.disabled = !enabled;
            if (!enabled && !preserveValues) dirInput.value = '';
        }
        if (ciudadSelect) {
            ciudadSelect.disabled = !enabled;
            if (!enabled && !preserveValues) ciudadSelect.value = '';
        }

        showExtraFields(enabled);
    }

    function setNombreReadonly(readonly) {
        if (!nombreInput) return;
        nombreInput.readOnly = readonly;
        nombreInput.classList.toggle('field-readonly', readonly);
    }

    function resetFormState() {
        escenarioResuelto = false;
        ultimoRucConsultado = null;

        if (clienteIdInput) clienteIdInput.value = '';
        setNombreReadonly(false);
        setExtraClienteEnabled(false);
        if (submitBtn) submitBtn.disabled = false;
    }

    function clearClienteInputs(keepRuc) {
        if (!keepRuc && rucInput) rucInput.value = '';
        if (nombreInput) nombreInput.value = '';
        if (clienteIdInput) clienteIdInput.value = '';
        if (mailInput) mailInput.value = '';
        if (telInput) telInput.value = '';
        if (dirInput) dirInput.value = '';
        if (ciudadSelect) ciudadSelect.value = '';
    }

    // ========== FUNCIONES DE VALIDACIÓN ==========

    function showFieldError(input, message) {
        if (!input) return;
        clearFieldError(input);
        input.classList.add('is-invalid');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = message;
        // Insertar después del input o del input-group
        const parent = input.closest('.input-group') || input;
        parent.parentNode.insertBefore(feedback, parent.nextSibling);
    }

    function clearFieldError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        const parent = input.closest('.mb-3');
        if (parent) {
            // Solo remover los que nosotros creamos (no los de Blade con data-blade)
            const dynamicFeedback = parent.querySelectorAll('.invalid-feedback.d-block:not([data-blade])');
            dynamicFeedback.forEach(el => el.remove());
        }
    }

    function clearAllErrors() {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback.d-block:not([data-blade])').forEach(el => el.remove());
    }

    // ========== VALIDACIONES ESPECÍFICAS ==========

    /**
     * Valida cédula (10 dígitos) o RUC (13 dígitos terminando en 001)
     */
    function rucEsValido(ruc) {
        if (!ruc) return { valid: false, message: MSG.ruc_vacio };

        // Solo números
        if (!/^\d+$/.test(ruc)) return { valid: false, message: MSG.ruc_solo_numeros };

        const length = ruc.length;

        // Cédula: exactamente 10 dígitos
        if (length === 10) {
            return { valid: true };
        }

        // RUC: exactamente 13 dígitos y terminar en 001
        if (length === 13) {
            if (!ruc.endsWith('001')) {
                return { valid: false, message: MSG.ruc_formato_ruc };
            }
            return { valid: true };
        }

        // Longitud inválida
        return { valid: false, message: MSG.ruc_longitud };
    }

    function nombreEsValido(nombre) {
        if (!nombre || !nombre.trim()) return { valid: false, message: MSG.nombre_vacio };
        if (nombre.length > 40) return { valid: false, message: MSG.nombre_max };
        if (!/^[\p{L}\s.]+$/u.test(nombre)) return { valid: false, message: MSG.nombre_formato };
        return { valid: true };
    }

    function emailEsValido(email, esObligatorio) {
        if (!email || !email.trim()) {
            if (esObligatorio) return { valid: false, message: MSG.email_vacio };
            return { valid: true };
        }
        if (email.length > 60) return { valid: false, message: MSG.email_max };
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) return { valid: false, message: MSG.email_formato };
        return { valid: true };
    }

    /**
     * Valida que el celular empiece con 09 y tenga exactamente 10 dígitos
     */
    function celularEsValido(cel, esObligatorio) {
        if (!cel || !cel.trim()) {
            if (esObligatorio) return { valid: false, message: MSG.celular_vacio };
            return { valid: true };
        }
        if (cel.length > 10) return { valid: false, message: MSG.celular_max };

        // Debe empezar con 09 y tener exactamente 10 dígitos
        if (!/^09\d{8}$/.test(cel)) {
            return { valid: false, message: MSG.celular_formato };
        }

        return { valid: true };
    }

    function direccionEsValida(dir, esObligatorio) {
        if (!dir || !dir.trim()) {
            if (esObligatorio) return { valid: false, message: MSG.direccion_vacio };
            return { valid: true };
        }
        if (dir.length > 60) return { valid: false, message: MSG.direccion_max };
        return { valid: true };
    }

    function usuarioEsValido(usuario) {
        if (!usuario || !usuario.trim()) return { valid: false, message: MSG.usuario_vacio };
        if (usuario.length > 50) return { valid: false, message: MSG.usuario_max };
        return { valid: true };
    }

    function passwordEsValido(password) {
        if (!password) return { valid: false, message: MSG.password_vacio };
        if (password.length < 8) return { valid: false, message: MSG.password_min };
        return { valid: true };
    }

    function passwordsCoinciden(password, confirm) {
        if (password !== confirm) return { valid: false, message: MSG.password_confirmar };
        return { valid: true };
    }

    // ========== VALIDACIÓN COMPLETA DEL FORMULARIO ==========

    function validarFormulario() {
        clearAllErrors();
        let valid = true;
        let firstInvalid = null;

        // Validar RUC
        const rucResult = rucEsValido(rucInput.value.trim());
        if (!rucResult.valid) {
            showFieldError(rucInput, rucResult.message);
            valid = false;
            firstInvalid = firstInvalid || rucInput;
        }

        // Validar nombre (si no es readonly = escenario cliente existente)
        if (!nombreInput.readOnly) {
            const nombreResult = nombreEsValido(nombreInput.value);
            if (!nombreResult.valid) {
                showFieldError(nombreInput, nombreResult.message);
                valid = false;
                firstInvalid = firstInvalid || nombreInput;
            }
        }

        // Si es cliente nuevo (campos extra visibles), validar esos campos como OBLIGATORIOS
        const esClienteNuevo = extraWrapper && !extraWrapper.classList.contains('d-none');

        if (esClienteNuevo) {
            // Email (obligatorio)
            const emailResult = emailEsValido(mailInput?.value.trim(), true);
            if (!emailResult.valid) {
                showFieldError(mailInput, emailResult.message);
                valid = false;
                firstInvalid = firstInvalid || mailInput;
            }

            // Celular (obligatorio)
            const celResult = celularEsValido(telInput?.value.trim(), true);
            if (!celResult.valid) {
                showFieldError(telInput, celResult.message);
                valid = false;
                firstInvalid = firstInvalid || telInput;
            }

            // Ciudad (obligatoria)
            if (!ciudadSelect?.value) {
                showFieldError(ciudadSelect, MSG.ciudad_requerida);
                valid = false;
                firstInvalid = firstInvalid || ciudadSelect;
            }

            // Dirección (obligatoria)
            const dirResult = direccionEsValida(dirInput?.value.trim(), true);
            if (!dirResult.valid) {
                showFieldError(dirInput, dirResult.message);
                valid = false;
                firstInvalid = firstInvalid || dirInput;
            }
        }

        // Validar usuario
        const usuarioResult = usuarioEsValido(usuarioInput?.value);
        if (!usuarioResult.valid) {
            showFieldError(usuarioInput, usuarioResult.message);
            valid = false;
            firstInvalid = firstInvalid || usuarioInput;
        }

        // Validar contraseña
        const passwordResult = passwordEsValido(passwordInput?.value);
        if (!passwordResult.valid) {
            showFieldError(passwordInput, passwordResult.message);
            valid = false;
            firstInvalid = firstInvalid || passwordInput;
        }

        // Validar que coincidan las contraseñas
        if (passwordInput?.value && passwordConfirmInput) {
            const matchResult = passwordsCoinciden(passwordInput.value, passwordConfirmInput.value);
            if (!matchResult.valid) {
                showFieldError(passwordConfirmInput, matchResult.message);
                valid = false;
                firstInvalid = firstInvalid || passwordConfirmInput;
            }
        }

        // Focus en el primer campo inválido
        if (firstInvalid) {
            firstInvalid.focus();
        }

        return valid;
    }

    // ========== PROCESAMIENTO DE ESCENARIOS DE RUC ==========

    function procesarEscenarioRuc(data, ruc) {
        const status = data.status;

        ultimoRucConsultado = ruc;
        escenarioResuelto = false;
        consultandoRuc = false;

        if (status === 'no_cliente') {
            // Cliente nuevo → mostrar formulario completo
            setExtraClienteEnabled(true);
            escenarioResuelto = true;
            return;
        }

        if (status === 'cliente_sin_usuario') {
            const usar = window.confirm(
                'Hemos encontrado un cliente con esta cédula/RUC.\n\n' +
                '¿Deseas usar este cliente para crear tu usuario?'
            );

            if (usar) {
                if (clienteIdInput) {
                    clienteIdInput.value = data.cliente.id_cliente || '';
                }
                if (nombreInput) {
                    nombreInput.value = data.cliente.cli_nombre || '';
                }
                setNombreReadonly(true);
                setExtraClienteEnabled(false);
                escenarioResuelto = true;
            } else {
                clearClienteInputs(false);
                resetFormState();
                rucInput.focus();
            }
            return;
        }

        if (status === 'cliente_con_usuario') {
            showFieldError(rucInput, MSG.cliente_con_usuario);

            clearClienteInputs(false);
            resetFormState();
            rucInput.focus();
            return;
        }

        if (status === 'cliente_inactivo') {
            showFieldError(rucInput, MSG.cliente_inactivo);

            clearClienteInputs(false);
            resetFormState();
            rucInput.focus();
            return;
        }

        resetFormState();
    }

    async function consultarRuc(ruc) {
        if (!buscarUrl) {
            console.error('No se configuró data-buscar-url en #ruc_cedula');
            return;
        }

        consultandoRuc = true;

        try {
            const response = await fetch(buscarUrl + '?ruc=' + encodeURIComponent(ruc), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            const data = await response.json();
            procesarEscenarioRuc(data, ruc);
        } catch (err) {
            console.error('Error buscando cliente por RUC:', err);
            resetFormState();
            consultandoRuc = false;
        }
    }

    // ========== MÁSCARAS DE ENTRADA ==========

    // Solo números en RUC (máximo 13)
    rucInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 13);

        // Si cambia el RUC, resetear el escenario
        if (this.value !== ultimoRucConsultado) {
            escenarioResuelto = false;
            if (clienteIdInput) clienteIdInput.value = '';
            setNombreReadonly(false);
        }
    });

    // Solo números en celular (máximo 10)
    if (telInput) {
        telInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    // ========== INICIALIZACIÓN ==========

    function initStateFromDOM() {
        if (!extraWrapper) return;

        const initialVisible = extraWrapper.dataset.initialVisible === '1';

        if (initialVisible) {
            // Venimos de un intento de cliente nuevo con errores
            setExtraClienteEnabled(true, true);
            escenarioResuelto = true;
            // Guardar el RUC actual como consultado
            if (rucInput.value.trim()) {
                ultimoRucConsultado = rucInput.value.trim();
            }
        } else {
            setExtraClienteEnabled(false);
            escenarioResuelto = false;
            ultimoRucConsultado = null;
        }
    }

    // Si hay un RUC ya puesto y no hemos consultado, consultar automáticamente
    async function checkInitialRuc() {
        const ruc = rucInput.value.trim();

        // Si hay RUC, es válido, pero no hay escenario resuelto, consultar
        if (ruc && !escenarioResuelto) {
            const rucResult = rucEsValido(ruc);
            if (rucResult.valid) {
                await consultarRuc(ruc);
            }
        }
    }

    initStateFromDOM();

    // Si no se resolvió el escenario por DOM, verificar si hay RUC para consultar
    if (!escenarioResuelto) {
        checkInitialRuc();
    }

    // ========== EVENTO BLUR EN RUC ==========

    rucInput.addEventListener('blur', function () {
        const ruc = rucInput.value.trim();

        // Limpiar error previo
        clearFieldError(rucInput);

        if (!ruc) {
            clearClienteInputs(false);
            resetFormState();
            return;
        }

        if (escenarioResuelto && ultimoRucConsultado === ruc) {
            return;
        }

        const rucResult = rucEsValido(ruc);
        if (!rucResult.valid) {
            showFieldError(rucInput, rucResult.message);
            return;
        }

        consultarRuc(ruc);
    });

    // ========== EVENTO SUBMIT ==========

    form.addEventListener('submit', function (e) {
        // Primero validar el formulario antes de enviar
        if (!validarFormulario()) {
            e.preventDefault();
            return;
        }

        // Si todavía estamos consultando el RUC, esperar
        if (consultandoRuc) {
            e.preventDefault();
            alert('Por favor espere mientras se verifica su cédula/RUC.');
            return;
        }

        // Si el RUC cambió y no se ha consultado, validar primero
        const ruc = rucInput.value.trim();
        if (ruc && !escenarioResuelto && ruc !== ultimoRucConsultado) {
            e.preventDefault();
            consultarRuc(ruc);
            return;
        }
    });
});
