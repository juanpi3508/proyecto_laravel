document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('filtersForm');
    const searchInput = document.getElementById('q');
    const selects = document.querySelectorAll('#cat, #sort');

    if (!form || !searchInput) return;

    let typingTimer;
    const delay = 500;

    searchInput.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            form.submit();
        }, delay);
    });

    selects.forEach(select => {
        select.addEventListener('change', () => {
            form.submit();
        });
    });
});
