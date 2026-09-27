// Configuracion comun de las tablas #example (DataTables): botones de exportacion
// y textos en espanol. pdfmake + vfs_fonts (~2 MB) se descargan solo al pulsar "PDF".
(function ($) {
    var BASE = document.currentScript.src.replace(/tablas\.js[^/]*$/, '');
    var pdfCargado = null;

    function cargarScript(src) {
        return new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = BASE + src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    function cargarPdfmake() {
        if (!pdfCargado) {
            // vfs_fonts necesita que pdfmake ya este cargado
            pdfCargado = cargarScript('pdfmake.js').then(function () {
                return cargarScript('vfs_fonts.js');
            });
            pdfCargado.catch(function () { pdfCargado = null; });
        }
        return pdfCargado;
    }

    var botonPdf = {
        extend: 'pdfHtml5',
        // sin esto Buttons oculta el boton porque pdfmake aun no existe
        available: function () { return true; },
        action: function (e, dt, button, config) {
            var boton = this;
            boton.processing(true);
            cargarPdfmake().then(function () {
                boton.processing(false);
                $.fn.dataTable.ext.buttons.pdfHtml5.action.call(boton, e, dt, button, config);
            }, function () {
                boton.processing(false);
                alert('No se pudo cargar el generador de PDF.');
            });
        }
    };

    var idioma = {
        emptyTable: 'No hay datos',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros)',
        lengthMenu: 'Mostrar _MENU_ registros',
        loadingRecords: 'Cargando...',
        processing: 'Procesando...',
        search: 'Buscar:',
        zeroRecords: 'No se encontraron resultados',
        paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
        buttons: {
            copy: 'Copiar',
            print: 'Imprimir',
            copyTitle: 'Copiado al portapapeles',
            copySuccess: { _: '%d filas copiadas', 1: '1 fila copiada' }
        }
    };

    $(function () {
        $('#example').DataTable({
            dom: 'Bfrtip',
            language: idioma,
            pagingType: 'simple_numbers',
            buttons: ['copy', 'csv', 'excel', botonPdf, 'print']
        });
    });
})(jQuery);
