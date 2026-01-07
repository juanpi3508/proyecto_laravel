let formAEliminar = null;
let formAVaciar = null;

function confirmarEliminacion(btn) {
    formAEliminar = btn.closest('form');
    new bootstrap.Modal(
        document.getElementById('confirmDeleteModal')
    ).show();
}

function confirmarVaciado(btn) {
    formAVaciar = btn.closest('form');
    new bootstrap.Modal(
        document.getElementById('confirmClearModal')
    ).show();
}

document.addEventListener('DOMContentLoaded', function () {

    const btnDelete = document.getElementById('btnConfirmDelete');
    if (btnDelete) {
        btnDelete.addEventListener('click', function () {
            if (formAEliminar) formAEliminar.submit();
        });
    }

    const btnClear = document.getElementById('btnConfirmClear');
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            if (formAVaciar) formAVaciar.submit();
        });
    }

    const modalConfirmacion = document.getElementById('modalConfirmacion');
    if (modalConfirmacion) {
        new bootstrap.Modal(modalConfirmacion).show();
    }
});

function mostrarLoginModal() {
    new bootstrap.Modal(
        document.getElementById('loginRequiredModal')
    ).show();
}
