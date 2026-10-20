<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

require '../../backend/config/Conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$sentencia = $connect->prepare(
    'SELECT idprod, codpro, nomprd, desprd, foto, precio, stock, idmar, idcate, modelo, peso, garantia_meses
       FROM productos WHERE idprod = ?'
);
$sentencia->execute([$id]);
$d = $sentencia->fetch();

$marcas = $categorias = [];
$foto = null;
if ($d) {
    // las activas y la actual del producto aunque este dada de baja; si no, el select
    // elegiria otra y al guardar el producto cambiaria de marca o categoria sin querer
    $m = $connect->prepare('SELECT idmar, nomarc, state FROM marca WHERE state = 1 OR idmar = ? ORDER BY nomarc');
    $m->execute([$d->idmar]);
    $marcas = $m->fetchAll();
    $c = $connect->prepare('SELECT idcate, nocate, state FROM categoria WHERE state = 1 OR idcate = ? ORDER BY nocate');
    $c->execute([$d->idcate]);
    $categorias = $c->fetchAll();

    // datos de la imagen actual para mostrarlos junto a la vista previa
    $ruta = __DIR__ . '/../../backend/img/subidas/' . basename((string)$d->foto);
    if ($d->foto && is_file($ruta)) {
        $medidas = @getimagesize($ruta);
        $foto = [
            'url'    => '../../backend/img/subidas/' . rawurlencode(basename($d->foto)),
            'nombre' => basename($d->foto),
            'medidas'=> $medidas ? $medidas[0] . ' × ' . $medidas[1] . ' px' : '',
            'peso'   => round(filesize($ruta) / 1024) . ' KB',
        ];
    }
}

// meses de garantia sugeridos (se agrega el actual si es otro valor)
$opcionesGarantia = [0, 3, 6, 12, 18, 24, 36];
if ($d && !in_array((int)$d->garantia_meses, $opcionesGarantia, true)) {
    $opcionesGarantia[] = (int)$d->garantia_meses;
    sort($opcionesGarantia);
}

$seccion = 'productos';
$migas = 'Productos / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$d): ?>
                <p class="alert alert-warning">No se encontró el producto.</p>
<?php else: ?>
                <div class="cabecera-form">
                    <div>
                        <h1>Editar producto</h1>
                        <p>Actualiza la información y controla el inventario</p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="mostrar.php" class="btn-secundario">Cancelar</a>
                        <button type="submit" form="form-producto" name="upd_prodct" class="btn-primario">
                            <span class="las la-save"></span> Guardar cambios
                        </button>
                    </div>
                </div>

                <form id="form-producto" action="" method="POST" enctype="multipart/form-data" autocomplete="off" class="tarjetas-form">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="prdid" value="<?php echo (int)$d->idprod; ?>">

                    <section class="tarjeta-form">
                        <h2><span class="las la-file-alt"></span> Información general</h2>

                        <label for="prdcod">Código del producto <span class="req">*</span></label>
                        <input type="text" id="prdcod" name="prdcod" value="<?php echo e($d->codpro); ?>" maxlength="14" inputmode="numeric" required>

                        <label for="prdnom">Nombre del producto <span class="req">*</span></label>
                        <input type="text" id="prdnom" name="prdnom" value="<?php echo e($d->nomprd); ?>" maxlength="255" required>

                        <label for="prddes">Descripción del producto <span class="req">*</span></label>
                        <textarea id="prddes" name="prddes" rows="6" maxlength="2000" required><?php echo e($d->desprd); ?></textarea>
                        <small class="contador"><span id="contador-des">0</span>/2000</small>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-image"></span> Imagen del producto</h2>

                        <div class="imagen-producto">
                            <div class="imagen-vista">
<?php if ($foto): ?>
                                <img id="vista-foto" src="<?php echo e($foto['url']); ?>" alt="Imagen de <?php echo e($d->nomprd); ?>">
<?php else: ?>
                                <img id="vista-foto" alt="Sin imagen" hidden>
                                <span class="las la-image sin-imagen" id="sin-foto"></span>
<?php endif; ?>
                            </div>
                            <div class="imagen-datos">
                                <b id="foto-titulo"><?php echo $foto ? 'Imagen actual' : 'Sin imagen'; ?></b>
                                <span id="foto-nombre"><?php echo $foto ? e($foto['nombre']) : ''; ?></span>
                                <small id="foto-medidas"><?php echo $foto ? e(trim($foto['medidas'] . ' · ' . $foto['peso'], ' ·')) : ''; ?></small>
                                <label for="foto" class="btn-secundario btn-cambiar-imagen"><span class="las la-sync"></span> Cambiar imagen</label>
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                                <p class="nota"><span class="las la-info-circle"></span> Formato JPG, PNG, GIF o WEBP, con un tamaño máximo de 5 MB. La imagen nueva se guarda al pulsar "Guardar cambios".</p>
                            </div>
                        </div>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-coins"></span> Precio e inventario</h2>

                        <div class="fila-campos">
                            <div>
                                <label for="prdprec">Precio del producto <span class="req">*</span></label>
                                <div class="campo-con-unidad">
                                    <span class="unidad">S/</span>
                                    <input type="number" id="prdprec" name="prdprec" value="<?php echo e($d->precio); ?>" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div>
                                <label for="prdstock">Stock actual <span class="req">*</span></label>
                                <input type="number" id="prdstock" name="prdstock" value="<?php echo (int)$d->stock; ?>" min="0" step="1" required>
                            </div>
                        </div>
                        <p class="nota"><span class="las la-info-circle"></span> Los ajustes de stock se registran en el kardex como un conteo o una merma, según corresponda. Las compras y ventas lo actualizan solas.</p>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-tag"></span> Clasificación</h2>

                        <div class="fila-campos">
                            <div>
                                <label for="prdmarc">Marca <span class="req">*</span></label>
                                <select id="prdmarc" name="prdmarc" required>
<?php foreach ($marcas as $m): ?>
                                    <option value="<?php echo (int)$m->idmar; ?>"<?php echo (int)$m->idmar === (int)$d->idmar ? ' selected' : ''; ?>><?php echo e($m->nomarc); ?><?php echo (int)$m->state === 1 ? '' : ' (dada de baja)'; ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="prdcate">Categoría <span class="req">*</span></label>
                                <select id="prdcate" name="prdcate" required>
<?php foreach ($categorias as $c): ?>
                                    <option value="<?php echo (int)$c->idcate; ?>"<?php echo (int)$c->idcate === (int)$d->idcate ? ' selected' : ''; ?>><?php echo e($c->nocate); ?><?php echo (int)$c->state === 1 ? '' : ' (dada de baja)'; ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="fila-campos">
                            <div>
                                <label for="prdmod">Modelo <span class="req">*</span></label>
                                <input type="text" id="prdmod" name="prdmod" value="<?php echo e($d->modelo); ?>" required>
                            </div>
                            <div>
                                <label for="prdpes">Peso del producto <span class="req">*</span></label>
                                <input type="text" id="prdpes" name="prdpes" value="<?php echo e($d->peso); ?>" placeholder="ej. 1.6 kg" required>
                            </div>
                        </div>
                        <label for="prdgar">Meses de garantía de la marca <span class="req">*</span></label>
                        <select id="prdgar" name="prdgar" required>
<?php foreach ($opcionesGarantia as $meses): ?>
                            <option value="<?php echo $meses; ?>"<?php echo $meses === (int)$d->garantia_meses ? ' selected' : ''; ?>><?php echo $meses === 0 ? 'Sin garantía' : $meses . ' meses'; ?></option>
<?php endforeach; ?>
                        </select>
                    </section>
                </form>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        // contador de la descripcion
        var des = document.getElementById('prddes');
        var cont = document.getElementById('contador-des');
        if (des) {
            var contar = function () { cont.textContent = des.value.length; };
            des.addEventListener('input', contar);
            contar();
        }

        // vista previa de la imagen nueva antes de guardar
        var archivo = document.getElementById('foto');
        if (!archivo) { return; }
        archivo.addEventListener('change', function () {
            var f = archivo.files[0];
            if (!f) { return; }
            if (f.size > 5 * 1024 * 1024) {
                swal('Imagen muy pesada', 'La imagen supera el máximo de 5 MB.', 'warning');
                archivo.value = '';
                return;
            }
            var img = document.getElementById('vista-foto');
            var url = URL.createObjectURL(f);
            img.onload = function () {
                document.getElementById('foto-medidas').textContent = img.naturalWidth + ' × ' + img.naturalHeight + ' px · ' + Math.round(f.size / 1024) + ' KB';
            };
            img.src = url;
            img.hidden = false;
            var sin = document.getElementById('sin-foto');
            if (sin) { sin.hidden = true; }
            document.getElementById('foto-titulo').textContent = 'Imagen nueva (sin guardar)';
            document.getElementById('foto-nombre').textContent = f.name;
        });
    })();
    </script>
</body>
</html>
