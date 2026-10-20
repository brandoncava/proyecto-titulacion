// formulario de producto (productos/formulario.php): contador, vista previa de la imagen
// y alta rapida de marca desde el modal sin recargar la pagina
(function () {
    var form = document.getElementById('form-producto');
    if (!form) { return; }
    var esNuevo = form.dataset.nuevo === '1';

    // contador de la descripcion
    var des = document.getElementById('prddes');
    var cont = document.getElementById('contador-des');
    var contar = function () { cont.textContent = des.value.length; };
    des.addEventListener('input', contar);
    contar();

    // vista previa de la imagen elegida antes de guardar
    var archivo = document.getElementById('foto');
    archivo.addEventListener('change', function () {
        var f = archivo.files[0];
        if (!f) { return; }
        if (f.size > 5 * 1024 * 1024) {
            swal('Imagen muy pesada', 'La imagen supera el máximo de 5 MB.', 'warning');
            archivo.value = '';
            return;
        }
        var img = document.getElementById('vista-foto');
        img.onload = function () {
            document.getElementById('foto-medidas').textContent = img.naturalWidth + ' × ' + img.naturalHeight + ' px · ' + Math.round(f.size / 1024) + ' KB';
        };
        img.src = URL.createObjectURL(f);
        img.hidden = false;
        var sin = document.getElementById('sin-foto');
        if (sin) { sin.hidden = true; }
        document.getElementById('foto-titulo').textContent = esNuevo ? 'Imagen elegida' : 'Imagen nueva (sin guardar)';
        document.getElementById('foto-nombre').textContent = f.name;
    });

    // el campo de archivo esta oculto: el navegador no puede avisar que falta, se avisa aqui
    form.addEventListener('submit', function (ev) {
        if (esNuevo && !archivo.files.length) {
            ev.preventDefault();
            swal('Falta la imagen', 'Sube una imagen del producto antes de guardar.', 'warning');
        }
    });

    // alta rapida de marca (modal backend/modal/md_marc.php): actualiza la lista y la deja elegida
    window.marca = function () {
        $.post('../../backend/php/add_marca.php', {
            trat: $('#trat').val(),
            csrf_token: $('input[name=csrf_token]').first().val()
        }, null, 'json').done(function (r) {
            swal(r.ok ? '¡Registrado!' : 'Aviso', r.mensaje, r.ok ? 'success' : 'warning');
            if (!r.idmar) { return; }
            var lista = $('#prdmarc');
            if (!lista.find('option[value="' + r.idmar + '"]').length) {
                lista.append($('<option>').val(r.idmar).text($('#trat').val().trim()));
            }
            lista.val(String(r.idmar));
            $('#trat').val('');
            $('#btns-modals').prop('checked', false);
        }).fail(function () {
            swal('Error', 'No se pudo guardar la marca.', 'error');
        });
    };
})();
