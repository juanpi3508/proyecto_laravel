// public/assets/js/carrito.js

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

function mostrarLoginModal() {
    new bootstrap.Modal(
        document.getElementById('loginRequiredModal')
    ).show();
}

function money(n) {
    const num = Number(n || 0);
    return num.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function showWarning(msg) {
    const el = document.getElementById('js-cart-warning');
    if (!el) return;

    if (!msg) {
        el.classList.add('d-none');
        el.textContent = '';
        return;
    }

    el.textContent = `⚠ ${msg}`;
    el.classList.remove('d-none');
}

async function ajaxUpdateCantidad(form) {
    const url = form.getAttribute('action');
    const token = form.querySelector('input[name="_token"]')?.value;

    const fd = new FormData(form);
    // Laravel usa method spoof
    if (!fd.has('_method')) fd.append('_method', 'PUT');

    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        body: fd,
    });

    if (!res.ok) {
        let msg = 'No se pudo actualizar la cantidad.';
        try {
            const j = await res.json();
            msg = j.message || msg;
        } catch (_) { }
        throw new Error(msg);
    }

    return await res.json();
}

function applyUpdateToDom(payload) {
    const { item, totales, warning } = payload;

    showWarning(warning);

    const card = document.querySelector(
        `.carrito-item[data-product-id="${item.id_producto}"]`
    );

    if (card) {
        // Subtotal por item
        card.querySelectorAll('.js-item-subtotal').forEach((el) => {
            el.textContent = money(item.subtotal);
        });

        // Inputs number
        card.querySelectorAll('input[type="number"][name="cantidad"]').forEach((inp) => {
            inp.value = item.cantidad;
            inp.defaultValue = String(item.cantidad);
            inp.max = String(item.stock);
        });

        // ✅ Recalcular SIEMPRE los botones +/- (y sus hidden)
        card.querySelectorAll('form').forEach((f) => {
            const method = f.querySelector('input[name="_method"]')?.value?.toUpperCase();
            if (method !== 'PUT') return;

            const hidden = f.querySelector('input[type="hidden"][name="cantidad"]');
            const btn = f.querySelector('button[type="submit"]');

            // El form del input number no tiene hidden ni botón
            if (!hidden || !btn) return;

            const label = btn.textContent.trim();

            // Botón "-"
            if (label === '−' || label === '-') {
                hidden.value = item.cantidad - 1;
                btn.disabled = item.cantidad <= 1;
            }

            // Botón "+"
            if (label === '+') {
                hidden.value = item.cantidad + 1;
                btn.disabled = item.cantidad >= item.stock;
            }
        });
    }

    // Totales
    const elArt = document.getElementById('js-cart-articulos');
    const elSub = document.getElementById('js-cart-subtotal');
    const elImp = document.getElementById('js-cart-impuestos');
    const elTot = document.getElementById('js-cart-total');

    if (elArt) elArt.textContent = totales.articulos;
    if (elSub) elSub.textContent = money(totales.subtotal);
    if (elImp) elImp.textContent = money(totales.impuestos);
    if (elTot) elTot.textContent = money(totales.total);
}

function hookAjaxUpdates() {
    // Intercepta forms PUT de carrito.update
    document.querySelectorAll('form').forEach((form) => {
        const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
        const hasCantidad = !!form.querySelector('input[name="cantidad"]');
        if (method !== 'PUT' || !hasCantidad) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Evitar doble submit
            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const payload = await ajaxUpdateCantidad(form);
                applyUpdateToDom(payload);
            } catch (err) {
                showWarning(err.message);
            } finally {
                // Rehabilitar: applyUpdateToDom ajusta los disabled correctos
                if (btn) btn.disabled = false;
            }
        });
    });
}

/* ============================
   INICIALIZACIÓN DOM
   ============================ */

document.addEventListener('DOMContentLoaded', function () {
    // Modales delete/clear (tu lógica original)
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

    // ✅ Activar AJAX para + / − / input cantidad
    hookAjaxUpdates();
});
