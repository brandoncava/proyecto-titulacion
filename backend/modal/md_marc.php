<?php
// ventana para crear una marca sin salir del formulario de producto.
// La abre el enlace "Nueva marca" (label for="btns-modals"); guarda con marca() de producto_form.js
?>
<input type="checkbox" id="btns-modals" class="modal-interruptor">
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="titulo-nueva-marca">
    <label for="btns-modals" class="modal-fondo" title="Cerrar"></label>
    <div class="modal-caja tarjeta-form">
        <h2><span class="las la-tags"></span> <span id="titulo-nueva-marca">Nueva marca</span></h2>
        <label for="trat">Nombre de la marca <span class="req">*</span></label>
        <input type="text" id="trat" maxlength="100" placeholder="ej. Epson"
               onkeydown="if (event.key === 'Enter') { event.preventDefault(); marca(); }">
        <p class="nota"><span class="las la-info-circle"></span> Queda elegida en el producto al guardarla.</p>
        <div class="modal-botones">
            <label for="btns-modals" class="btn-secundario">Cancelar</label>
            <button type="button" class="btn-primario" onclick="marca();"><span class="las la-save"></span> Guardar marca</button>
        </div>
    </div>
</div>
