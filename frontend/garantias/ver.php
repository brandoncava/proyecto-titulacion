<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('garantias');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare(
    'SELECT g.*, p.nomprd, p.codpro, pr.nomprv, u.nombre AS recibido_por
       FROM garantias g
       INNER JOIN productos p ON p.idprod = g.idprod
       LEFT JOIN proveedores pr ON pr.idprov = g.idprov
       INNER JOIN usuarios u ON u.id = g.user_id
      WHERE g.idgar = ?'
);
$b->execute([$id]);
$g = $b->fetch();

$historial = [];
$siguiente = null;
$proveedores = [];
if ($g) {
    $h = $connect->prepare(
        'SELECT h.estado, h.comentario, h.fecha, u.nombre FROM garantias_historial h
           INNER JOIN usuarios u ON u.id = h.user_id WHERE h.idgar = ? ORDER BY h.fecha, h.idhis'
    );
    $h->execute([$id]);
    $historial = $h->fetchAll();
    $siguiente = garantia_siguiente_estado($g->estado);
    if ($siguiente === 'Enviado') {
        $proveedores = $connect->query('SELECT idprov, nomprv FROM proveedores WHERE state = 1 ORDER BY nomprv')->fetchAll();
    }
}

$seccion = 'garantias';
$migas = 'Garantías / ' . ($g ? formato_garantia($g->idgar) : 'Orden');
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$g): ?>
                <p class="alert">No se encontró la orden de garantía.</p>
<?php else: ?>

<?php
$fecha  = fn($f, $hora = false) => $f ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f)) : '—';
$claseEstado = GARANTIA_CHIP[$g->estado] ?? '';
?>
                <div class="cabecera-form">
                    <div>
                        <h1><?php echo e(formato_garantia($g->idgar)); ?></h1>
                        <div class="chips-gar">
                            <span class="chip <?php echo $claseEstado; ?>"><?php echo e(GARANTIA_ESTADOS[$g->estado]); ?></span>
                            <span class="chip <?php echo $g->vigente ? 'ok' : 'peligro'; ?>"><?php echo $g->vigente ? 'Garantía vigente' : 'Garantía vencida'; ?> hasta <?php echo $fecha($g->vence); ?></span>
                        </div>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="mostrar.php" class="btn-secundario">Volver</a>
                        <a href="orden.php?id=<?php echo (int)$g->idgar; ?>" class="btn-primario"><span class="las la-print"></span> Imprimir orden</a>
                    </div>
                </div>

                <!-- avance de la orden -->
                <ol class="pasos-gar">
<?php $pasado = true; foreach (GARANTIA_ESTADOS as $clave => $texto): ?>
<?php $clase = $clave === $g->estado ? ($siguiente ? 'actual' : 'hecho') : ($pasado ? 'hecho' : ''); ?>
                    <li class="<?php echo $clase; ?>">
                        <span class="paso-punto"><?php echo $clase === 'hecho' ? '<span class="las la-check"></span>' : ''; ?></span>
                        <span class="paso-texto"><?php echo e($texto); ?></span>
                    </li>
<?php if ($clave === $g->estado) { $pasado = false; } endforeach; ?>
                </ol>

                <div class="gar-layout">
                    <div class="gar-principal">
                        <section class="tarjeta-form">
                            <h2><span class="las la-clipboard-list"></span> Datos de la orden</h2>
                            <div class="datos-gar">
                                <dl>
                                    <dt>Cliente</dt>
                                    <dd><b><?php echo e($g->cliente_nombre); ?></b></dd>
                                    <dt>Documento</dt>
                                    <dd><?php echo e($g->cliente_doc ?: '—'); ?></dd>
                                    <dt>Teléfono</dt>
                                    <dd><?php echo e($g->cliente_tel ?: '—'); ?></dd>
                                </dl>
                                <dl>
                                    <dt>Producto</dt>
                                    <dd><b><?php echo e($g->nomprd); ?></b></dd>
                                    <dt>N° de serie</dt>
                                    <dd><?php echo e($g->numero_serie ?: '—'); ?></dd>
                                    <dt>Comprobante de compra</dt>
                                    <dd><?php echo e($g->comprobante ?: 'Sin comprobante'); ?> · <?php echo $fecha($g->fecha_compra); ?></dd>
                                </dl>
                                <dl>
                                    <dt>Ingreso</dt>
                                    <dd><?php echo $fecha($g->fecha_ingreso, true); ?> · <?php echo e($g->recibido_por); ?></dd>
                                    <dt>Estado físico</dt>
                                    <dd><?php echo e($g->estado_fisico); ?></dd>
                                    <dt>Accesorios</dt>
                                    <dd><?php echo e($g->accesorios ?: '—'); ?></dd>
                                </dl>
                            </div>
                            <div class="falla-gar">
                                <span class="etiqueta-dato">Falla reportada</span>
                                <p><?php echo nl2br(e($g->falla)); ?></p>
                            </div>
                        </section>

                        <section class="tarjeta-form">
                            <h2><span class="las la-shipping-fast"></span> Trámite con la marca</h2>
                            <div class="datos-gar">
                                <dl>
                                    <dt>Proveedor / servicio técnico</dt>
                                    <dd><?php echo e($g->nomprv ?: '—'); ?></dd>
                                    <dt>Código RMA / guía</dt>
                                    <dd><?php echo e($g->rma_codigo ?: '—'); ?></dd>
                                </dl>
                                <dl>
                                    <dt>Fecha de envío</dt>
                                    <dd><?php echo $fecha($g->rma_fecha_envio); ?></dd>
                                    <dt>Entregado al cliente</dt>
                                    <dd><?php echo $fecha($g->fecha_entrega, true); ?></dd>
                                </dl>
                                <dl>
                                    <dt>Respuesta de la marca</dt>
                                    <dd><b><?php echo e($g->resultado ?: 'Pendiente'); ?></b></dd>
<?php if ($g->resultado_detalle): ?>
                                    <dt>Detalle</dt>
                                    <dd><?php echo e($g->resultado_detalle); ?></dd>
<?php endif; ?>
                                </dl>
                            </div>
                        </section>
                    </div>

                    <div class="gar-lateral">
<?php if ($siguiente): ?>
                        <form action="" method="POST" autocomplete="off" class="tarjeta-form">
                            <?php echo csrf_campo(); ?>
                            <input type="hidden" name="idgar" value="<?php echo (int)$g->idgar; ?>">
                            <input type="hidden" name="estado_actual" value="<?php echo e($g->estado); ?>">
                            <h2><span class="las la-arrow-circle-right"></span> Siguiente paso</h2>
                            <p class="siguiente-gar"><?php echo e(GARANTIA_ESTADOS[$siguiente]); ?></p>

<?php if ($siguiente === 'Enviado'): ?>
                            <label for="idprov">Proveedor o servicio técnico <span class="req">*</span></label>
                            <select name="idprov" id="idprov" required>
                                <option value="">Seleccione</option>
<?php foreach ($proveedores as $p): ?>
                                <option value="<?php echo (int)$p->idprov; ?>"><?php echo e($p->nomprv); ?></option>
<?php endforeach; ?>
                            </select>
                            <label for="rma_codigo">Código RMA o N° de guía</label>
                            <input type="text" name="rma_codigo" id="rma_codigo" maxlength="50" placeholder="Lo entrega el proveedor o la agencia">
                            <label for="rma_fecha_envio">Fecha de envío <span class="req">*</span></label>
                            <input type="date" name="rma_fecha_envio" id="rma_fecha_envio" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
<?php elseif ($siguiente === 'Resuelto'): ?>
                            <label for="resultado">Respuesta de la marca <span class="req">*</span></label>
                            <select name="resultado" id="resultado" required>
                                <option value="">Seleccione</option>
<?php foreach (GARANTIA_RESULTADOS as $r): ?>
                                <option value="<?php echo $r; ?>"><?php echo $r === 'Rechazado' ? 'Rechazado (no procede la garantía)' : $r; ?></option>
<?php endforeach; ?>
                            </select>
                            <label for="resultado_detalle">Detalle de la respuesta <span class="req">*</span></label>
                            <input type="text" name="resultado_detalle" id="resultado_detalle" maxlength="255" required placeholder="ej. se cambió la placa">
<?php endif; ?>
                            <label for="comentario">Comentario</label>
                            <input type="text" name="comentario" id="comentario" maxlength="255" placeholder="Opcional">

                            <button type="submit" name="avanzar_garantia" class="btn-primario btn-ancho" onclick="return confirm('¿Pasar la orden a: <?php echo e(GARANTIA_ESTADOS[$siguiente]); ?>?');">
                                Pasar a este paso
                            </button>
                        </form>
<?php endif; ?>

                        <section class="tarjeta-form">
                            <h2><span class="las la-history"></span> Historial</h2>
                            <ol class="linea-tiempo">
<?php foreach (array_reverse($historial) as $h): ?>
                                <li>
                                    <b><?php echo e(GARANTIA_ESTADOS[$h->estado] ?? $h->estado); ?></b>
                                    <small><?php echo $fecha($h->fecha, true); ?> · <?php echo e($h->nombre); ?></small>
<?php if ($h->comentario): ?>
                                    <p><?php echo e($h->comentario); ?></p>
<?php endif; ?>
                                </li>
<?php endforeach; ?>
                            </ol>
                        </section>
                    </div>
                </div>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_garantia_estado.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
