@auth
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pasarela de Pago</h5>
                </div>

                <div class="modal-body">
                    {{-- Estado 1: formulario de tarjeta --}}
                    <div class="factura-state factura-state-form">
                        <p class="mb-3 text-center">
                            Ingresa los datos de tu tarjeta para simular el pago.
                        </p>

                        <div class="mb-3">
                            <label for="card_name" class="form-label">Nombre en la tarjeta</label>
                            <input type="text"
                                   class="form-control"
                                   id="card_name"
                                   autocomplete="cc-name"
                                   placeholder="Como aparece en la tarjeta"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="card_number" class="form-label">Número de tarjeta</label>
                            <input type="text"
                                   class="form-control"
                                   id="card_number"
                                   autocomplete="cc-number"
                                   inputmode="numeric"
                                   maxlength="19"
                                   placeholder="XXXX XXXX XXXX XXXX"
                                   required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="card_expiry" class="form-label">Fecha de caducidad</label>
                                <input type="text"
                                       class="form-control"
                                       id="card_expiry"
                                       autocomplete="cc-exp"
                                       inputmode="numeric"
                                       maxlength="5"
                                       placeholder="MM/AA"
                                       required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="card_cvv" class="form-label">Código de seguridad</label>
                                <input type="password"
                                       class="form-control"
                                       id="card_cvv"
                                       autocomplete="cc-csc"
                                       inputmode="numeric"
                                       maxlength="4"
                                       placeholder="CVV"
                                       required>
                            </div>
                        </div>

                        <small class="text-muted d-block text-center">
                            Esta es una simulación, no se realizará ningún cobro real.
                        </small>
                    </div>

                    {{-- Estado 2: cargando --}}
                    <div class="factura-state factura-state-loading d-none text-center">
                        <div class="spinner-border mb-3" role="status"></div>
                        <p class="mb-2">Procesando tu pago...</p>
                        <p class="text-muted mb-0">Por favor, no cierres esta ventana.</p>
                    </div>

                    {{-- Estado 3: éxito (animación final) --}}
                    <div class="factura-state factura-state-success d-none text-center">
                        <div class="display-4 text-success mb-2">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <p class="mb-2 fw-bold">¡Pago confirmado!</p>
                        <p class="mb-1" id="factura-numero-msg"></p>
                        <p class="text-muted mb-0">Redirigiendo a tus compras...</p>
                    </div>

                    {{-- Estado 4: error --}}
                    <div class="factura-state factura-state-error d-none text-center">
                        <div class="display-4 text-danger mb-2">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <p class="mb-2 fw-bold">Error en el pago</p>
                        <p class="text-muted mb-0" id="factura-error-msg"></p>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- Cancelar: solo cierra el modal, no afecta la base de datos --}}
                    <button type="button" class="btn btn-outline-secondary me-auto" id="btnCancelarFactura" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    {{-- Pagar: genera la factura y la aprueba via AJAX --}}
                    <button type="button" class="btn btn-primary" id="btnPagarFactura">
                        Pagar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endauth

{{-- El resto de tus modales se queda igual --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
            </div>
            <div class="modal-body text-center">
                ¿Está seguro de eliminar el producto del carrito?
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmDelete">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmClearModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vaciar carrito</h5>
            </div>
            <div class="modal-body text-center">
                ¿Estás seguro de vaciar todo el carrito de compras?
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmClear">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loginRequiredModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inicio de sesión requerido</h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">Para finalizar tu compra necesitas iniciar sesión.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="{{ route('login') }}" class="btn btn-primary">Ir a iniciar sesión</a>
            </div>
        </div>
    </div>
</div>
