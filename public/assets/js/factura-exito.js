// assets/js/factura-exito.js
// Lógica del modal de pago con validaciones realistas para tarjeta de crédito

$(function () {
    const $modalEl = $("#modalConfirmacion");
    if (!$modalEl.length) return;

    const $stateForm = $modalEl.find(".factura-state-form");
    const $stateLoading = $modalEl.find(".factura-state-loading");
    const $stateSuccess = $modalEl.find(".factura-state-success");
    const $stateError = $modalEl.find(".factura-state-error");

    const $btnPagar = $("#btnPagarFactura");
    const $btnCancelar = $("#btnCancelarFactura");

    const $inputName = $("#card_name");
    const $inputNumber = $("#card_number");
    const $inputExpiry = $("#card_expiry");
    const $inputCvv = $("#card_cvv");

    // ============================
    // MÁSCARAS DE ENTRADA
    // ============================

    // Máscara para número de tarjeta: XXXX XXXX XXXX XXXX
    $inputNumber.on("input", function () {
        let value = $(this).val().replace(/\D/g, "").slice(0, 16);
        let formatted = "";
        for (let i = 0; i < value.length; i += 4) {
            if (formatted) formatted += " ";
            formatted += value.slice(i, i + 4);
        }
        $(this).val(formatted);
    });

    // Solo permitir letras y espacios en el nombre
    $inputName.on("input", function () {
        let value = $(this).val().replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, "");
        $(this).val(value.toUpperCase());
    });

    // Máscara para fecha: MM/AA con validación
    $inputExpiry.on("input", function () {
        let value = $(this).val().replace(/\D/g, "").slice(0, 4);

        // Validar mes (01-12)
        if (value.length >= 2) {
            let month = parseInt(value.slice(0, 2), 10);
            if (month > 12) {
                value = "12" + value.slice(2);
            } else if (month < 1 && value.slice(0, 2) !== "0" && value.slice(0, 2) !== "00") {
                value = "01" + value.slice(2);
            }
        }

        // Formatear con /
        if (value.length >= 3) {
            value = value.slice(0, 2) + "/" + value.slice(2);
        }

        $(this).val(value);
    });

    // Máscara para CVV: solo 3-4 dígitos
    $inputCvv.on("input", function () {
        let value = $(this).val().replace(/\D/g, "").slice(0, 4);
        $(this).val(value);
    });

    // ============================
    // VALIDACIONES
    // ============================

    function validateCardNumber() {
        const digits = $inputNumber.val().replace(/\D/g, "");
        return digits.length === 16;
    }

    function validateName() {
        return $inputName.val().trim().length >= 3;
    }

    function validateExpiry() {
        const value = $inputExpiry.val();
        if (value.length !== 5 || value.indexOf("/") !== 2) {
            return { valid: false, message: "El formato debe ser MM/AA" };
        }

        const parts = value.split("/");
        const month = parseInt(parts[0], 10);
        const year = parseInt("20" + parts[1], 10);

        if (month < 1 || month > 12) {
            return { valid: false, message: "El mes debe estar entre 01 y 12" };
        }

        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;

        // La tarjeta debe ser válida desde el año actual
        if (year < currentYear) {
            return { valid: false, message: "La tarjeta está expirada" };
        }

        // Si es el año actual, el mes debe ser igual o mayor al mes actual
        if (year === currentYear && month < currentMonth) {
            return { valid: false, message: "La tarjeta está expirada" };
        }

        // No permitir más de 10 años en el futuro
        if (year > currentYear + 10) {
            return { valid: false, message: "Fecha de caducidad inválida" };
        }

        return { valid: true };
    }

    function validateCvv() {
        const cvv = $inputCvv.val();
        return cvv.length >= 3 && cvv.length <= 4;
    }

    function showFieldError($field, message) {
        $field.addClass("is-invalid");
        // Remover error previo si existe
        $field.next(".invalid-feedback").remove();
        $field.after('<div class="invalid-feedback">' + message + '</div>');
    }

    function clearFieldError($field) {
        $field.removeClass("is-invalid");
        $field.next(".invalid-feedback").remove();
    }

    function clearAllErrors() {
        [$inputName, $inputNumber, $inputExpiry, $inputCvv].forEach(function ($field) {
            clearFieldError($field);
        });
    }

    // ============================
    // GESTIÓN DEL MODAL
    // ============================

    // Resetear el modal cuando se cierra
    $modalEl.on("hidden.bs.modal", function () {
        resetModal();
    });

    function resetModal() {
        // Mostrar solo el formulario
        $stateForm.removeClass("d-none");
        $stateLoading.addClass("d-none");
        $stateSuccess.addClass("d-none");
        $stateError.addClass("d-none");

        // Rehabilitar botones
        $btnPagar.prop("disabled", false).removeClass("d-none").text("Pagar");
        $btnCancelar.prop("disabled", false).removeClass("d-none");

        // Limpiar campos del formulario
        $inputName.val("");
        $inputNumber.val("");
        $inputExpiry.val("");
        $inputCvv.val("");

        // Limpiar errores
        clearAllErrors();
    }

    function showState(state) {
        $stateForm.addClass("d-none");
        $stateLoading.addClass("d-none");
        $stateSuccess.addClass("d-none");
        $stateError.addClass("d-none");

        if (state === "form") $stateForm.removeClass("d-none");
        if (state === "loading") $stateLoading.removeClass("d-none");
        if (state === "success") $stateSuccess.removeClass("d-none");
        if (state === "error") $stateError.removeClass("d-none");
    }

    // ============================
    // EVENTO PAGAR
    // ============================

    $btnPagar.on("click", function () {
        clearAllErrors();
        let hasErrors = false;

        // Validar nombre
        if (!validateName()) {
            showFieldError($inputName, "Ingresa el nombre como aparece en la tarjeta");
            hasErrors = true;
        }

        // Validar número de tarjeta
        if (!validateCardNumber()) {
            showFieldError($inputNumber, "El número de tarjeta debe tener 16 dígitos");
            hasErrors = true;
        }

        // Validar fecha de expiración
        const expiryResult = validateExpiry();
        if (!expiryResult.valid) {
            showFieldError($inputExpiry, expiryResult.message);
            hasErrors = true;
        }

        // Validar CVV
        if (!validateCvv()) {
            showFieldError($inputCvv, "El CVV debe tener 3 o 4 dígitos");
            hasErrors = true;
        }

        if (hasErrors) {
            return;
        }

        // Deshabilitar botones
        $btnPagar.prop("disabled", true);
        $btnCancelar.prop("disabled", true);

        // Mostrar estado de carga
        showState("loading");

        // Realizar petición AJAX para procesar el pago
        $.ajax({
            url: "/factura/procesar-pago",
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            data: {
                card_name: $inputName.val(),
                card_number: $inputNumber.val().replace(/\s/g, ""),
                card_expiry: $inputExpiry.val(),
                card_cvv: $inputCvv.val()
            },
            success: function (response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    $("#factura-numero-msg").text("Tu factura Nº " + response.id_factura + " está lista.");
                    showState("success");

                    // Ocultar botones
                    $btnPagar.addClass("d-none");
                    $btnCancelar.addClass("d-none");

                    // Redirigir después de un pequeño delay
                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    showError(response.message || "Error desconocido al procesar el pago.");
                }
            },
            error: function (xhr) {
                let message = "Error al procesar el pago.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            }
        });
    });

    function showError(message) {
        $("#factura-error-msg").text(message);
        showState("error");

        // Rehabilitar botones para que pueda intentar de nuevo o cancelar
        $btnPagar.prop("disabled", false).text("Reintentar");
        $btnCancelar.prop("disabled", false);
    }

    // Cuando hace clic en reintentar desde estado error, volver al formulario
    $btnPagar.on("click.retry", function () {
        if ($stateError.is(":visible")) {
            $btnPagar.text("Pagar");
            showState("form");
            return false; // Prevenir que se ejecute el handler principal
        }
    });
});

// Función global para abrir el modal de pago
function abrirModalPago() {
    const modalEl = document.getElementById("modalConfirmacion");
    if (modalEl && typeof bootstrap !== "undefined" && bootstrap.Modal) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}
