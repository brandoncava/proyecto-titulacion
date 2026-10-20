<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('garantias');

require '../../backend/config/Conexion.php';

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
                <p class="alert alert-warning">No se encontró la orden de garantía.</p>
<?php else: ?>

                <div class="cabecera-gar">
                    <div>
                        <h2><?php echo e(formato_garantia($g->idgar)); ?></h2>
                        <span class="estado-gar e-<?php echo e(strtolower(str_replace(' ', '-', $g->estado))); ?>"><?php echo e(GARANTIA_ESTADOS[$g->estado]); ?></span>
                        <span class="estado-gar <?php echo $g->vigente ? 'vigente' : 'vencida'; ?>"><?php echo $g->vigente ? 'Garantía vigente' : 'Garantía vencida'; ?> hasta <?php echo date('d/m/Y', strtotime($g->vence)); ?></span>
                    </div>
                    <div>
                        <a class="boton-elegir" href="orden.php?id=<?php echo (int)$g->idgar; ?>"><span class="las la-print"></span> Imprimir orden</a>
                        <a class="boton-elegir secundario" href="mostrar.php">Volver</a>
                    </div>
                </div>

                <!-- linea de estados -->
                <ol class="pasos-gar">
<?php $pasado = true; foreach (GARANTIA_ESTADOS as $clave => $texto): ?>
                    <li class="<?php echo $clave === $g->estado ? 'actual' : ($pasado ? 'hecho' : ''); ?>"><?php echo e($texto); ?></li>
<?php if ($clave === $g->estado) { $pasado = false; } endforeach; ?>
                </ol>

                <div class="fichas-gar">
                    <div class="ficha">
                        <h3>Cliente</h3>
                        <p><b><?php echo e($g->cliente_nombre); ?></b></p>
                        <p>Documento: <?php echo e($g->cliente_doc ?: '—'); ?></p>
                        <p>Teléfono: <?php echo e($g->cliente_tel ?: '—'); ?></p>
                    </div>
                    <div class="ficha">
                        <h3>Producto</h3>
                        <p><b><?php echo e($g->nomprd); ?></b></p>
                        <p>N° de serie: <?php echo e($g->numero_serie ?: '—'); ?></p>
                        <p>Compra: <?php echo e($g->comprobante ?: 'sin comprobante'); ?> del <?php echo date('d/m/Y', strtotime($g->fecha_compra)); ?></p>
                    </div>
                    <div class="ficha">
                        <h3>Recepción</h3>
                        <p>Ingreso: <?php echo date('d/m/Y H:i', strtotime($g->fecha_ingreso)); ?> por <?php echo e($g->recibido_por); ?></p>
                        <p>Estado físico: <?php echo e($g->estado_fisico); ?></p>
                        <p>Accesorios: <?php echo e($g->accesorios ?: '—'); ?></p>
                    </div>
                    <div class="ficha ancha">
                        <h3>Falla reportada</h3>
                        <p><?php echo nl2br(e($g->falla)); ?></p>
                    </div>
                    <div class="ficha">
                        <h3>Trámite con el proveedor (RMA)</h3>
                        <p>Proveedor / servicio técnico: <?php echo e($g->nomprv ?: '—'); ?></p>
                        <p>Código RMA / guía: <?php echo e($g->rma_codigo ?: '—'); ?></p>
                        <p>Fecha de envío: <?php echo $g->rma_fecha_envio ? date('d/m/Y', strtotime($g->rma_fecha_envio)) : '—'; ?></p>
                    </div>
                    <div class="ficha">
                        <h3>Resultado</h3>
                        <p><b><?php echo e($g->resultado ?: 'Pendiente'); ?></b></p>
                        <p><?php echo e($g->resultado_detalle ?: ''); ?></p>
                        <p>Entregado: <?php echo $g->fecha_entrega ? date('d/m/Y H:i', strtotime($g->fecha_entrega)) : '—'; ?></p>
                    </div>
                </div>

<?php if ($siguiente): ?>
                <form action="" method="POST" autocomplete="off" class="records avanzar-gar">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idgar" value="<?php echo (int)$g->idgar; ?>">
                    <input type="hidden" name="estado_actual" value="<?php echo e($g->estado); ?>">
                    <h3>Siguiente paso: <?php echo e(GARANTIA_ESTADOS[$siguiente]); ?></h3>

<?php if ($siguiente === 'Enviado'): ?>
                    <label for="idprov"><b>Proveedor o servicio técnico de la marca</b></label><span class="badge-warning">*</span>
                    <select name="idprov" id="idprov" required>
                        <option value="">Seleccione</option>
<?php foreach ($proveedores as $p): ?>
                        <option value="<?php echo (int)$p->idprov; ?>"><?php echo e($p->nomprv); ?></option>
<?php endforeach; ?>
                    </select>
                    <label for="rma_codigo"><b>Código RMA o N° de guía</b></label>
                    <input type="text" name="rma_codigo" id="rma_codigo" maxlength="50" placeholder="El que entrega el proveedor o la empresa de envío">
                    <label for="rma_fecha_envio"><b>Fecha de envío</b></label><span class="badge-warning">*</span>
                    <input type="date" name="rma_fecha_envio" id="rma_fecha_envio" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
<?php elseif ($siguiente === 'Resuelto'): ?>
                    <label for="resultado"><b>Respuesta de la marca</b></label><span class="badge-warning">*</span>
                    <select name="resultado" id="resultado" required>
                        <option value="">Seleccione</option>
<?php foreach (GARANTIA_RESULTADOS as $r): ?>
                        <option value="<?php echo $r; ?>"><?php echo $r === 'Rechazado' ? 'Rechazado (no procede la garantía)' : $r; ?></option>
<?php endforeach; ?>
                    </select>
                    <label for="resultado_detalle"><b>Detalle de la respuesta</b></label><span class="badge-warning">*</span>
                    <input type="text" name="resultado_detalle" id="resultado_detalle" maxlength="255" required placeholder="ej. se cambió la placa / envían equipo nuevo / golpe no cubierto">
<?php endif; ?>
                    <label for="comentario"><b>Comentario</b></label>
                    <input type="text" name="comentario" id="comentario" maxlength="255" placeholder="Opcional">

                    <button type="submit" name="avanzar_garantia" class="registerbtn" onclick="return confirm('¿Pasar la orden a: <?php echo e(GARANTIA_ESTADOS[$siguiente]); ?>?');">
                        Pasar a: <?php echo e(GARANTIA_ESTADOS[$siguiente]); ?>
                    </button>
                </form>
<?php endif; ?>

                <div class="records historial-gar">
                    <h3>Historial</h3>
                    <table width="100%">
                        <thead><tr><th>Fecha</th><th>Estado</th><th>Detalle</th><th>Usuario</th></tr></thead>
                        <tbody>
<?php foreach ($historial as $h): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($h->fecha)); ?></td>
                                <td><?php echo e(GARANTIA_ESTADOS[$h->estado] ?? $h->estado); ?></td>
                                <td><?php echo e($h->comentario); ?></td>
                                <td><?php echo e($h->nombre); ?></td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_garantia_estado.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
