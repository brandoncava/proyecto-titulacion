// Oculta la pantalla de carga en cuanto el HTML esta listo,
// sin esperar imagenes ni scripts externos (ionicons, CDN...)
(function () {
    function ocultar() {
        var el = document.querySelector('.load_animation');
        if (!el) return;
        el.classList.add('oculto');
        setTimeout(function () { el.style.display = 'none'; }, 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ocultar);
    } else {
        ocultar();
    }
})();
