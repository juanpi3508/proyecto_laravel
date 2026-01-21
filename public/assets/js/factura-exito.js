// assets/js/factura-exito.js
// Animación de éxito cuando el usuario acepta la compra (factura_confirmada)

$(function () {
    const $modalEl = $("#modalConfirmacion");
    if (!$modalEl.length) return; // Si no hay modal, no hacemos nada

    // Mostrar el modal automáticamente si existe (Bootstrap 5)
    if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
        const modalInstance = new bootstrap.Modal($modalEl[0]);
        modalInstance.show();
    } else {
        // Si por alguna razón no está Bootstrap JS, al menos lo mostramos
        $modalEl.modal && $modalEl.modal("show");
    }

    const $form          = $("#formFacturaAprobar");
    const $btnAceptar    = $("#btnAceptarFactura");

    const $stateNormal   = $modalEl.find(".factura-state-normal");
    const $stateLoading  = $modalEl.find(".factura-state-loading");
    const $stateSuccess  = $modalEl.find(".factura-state-success");

    // Interceptamos el submit del formulario
    $form.on("submit.facturaExito", function (e) {
        e.preventDefault();

        // Deshabilitar el botón para evitar doble clic
        $btnAceptar.prop("disabled", true);

        // Cambiar a estado "cargando"
        $stateNormal.addClass("d-none");
        $stateSuccess.addClass("d-none");
        $stateLoading.removeClass("d-none");

        // Pequeño delay para simular confirmación
        setTimeout(function () {
            // Cambiar a estado "éxito"
            $stateLoading.addClass("d-none");
            $stateSuccess.removeClass("d-none");

            // Pequeño tiempo para que el usuario vea el check y luego enviar el form real
            setTimeout(function () {
                $form.off("submit.facturaExito").submit();
            }, 800);
        }, 900);
    });
});
