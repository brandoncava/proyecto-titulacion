<?php
// formulario de producto en tarjetas, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define:
//   $producto     fila de productos a editar, o null si es nuevo
//   $marcas       marcas para el select (idmar, nomarc, state)
//   $categorias   categorias para el select (idcate, nocate, state)
//   $foto         datos de la imagen actual (url, nombre, medidas, peso) o null
if (!isset($marcas, $categorias)) {
    exit;
}

$esNuevo = empty($producto);
$p = $producto ?: (object)[
    'idprod' => 0, 'codpro' => '', 'nomprd' => '', 'desprd' => '', 'precio' => '', 'stock' => 0,
    'idmar' => 0, 'idcate' => 0, 'modelo' => '', 'peso' => '', 'garantia_meses' => 12,
];

// meses de garantia sugeridos (se agrega el actual si es otro valor)
$opcionesGarantia = [0, 3, 6, 12, 18, 24, 36];
if (!in_array((int)$p->garantia_meses, $opcionesGarantia, true)) {
    $opcionesGarantia[] = (int)$p->garantia_meses;
    sort($opcionesGarantia);
}
?>
<?php
$cabecera = [
    'titulo'    => $esNuevo ? 'Nuevo producto' : 'Editar producto',
    'subtitulo' => $esNuevo ? 'Registra un producto y su stock inicial' : 'Actualiza la información y controla el inventario',
    'form'      => 'form-producto',
    'boton'     => $esNuevo ? 'add_prodct' : 'upd_prodct',
    'texto'     => $esNuevo ? 'Guardar producto' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-producto" action="" method="POST" enctype="multipart/form-data" autocomplete="off" class="tarjetas-form" data-nuevo="<?php echo $esNuevo ? 1 : 0; ?>">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="prdid" value="<?php echo (int)$p->idprod; ?>">
<?php endif; ?>

                    <section class="tarjeta-form">
                        <h2><span class="las la-file-alt"></span> Información general</h2>

                        <label for="prdcod">Código del producto <span class="req">*</span></label>
                        <input type="text" id="prdcod" name="prdcod" value="<?php echo e($p->codpro); ?>" maxlength="14" inputmode="numeric" placeholder="ej. 22478754784578" required>

                        <label for="prdnom">Nombre del producto <span class="req">*</span></label>
                        <input type="text" id="prdnom" name="prdnom" value="<?php echo e($p->nomprd); ?>" maxlength="255" placeholder="ej. Laptop Lenovo V14" required>

                        <label for="prddes">Descripción del producto <span class="req">*</span></label>
                        <textarea id="prddes" name="prddes" rows="6" maxlength="2000" placeholder="Procesador, memoria, disco, pantalla..." required><?php echo e($p->desprd); ?></textarea>
                        <small class="contador"><span id="contador-des">0</span>/2000</small>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-image"></span> Imagen del producto</h2>

                        <div class="imagen-producto">
                            <div class="imagen-vista">
<?php if ($foto): ?>
                                <img id="vista-foto" src="<?php echo e($foto['url']); ?>" alt="Imagen de <?php echo e($p->nomprd); ?>">
<?php else: ?>
                                <img id="vista-foto" alt="Vista previa" hidden>
                                <span class="las la-image sin-imagen" id="sin-foto"></span>
<?php endif; ?>
                            </div>
                            <div class="imagen-datos">
                                <b id="foto-titulo"><?php echo $foto ? 'Imagen actual' : ($esNuevo ? 'Sin imagen todavía' : 'Sin imagen'); ?></b>
                                <span id="foto-nombre"><?php echo $foto ? e($foto['nombre']) : ''; ?></span>
                                <small id="foto-medidas"><?php echo $foto ? e(trim($foto['medidas'] . ' · ' . $foto['peso'], ' ·')) : ''; ?></small>
                                <label for="foto" class="btn-secundario btn-cambiar-imagen">
                                    <span class="las <?php echo $esNuevo ? 'la-upload' : 'la-sync'; ?>"></span> <?php echo $esNuevo ? 'Subir imagen' : 'Cambiar imagen'; ?><?php echo $esNuevo ? ' <span class="req">*</span>' : ''; ?>
                                </label>
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                                <p class="nota"><span class="las la-info-circle"></span> Formato JPG, PNG, GIF o WEBP, con un tamaño máximo de 5 MB.<?php echo $esNuevo ? '' : ' La imagen nueva se guarda al pulsar "Guardar cambios".'; ?></p>
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
                                    <input type="number" id="prdprec" name="prdprec" value="<?php echo e($p->precio); ?>" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                            </div>
                            <div>
                                <label for="prdstock"><?php echo $esNuevo ? 'Stock inicial' : 'Stock actual'; ?> <span class="req">*</span></label>
                                <input type="number" id="prdstock" name="prdstock" value="<?php echo (int)$p->stock; ?>" min="0" step="1" required>
                            </div>
                        </div>
                        <p class="nota"><span class="las la-info-circle"></span>
                            <?php echo $esNuevo
                                ? 'El stock inicial queda registrado en el kardex como saldo inicial. Después lo actualizan las compras y ventas.'
                                : 'Los ajustes de stock se registran en el kardex como un conteo o una merma, según corresponda. Las compras y ventas lo actualizan solas.'; ?>
                        </p>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-tag"></span> Clasificación</h2>

                        <div class="fila-campos">
                            <div>
                                <div class="label-con-accion">
                                    <label for="prdmarc">Marca <span class="req">*</span></label>
<?php if (puede('catalogos')): ?>
                                    <label for="btns-modals" class="enlace-accion"><span class="las la-plus"></span> Nueva marca</label>
<?php endif; ?>
                                </div>
                                <select id="prdmarc" name="prdmarc" required>
<?php if ($esNuevo): ?>
                                    <option value="">Seleccione</option>
<?php endif; ?>
<?php foreach ($marcas as $m): ?>
                                    <option value="<?php echo (int)$m->idmar; ?>"<?php echo (int)$m->idmar === (int)$p->idmar ? ' selected' : ''; ?>><?php echo e($m->nomarc); ?><?php echo (int)$m->state === 1 ? '' : ' (dada de baja)'; ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="prdcate">Categoría <span class="req">*</span></label>
                                <select id="prdcate" name="prdcate" required>
<?php if ($esNuevo): ?>
                                    <option value="">Seleccione</option>
<?php endif; ?>
<?php foreach ($categorias as $c): ?>
                                    <option value="<?php echo (int)$c->idcate; ?>"<?php echo (int)$c->idcate === (int)$p->idcate ? ' selected' : ''; ?>><?php echo e($c->nocate); ?><?php echo (int)$c->state === 1 ? '' : ' (dada de baja)'; ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="fila-campos">
                            <div>
                                <label for="prdmod">Modelo <span class="req">*</span></label>
                                <input type="text" id="prdmod" name="prdmod" value="<?php echo e($p->modelo); ?>" placeholder="ej. V14 IIL" required>
                            </div>
                            <div>
                                <label for="prdpes">Peso del producto <span class="req">*</span></label>
                                <input type="text" id="prdpes" name="prdpes" value="<?php echo e($p->peso); ?>" placeholder="ej. 1.6 kg" required>
                            </div>
                        </div>
                        <label for="prdgar">Meses de garantía de la marca <span class="req">*</span></label>
                        <select id="prdgar" name="prdgar" required>
<?php foreach ($opcionesGarantia as $meses): ?>
                            <option value="<?php echo $meses; ?>"<?php echo $meses === (int)$p->garantia_meses ? ' selected' : ''; ?>><?php echo $meses === 0 ? 'Sin garantía' : $meses . ' meses'; ?></option>
<?php endforeach; ?>
                        </select>
                    </section>
                </form>
<?php if (puede('catalogos')) { include __DIR__ . '/../../backend/modal/md_marc.php'; } ?>
