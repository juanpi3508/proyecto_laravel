// public/assets/js/login-success.js

$(function () {
    const $overlay = $("#login-success-overlay");
    if (!$overlay.length) return; // si no hay overlay, no hay nada que hacer

    const $carrito = $(".carrito-container");

    // Iniciamos el carrito con una ligera animación (opcional)
    if ($carrito.length) {
        $carrito.css({
            opacity: 0,
            transform: "translateY(10px)",
            transition: "opacity 0.4s ease, transform 0.4s ease"
        });
    }

    // Mostrar overlay
    $overlay.fadeIn(300, function () {
        setTimeout(function () {
            // Ocultar overlay y luego eliminarlo del DOM
            $overlay.fadeOut(400, function () {
                $overlay.remove(); // 🔥 esto evita que vuelva a molestar

                if ($carrito.length) {
                    $carrito.css({
                        opacity: 1,
                        transform: "translateY(0)"
                    });
                }
            });
        }, 900);
    });
});
