(function () {
    var modal = document.getElementById('auth-modal');
    if (!modal) return;

    function open(event) {
        if (event) event.preventDefault();
        modal.classList.add('is-open');
    }

    function close(event) {
        if (event) event.preventDefault();
        modal.classList.remove('is-open');
    }

    document.querySelectorAll('[data-auth-open]').forEach(function (el) {
        el.addEventListener('click', open);
    });

    modal.querySelectorAll('.auth-close, .auth-backdrop, .auth-btn-secondary').forEach(function (el) {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.key === 'Esc') close();
    });
})();
