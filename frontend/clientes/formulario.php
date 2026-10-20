<?php
// formulario de cliente en tarjetas, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define:
//   $cliente    fila de clientes a editar, o null si es nuevo
//   $historial  (solo editar) compras, total y ultima compra del cliente
if (!function_exists('csrf_campo')) {
    exit;
}

$esNuevo = empty($cliente);
$c = $cliente ?: (object)['idcli' => 0, 'tipd' => 'DNI', 'nudoc' => '', 'nocl' => '', 'apcl' => '', 'telfcl' => '',
                           'emailcl' => '', 'acepta_promos' => 0, 'acepta_promos_on' => null];
$tipo = strtoupper((string)$c->tipd) === 'RUC' ? 'RUC' : 'DNI';
?>
<?php
$cabecera = [
    'titulo'    => $esNuevo ? 'Nuevo cliente' : 'Editar cliente',
    'subtitulo' => $esNuevo ? 'Registra a una persona (DNI) o a una empresa (RUC)' : 'Actualiza los datos del cliente',
    'form'      => 'form-cliente',
    'boton'     => $esNuevo ? 'add_customer' : 'upd_customer',
    'texto'     => $esNuevo ? 'Guardar cliente' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-cliente" action="" method="POST" autocomplete="off" class="tarjetas-form">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="clid" value="<?php echo (int)$c->idcli; ?>">
<?php endif; ?>

                    <section class="tarjeta-form">
                        <h2><span class="las la-id-card"></span> Documento</h2>

                        <div class="fila-campos">
                            <div>
                                <label for="tipcl">Tipo de documento <span class="req">*</span></label>
                                <select id="tipcl" name="tipcl" required>
                                    <option value="DNI"<?php echo $tipo === 'DNI' ? ' selected' : ''; ?>>DNI (persona)</option>
                                    <option value="RUC"<?php echo $tipo === 'RUC' ? ' selected' : ''; ?>>RUC (empresa)</option>
                                </select>
                            </div>
                            <div>
                                <label for="numcl">Número <span class="req">*</span></label>
                                <input type="text" id="numcl" name="numcl" value="<?php echo e($c->nudoc); ?>" inputmode="numeric" maxlength="<?php echo $tipo === 'RUC' ? 11 : 8; ?>" placeholder="<?php echo $tipo === 'RUC' ? '11 dígitos' : '8 dígitos'; ?>" required>
                            </div>
                        </div>
                        <p class="nota"><span class="las la-info-circle"></span> Con DNI se emiten boletas; con RUC, facturas. El RUC se valida con su dígito verificador.</p>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-user"></span> Datos del cliente</h2>

                        <label for="nomcl" id="etiqueta-nombre"><?php echo $tipo === 'RUC' ? 'Razón social' : 'Nombres'; ?> <span class="req">*</span></label>
                        <input type="text" id="nomcl" name="nomcl" value="<?php echo e($c->nocl); ?>" maxlength="150" placeholder="<?php echo $tipo === 'RUC' ? 'ej. Tiendas de Cómputo EIRL' : 'ej. Rosa María'; ?>" required>

                        <div id="campo-apellidos"<?php echo $tipo === 'RUC' ? ' hidden' : ''; ?>>
                            <label for="apecl">Apellidos</label>
                            <input type="text" id="apecl" name="apecl" value="<?php echo e($c->apcl); ?>" maxlength="60" placeholder="ej. Medina Paz">
                        </div>
                    </section>

                    <section class="tarjeta-form ancho-completo">
                        <h2><span class="las la-address-book"></span> Contacto</h2>

                        <div class="fila-campos">
                            <div>
                                <label for="telcl">Teléfono celular</label>
                                <input type="text" id="telcl" name="telcl" value="<?php echo e($c->telfcl); ?>" inputmode="tel" maxlength="9" placeholder="9 dígitos (opcional)">
                            </div>
                            <div>
                                <label for="emailcl">Correo electrónico</label>
                                <input type="email" id="emailcl" name="emailcl" value="<?php echo e($c->emailcl); ?>" maxlength="100" placeholder="ej. rosa.medina@gmail.com (opcional)">
                            </div>
                        </div>

                        <label class="casilla">
                            <input type="checkbox" id="promos" name="promos" value="1"<?php echo (int)$c->acepta_promos === 1 ? ' checked' : ''; ?>>
                            El cliente acepta recibir promociones y novedades por correo
                        </label>
                        <p class="nota"><span class="las la-info-circle"></span>
<?php if ((int)$c->acepta_promos === 1 && $c->acepta_promos_on): ?>
                            Autorizó el <?php echo date('d/m/Y', strtotime($c->acepta_promos_on)); ?>. Si retira su permiso, desmarca la casilla (Ley 29733).
<?php else: ?>
                            Márcalo solo si el cliente lo autoriza; se guarda la fecha y puede retirarlo cuando quiera (Ley 29733).
<?php endif; ?>
                        </p>
                    </section>

<?php if (!$esNuevo && !empty($historial)): ?>
                    <section class="tarjeta-form ancho-completo">
                        <h2><span class="las la-history"></span> Historial de compras</h2>
                        <div class="cifras-cliente">
                            <div><b><?php echo (int)$historial->compras; ?></b><small>compras</small></div>
                            <div><b>S/ <?php echo number_format((float)$historial->total, 2); ?></b><small>total comprado</small></div>
                            <div><b><?php echo $historial->ultima ? date('d/m/Y', strtotime($historial->ultima)) : '—'; ?></b><small>última compra</small></div>
                        </div>
                        <p class="nota"><span class="las la-info-circle"></span> Solo ventas válidas emitidas con el documento de este cliente.</p>
                    </section>
<?php endif; ?>
                </form>
