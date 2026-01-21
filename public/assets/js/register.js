document.addEventListener('DOMContentLoaded', function () {
    const rucInput       = document.getElementById('ruc_cedula');
    if (!rucInput) return;

    const nombreInput    = document.getElementById('cli_nombre');
    const clienteIdInput = document.getElementById('cliente_id');

    const mailInput      = document.getElementById('cli_mail');
    const telInput       = document.getElementById('cli_telefono');
    const dirInput       = document.getElementById('cli_direccion');
    const ciudadSelect   = document.getElementById('cli_ciudad');

    const extraWrapper   = document.getElementById('cliente-extra-fields');
    const buscarUrl      = rucInput.dataset.buscarUrl;
    const submitBtn      = document.querySelector('button[type="submit"].btn-register');

    let escenarioResuelto   = false;
    let ultimoRucConsultado = null;

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
            mailInput.required = enabled;
            if (!enabled && !preserveValues) mailInput.value = '';
        }
        if (telInput) {
            telInput.disabled = !enabled;
            telInput.required = enabled;
            if (!enabled && !preserveValues) telInput.value = '';
        }
        if (dirInput) {
            dirInput.disabled = !enabled;
            if (!enabled && !preserveValues) dirInput.value = '';
        }
        if (ciudadSelect) {
            ciudadSelect.disabled = !enabled;
            ciudadSelect.required = enabled;
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
        escenarioResuelto   = false;
        ultimoRucConsultado = null;

        if (clienteIdInput) clienteIdInput.value = '';
        setNombreReadonly(false);
        setExtraClienteEnabled(false); // aquí sí limpiamos porque el usuario está empezando de cero
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

    function rucBasicaValida(ruc) {
        if (!ruc) return false;
        if (!/^[0-9]+$/.test(ruc)) return false;
        if (!(ruc.length === 10 || ruc.length === 13)) return false;
        return true;
    }

    // 🆕 Inicializar respetando lo que vino del servidor
    function initStateFromDOM() {
        if (!extraWrapper) {
            // Sin bloque extra, nada raro
            return;
        }

        const initialVisible = extraWrapper.dataset.initialVisible === '1';

        if (initialVisible) {
            // Venimos de un intento de cliente nuevo con errores:
            // los campos extra ya vienen con old() y errores → NO los tocamos.
            setExtraClienteEnabled(true, true); // habilita pero conserva valores
            escenarioResuelto = true;           // ya sabemos que es "no_cliente"
        } else {
            // Estado base (página fresca) → extras ocultos
            setExtraClienteEnabled(false);
            escenarioResuelto   = false;
            ultimoRucConsultado = null;
        }
    }

    // ---------- Lógica común para procesar el resultado del endpoint ----------
    function procesarEscenarioRuc(data, ruc) {
        const status = data.status;

        ultimoRucConsultado = ruc;
        escenarioResuelto   = false;

        if (status === 'no_cliente') {
            // Cliente nuevo → mostrar formulario completo
            setExtraClienteEnabled(true);  // habilita y muestra
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
            alert(
                'Esta cédula/RUC ya tiene un usuario asociado.\n\n' +
                'Por favor, inicia sesión o usa la opción "Olvidé mi contraseña".'
            );

            clearClienteInputs(false);
            resetFormState();
            rucInput.focus();
            return;
        }

        if (status === 'cliente_inactivo') {
            alert(
                'Tu cuenta de cliente se encuentra INACTIVA.\n\n' +
                'Por favor contáctate con nosotros para más información.'
            );

            clearClienteInputs(false);
            resetFormState();
            rucInput.focus();
            return;
        }

        resetFormState();
    }

    // ---------- Inicialización ----------
    initStateFromDOM();

    // ---------- BLUR en cédula ----------
    rucInput.addEventListener('blur', function () {
        const ruc = rucInput.value.trim();

        if (!ruc) {
            clearClienteInputs(false);
            resetFormState();
            return;
        }

        if (escenarioResuelto && ultimoRucConsultado === ruc) {
            return;
        }

        if (!rucBasicaValida(ruc)) {
            alert('La cédula/RUC debe tener solo números y ser de 10 o 13 dígitos.');
            clearClienteInputs(false);
            resetFormState();
            return;
        }

        if (!buscarUrl) {
            console.error('No se configuró data-buscar-url en #ruc_cedula');
            return;
        }

        fetch(buscarUrl + '?ruc=' + encodeURIComponent(ruc), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
            .then(r => r.json())
            .then(data => procesarEscenarioRuc(data, ruc))
            .catch(err => {
                console.error('Error buscando cliente por RUC:', err);
                resetFormState();
            });
    });

    // Si cambia la cédula, desmontamos el escenario actual
    rucInput.addEventListener('input', function () {
        const ruc = rucInput.value.trim();

        if (ruc !== ultimoRucConsultado) {
            escenarioResuelto = false;
            if (clienteIdInput) clienteIdInput.value = '';
            setNombreReadonly(false);
            // OJO: no escondemos extras aquí, solo cuando el usuario borra realmente o cambia de flujo.
        }
    });

    // No tocamos el submit: el backend manda.
});
