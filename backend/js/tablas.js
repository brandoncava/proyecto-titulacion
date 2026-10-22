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
        var tabla = $('#example');
        // TablaPaginada (data-tabla-paginada="url"): para listas que pueden tener miles de filas.
        // El servidor manda solo la pagina que se ve (ver tabla_paginada() en helpers.php), asi el
        // modulo carga rapido; buscar y ordenar tambien los resuelve el servidor. Las filas se
        // desplazan dentro de la tarjeta hasta el borde inferior y el paginador queda a la vista.
        // (Las listas cortas usan TablaSinPaginar, solo css: .tabla-sin-paginar)
        var url = tabla.data('tablaPaginada');
        var opciones = {
            dom: 'Bfrtip',
            language: idioma,
            pagingType: 'simple_numbers',
            buttons: ['copy', 'csv', 'excel', botonPdf, 'print']
        };
        if (url) {
            $.extend(opciones, {
                dom: 'frtip',             // exportar una sola pagina confundiria: los reportes exportan completo
                serverSide: true,
                processing: true,
                ajax: url,
                pageLength: 25,
                order: [],
                searchDelay: 350,
                scrollY: '50vh',
                scrollCollapse: true,
                // una celda puede pedir una clase para su fila: <span data-fila-clase="fila-anulada">
                createdRow: function (fila) {
                    var marca = fila.querySelector('[data-fila-clase]');
                    if (marca) { fila.classList.add(marca.dataset.filaClase); }
                }
            });
        }
        var dt = tabla.DataTable(opciones);

        // si la pagina reserva #buscador-tabla en su encabezado, el buscador va ahi (junto a sus botones)
        var lugar = $('#buscador-tabla');
        if (lugar.length) {
            var campo = $(dt.table().container()).find('.dataTables_filter input');
            campo.attr('placeholder', lugar.data('placeholder') || 'Buscar').attr('aria-label', 'Buscar');
            lugar.append('<span class="las la-search"></span>').append(campo);
            $(dt.table().container()).find('.dataTables_filter').remove();
        }

        if (url) {
            var cuerpo = $(dt.table().container()).find('.dataTables_scrollBody');
            var ajustar = function () {
                // alto libre desde el inicio de las filas hasta el borde, menos la linea de "Mostrando..." y el margen
                var alto = window.innerHeight - cuerpo[0].getBoundingClientRect().top - 90;
                cuerpo.css('max-height', Math.max(220, alto) + 'px');
                dt.columns.adjust();
            };
            ajustar();
            $(window).on('resize', ajustar);
        }
    });
})(jQuery);
