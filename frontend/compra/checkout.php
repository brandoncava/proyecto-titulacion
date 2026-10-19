<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('compras');

$seccion = 'compras';
$migas = 'Compras / Finalizar pago';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">

                <div class="input-block">
    
           <?php
    require_once('../../backend/config/Conexion.php');
    $user_id = $_SESSION['id'];
      $cart_grand_total = 0;
      $select_cart_items = $connect->prepare("SELECT cart_purchase.idcpr, cart_purchase.user_id, productos.precio,productos.stock,productos.foto,productos.idprod, productos.codpro, productos.nomprd, cart_purchase.name, cart_purchase.price, cart_purchase.quantity FROM cart_purchase INNER JOIN productos ON cart_purchase.idprod = productos.idprod WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?php echo e($fetch_cart_items['name']); ?> <span>(<?= 'S/'.$fetch_cart_items['price'].' (sin IGV) x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty"><p class="alert alert-warning">Tu carrito esta vació</p></p>';

   }
   ?>
   <?php list($c_base, $c_igv, $c_total) = igv_agregar($cart_grand_total); ?>
   <div class="grand-total">Subtotal (sin IGV) : <span>S/<?php echo number_format($c_base, 2); ?></span></div>
   <div class="grand-total">IGV (<?php echo IGV_TASA * 100; ?>%) : <span id="igv-monto">S/<?php echo number_format($c_igv, 2); ?></span></div>
   <div class="grand-total">Total : <span id="total-monto">S/<?php echo number_format($c_total, 2); ?></span></div>
   
 </div>  

            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Finalizar compra</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  
    <label for="email"><b>Proveedores</b></label><span class="badge-warning">*</span>
    <select required name="cxtprov" id="provee">
        <option value="">Seleccione</option>
       
       
    </select>

   <label for="psw"><b>Comprobante de pago</b></label><span class="badge-warning">*</span>
    <select required name="cxcom" id="cxcom">
        <option value="">Seleccione</option>
        <option value="Factura">Factura</option>
        <option value="Boleta">Boleta</option>
       
    </select>

    <label for="cxigv"><b>El comprobante del proveedor</b></label><span class="badge-warning">*</span>
    <select required name="cxigv" id="cxigv">
        <option value="1">Incluye IGV</option>
        <option value="0">No incluye IGV</option>
    </select>

    <label for="serie_doc"><b>Serie y número del comprobante</b></label><span class="badge-warning">*</span>
    <input type="text" name="serie_doc" id="serie_doc" maxlength="4" placeholder="Serie ejm: F001" style="text-transform:uppercase;" required>
    <input type="text" name="numero_doc" id="numero_doc" maxlength="8" onKeypress="if (event.keyCode < 48 || event.keyCode > 57) event.returnValue = false;" placeholder="Número ejm: 00001234" required>

    <label for="fecha_emision"><b>Fecha de emisión del comprobante</b></label><span class="badge-warning">*</span>
    <input type="date" name="fecha_emision" id="fecha_emision" max="<?php echo date('Y-m-d'); ?>" required>

    <label for="psw"><b>Método de pago</b></label><span class="badge-warning">*</span>
        <select required name="cxtcre" id="cxtcre">
            <option value="">Seleccione</option>
            <option value="Contado">Contado</option>
            <option value="Tarjeta">Tarjeta</option>
        </select>

    <hr>
   
    <button type="submit" id="validate"  name="order" class="registerbtn <?= ($cart_grand_total > 1)?'':'disabled'; ?>">Guardar</button>
    <button onclick="location.href='cart.php'" class="pabtn ">Cancelar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php
    include_once '../../backend/php/add_check_purchase.php'
?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/provee.js"></script>
    <script type="text/javascript">
    (function () {
        var igv = document.getElementById('cxigv');
        var montoIgv = document.getElementById('igv-monto');
        var montoTotal = document.getElementById('total-monto');
        var conIgv = [montoIgv.textContent, montoTotal.textContent];
        var base = <?php echo json_encode('S/' . number_format($c_base, 2)); ?>;

        igv.addEventListener('change', function () {
            if (igv.value === '1') {
                montoIgv.textContent = conIgv[0];
                montoTotal.textContent = conIgv[1];
            } else {
                montoIgv.textContent = 'S/0.00';
                montoTotal.textContent = base;
            }
        });
    })();
    </script>
</body>
</html>
