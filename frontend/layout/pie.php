<?php
// Cierre comun de las paginas del panel (abierto en layout/cabecera.php):
// cierra <main>, carga los scripts base y, si $tablas, DataTables.
// Despues la pagina agrega sus propios scripts y cierra </body></html>.
if (!isset($seccion)) {
    exit;
}
?>
        </main>

    </div>
    <script src="../../backend/js/jquery.min.js"></script>
<?php if ($tablas): ?>
    <script src="../../backend/js/datatable.js"></script>
    <script src="../../backend/js/datatablebuttons.js"></script>
    <script src="../../backend/js/jszip.js"></script>
    <script src="../../backend/js/buttonshtml5.js"></script>
    <script src="../../backend/js/buttonsprint.js"></script>
    <script src="../../backend/js/tablas.js"></script>
<?php endif; ?>
    <script src="../../backend/js/loader.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
