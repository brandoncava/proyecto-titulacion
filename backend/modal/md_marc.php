<!--Ventana Modal-->
<form method="POST" >
    <input type="checkbox" id="btns-modals">
    <div class="container-modal">
        <div class="content-modal">
            <h2>Nueva marca</h2>
            <hr>
            <label for="email"><b>Marca del producto</b></label><span class="badge-warning">*</span>

            <textarea  name="trat" id="trat" required placeholder="Write something.." style="height:200px"></textarea>

          
            

            <input type="button" class="registerbtn" name="submit" value="Guardar" onclick="marca();"> 
        </div>
        <label for="btns-modals" class="cerrar-modal"></label>
    </div>
    </form>
<!--Fin de Ventana Modal-->