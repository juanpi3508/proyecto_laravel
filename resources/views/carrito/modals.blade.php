@if(session('factura_confirmada'))
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pasarela de Pago</h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-2">Compra realizada con éxito.</p>
                    <p class="fw-bold">Factura Nº {{ session('id_factura') }}</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('factura.aprobar', session('id_factura')) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Aceptar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

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
