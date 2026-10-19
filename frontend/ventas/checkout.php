<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas / Finalizar pago';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">

                <div class="input-block">
    
           <?php
    require_once('../../backend/config/Conexion.php');
    $user_id = $_SESSION['id'];
      $cart_grand_total = 0;
      $select_cart_items = $connect->prepare("SELECT cart.idv, usuarios.id, usuarios.nombre, productos.precio,productos.stock,productos.foto,productos.idprod, productos.codpro, productos.nomprd, cart.name, cart.price, cart.quantity FROM cart INNER JOIN usuarios ON cart.user_id = usuarios.id INNER JOIN productos ON cart.idprod = productos.idprod WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['precio'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?php echo e($fetch_cart_items['name']); ?> <span>(<?= 'S/'.$fetch_cart_items['precio'].'/- x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty"><p class="alert alert-warning">Tu carrito esta vació</p></p>';

   }
   ?>
   <?php list($c_base, $c_igv) = igv_desglosar($cart_grand_total); ?>
   <div class="grand-total">Op. gravada : <span>S/<?php echo number_format($c_base, 2); ?></span></div>
   <div class="grand-total">IGV (<?php echo IGV_TASA * 100; ?>%) : <span>S/<?php echo number_format($c_igv, 2); ?></span></div>
   <div class="grand-total">Total (incluye IGV) : <span>S/<?php echo number_format($cart_grand_total, 2); ?></span></div>
   
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
  
    <label for="doctipo"><b>Documento del cliente</b></label>
    <select name="doctipo" id="doctipo">
        <option value="">Sin documento (solo boleta de hasta S/ 700)</option>
        <option value="DNI">DNI &rarr; Boleta</option>
        <option value="RUC">RUC &rarr; Factura</option>
    </select>

    <input type="text" name="docnum" id="docnum" inputmode="numeric" maxlength="11" placeholder="Número de documento" disabled>

    <label for="nomcl"><b>Nombre o razón social del cliente</b></label>
    <input type="text" name="nomcl" id="nomcl" maxlength="150" placeholder="Solo si el cliente es nuevo; si ya existe se usa el registrado">

    <p id="aviso-comprobante" style="margin:10px 0;font-size:15px;">Se emitirá: <b id="tipo-emitido">Boleta</b></p>

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
    include_once '../../backend/php/add_check.php'
?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        var total = <?php echo json_encode(round((float)$cart_grand_total, 2)); ?>;
        var limite = <?php echo json_encode(LIMITE_BOLETA_SIN_DNI); ?>;
        var tipo = document.getElementById('doctipo');
        var num = document.getElementById('docnum');
        var nom = document.getElementById('nomcl');
        var emitido = document.getElementById('tipo-emitido');
        var aviso = document.getElementById('aviso-comprobante');

        function actualizar() {
            var t = tipo.value;
            num.disabled = (t === '');
            num.required = (t !== '');
            if (t === '') { num.value = ''; }
            num.maxLength = (t === 'RUC') ? 11 : 8;
            num.placeholder = (t === 'RUC') ? 'RUC (11 dígitos)' : (t === 'DNI' ? 'DNI (8 dígitos)' : 'Número de documento');
            if (num.value.length > num.maxLength) { num.value = num.value.slice(0, num.maxLength); }
            emitido.textContent = (t === 'RUC') ? 'Factura' : 'Boleta';
            var falta = (t === '' && total > limite);
            aviso.style.color = falta ? '#B42318' : '';
            if (falta) {
                aviso.innerHTML = 'El total supera S/ ' + limite.toFixed(2) + ': ingresa el <b>DNI</b> del cliente (o su <b>RUC</b> para factura).';
            } else {
                aviso.innerHTML = 'Se emitirá: <b id="tipo-emitido">' + emitido.textContent + '</b>';
                emitido = document.getElementById('tipo-emitido');
            }
        }

        num.addEventListener('input', function () { num.value = num.value.replace(/\D/g, ''); });
        tipo.addEventListener('change', actualizar);
        actualizar();
    })();
    </script>
</body>
</html>
