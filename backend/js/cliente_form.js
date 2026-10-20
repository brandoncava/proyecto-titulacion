// formulario de cliente (clientes/formulario.php): DNI = persona con apellidos, RUC = empresa con razon social
(function () {
    var tipo = document.getElementById('tipcl');
    if (!tipo) { return; }
    var num = document.getElementById('numcl');
    var nombre = document.getElementById('nomcl');
    var etiqueta = document.getElementById('etiqueta-nombre');
    var apellidos = document.getElementById('campo-apellidos');
    var tel = document.getElementById('telcl');

    function actualizar() {
        var ruc = tipo.value === 'RUC';
        num.maxLength = ruc ? 11 : 8;
        num.placeholder = ruc ? '11 dígitos' : '8 dígitos';
        if (num.value.length > num.maxLength) { num.value = num.value.slice(0, num.maxLength); }
        etiqueta.firstChild.textContent = ruc ? 'Razón social ' : 'Nombres ';
        nombre.placeholder = ruc ? 'ej. Tiendas de Cómputo EIRL' : 'ej. Rosa María';
        apellidos.hidden = ruc;
        if (ruc) { document.getElementById('apecl').value = ''; }
    }
    // solo digitos en documento y telefono
    [num, tel].forEach(function (campo) {
        campo.addEventListener('input', function () { campo.value = campo.value.replace(/\D/g, ''); });
    });
    tipo.addEventListener('change', actualizar);
    actualizar();

    // para recibir promociones el correo es obligatorio
    var promos = document.getElementById('promos');
    var correo = document.getElementById('emailcl');
    function exigirCorreo() { correo.required = promos.checked; }
    promos.addEventListener('change', exigirCorreo);
    exigirCorreo();
})();
