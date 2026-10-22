// carrito sin recargar la pagina (lista de productos y carrito, de ventas y de compras):
// - "Agregar" (form .form-agregar) pone 1 unidad y la fila pasa al SelectorCantidad
// - SelectorCantidad (frontend/comun/selector_cantidad.php): "- n +" guarda la cantidad al momento;
//   con 0 quita la linea. En compras, el campo de costo de la linea tambien se guarda solo
// - si la fila tiene subtotal ([data-subtotal]) y la pagina total (#total-carrito), se recalculan
// Los manejadores responden json cuando reciben ajax=1.
(function () {
    var aviso = null, espera = null;
    var moneda = function (n) { return 'S/ ' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    function mostrarAviso(ok, texto) {
        if (!aviso) {
            aviso = document.createElement('div');
            aviso.setAttribute('role', 'status');
            document.body.appendChild(aviso);
        }
        aviso.className = 'aviso-flotante ' + (ok ? 'ok' : 'error') + ' visible';
        aviso.textContent = '';
        var icono = document.createElement('span');
        icono.className = 'las ' + (ok ? 'la-check-circle' : 'la-exclamation-circle');
        var mensaje = document.createElement('span');
        mensaje.textContent = texto;
        aviso.appendChild(icono);
        aviso.appendChild(mensaje);
        if (ok && !document.getElementById('total-carrito')) {
            var enlace = document.createElement('a');
            enlace.href = 'cart.php';
            enlace.textContent = 'Ver carrito';
            aviso.appendChild(enlace);
        }
        clearTimeout(espera);
        espera = setTimeout(function () { aviso.classList.remove('visible'); }, ok ? 3000 : 5000);
    }

    function csrf(caja) {
        var campo = caja.querySelector('input[name=csrf_token]') || document.querySelector('input[name=csrf_token]');
        return campo ? campo.value : '';
    }

    function enviar(url, datos) {
        var cuerpo = new FormData();
        Object.keys(datos).forEach(function (k) { cuerpo.append(k, datos[k]); });
        cuerpo.append('ajax', '1');
        return fetch(url, { method: 'POST', body: cuerpo, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }

    function sumarAlContador(n) {
        var contador = document.getElementById('contador-carrito');
        if (contador) { contador.textContent = Math.max(0, parseInt(contador.textContent, 10) + n); }
    }

    function partes(caja) {
        var selector = caja.querySelector('.selector-cantidad');
        return {
            form: caja.querySelector('.form-agregar'),
            selector: selector,
            campo: selector.querySelector('input'),
            sumar: selector.querySelector('.sumar'),
            max: parseInt(caja.dataset.max, 10)
        };
    }

    function cantidadActual(caja) {
        var campo = partes(caja).campo;
        return parseInt(campo.dataset.anterior || campo.value, 10) || 0;
    }

    // muestra la cantidad; con 0 vuelve el boton Agregar (solo en la lista de productos)
    function pintar(caja, cantidad) {
        var p = partes(caja);
        if (p.form) {
            p.form.hidden = cantidad > 0;
            p.selector.hidden = cantidad <= 0;
        }
        p.campo.value = cantidad;
        p.campo.dataset.anterior = cantidad;
        p.sumar.disabled = cantidad >= p.max;
    }

    // subtotal de la fila y total de la pagina (solo en el carrito)
    function recalcular(caja, cantidad) {
        var fila = caja.closest('tr');
        var subtotal = fila && fila.querySelector('[data-subtotal]');
        var total = document.getElementById('total-carrito');
        if (!subtotal || !total) { return; }

        if (cantidad < 1) {
            fila.remove();
            if (!document.querySelector('[data-subtotal]')) { location.reload(); return; }
        } else {
            var precio = parseFloat(caja.dataset.costo || fila.dataset.precio);
            subtotal.textContent = moneda(precio * cantidad);
        }
        var suma = 0;
        document.querySelectorAll('.accion-carrito').forEach(function (c) {
            var f = c.closest('tr');
            suma += parseFloat(c.dataset.costo || f.dataset.precio) * cantidadActual(c);
        });
        total.textContent = moneda(suma);
        revisarStock();
    }

    // lineas que piden mas de lo disponible: se marcan y no se deja ir a pagar hasta corregirlas
    function revisarStock() {
        var faltan = 0;
        document.querySelectorAll('tr[data-disponible]').forEach(function (fila) {
            var caja = fila.querySelector('.accion-carrito');
            var excede = caja && cantidadActual(caja) > parseInt(fila.dataset.disponible, 10);
            fila.classList.toggle('fila-sin-stock', !!excede);
            if (excede) { faltan++; }
        });
        var aviso = document.getElementById('aviso-stock');
        var pagar = document.getElementById('proceder-pago');
        if (aviso) { aviso.hidden = faltan === 0; }
        if (pagar) { pagar.disabled = faltan > 0; }
    }

    // guarda la cantidad (y en compras el costo) de la linea; con cantidad 0 la quita
    function guardar(caja, nueva, costo) {
        var p = partes(caja);
        var anterior = cantidadActual(caja);
        if (isNaN(nueva)) { pintar(caja, anterior); return; }
        if (nueva > p.max) {
            mostrarAviso(false, 'Solo hay ' + p.max + ' unidades disponibles.');
            pintar(caja, anterior);
            return;
        }
        caja.classList.add('ocupado');

        var pedido;
        if (nueva < 1) {
            pedido = fetch(caja.dataset.quitar + '?ajax=1&id=' + encodeURIComponent(caja.dataset.linea), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); });
        } else {
            var datos = { prdt: caja.dataset.linea, p_qty: nueva, update_qty: '1', csrf_token: csrf(caja) };
            if (caja.dataset.costo !== undefined) { datos.p_cost = costo || caja.dataset.costo; }
            pedido = enviar(caja.dataset.actualizar, datos);
        }

        pedido.then(function (r) {
            if (!r.ok) {
                mostrarAviso(false, r.texto || 'No se pudo guardar el cambio.');
                pintar(caja, anterior);
                return;
            }
            if (costo) { caja.dataset.costo = costo; }
            if (nueva < 1) { sumarAlContador(-1); }
            pintar(caja, Math.max(0, nueva));
            recalcular(caja, nueva);
        }).catch(function () {
            mostrarAviso(false, 'No se pudo guardar el cambio. Revisa tu conexión.');
            pintar(caja, anterior);
        }).then(function () { caja.classList.remove('ocupado'); });
    }

    // "Agregar": 1 unidad (en compras con el costo de la fila)
    document.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!form.classList.contains('form-agregar') || !window.fetch) { return; }
        ev.preventDefault();
        var caja = form.closest('.accion-carrito');
        var boton = form.querySelector('button[type=submit]');
        var datos = {};
        new FormData(form).forEach(function (v, k) { datos[k] = v; });
        datos[boton.name] = '1';
        boton.disabled = true;

        enviar(form.dataset.accion, datos).then(function (r) {
            mostrarAviso(r.ok, r.texto);
            if (!r.ok) { return; }
            caja.dataset.linea = r.idlinea;
            if (datos.p_cost) { caja.dataset.costo = datos.p_cost; }
            var contador = document.getElementById('contador-carrito');
            if (contador) { contador.textContent = r.lineas; }
            pintar(caja, r.enCarrito);
        }).catch(function () {
            mostrarAviso(false, 'No se pudo agregar. Revisa tu conexión e intenta de nuevo.');
        }).then(function () { boton.disabled = false; });
    });

    document.addEventListener('click', function (ev) {
        var boton = ev.target.closest('.selector-cantidad button');
        if (!boton) { return; }
        var caja = boton.closest('.accion-carrito');
        guardar(caja, cantidadActual(caja) + (boton.classList.contains('sumar') ? 1 : -1));
    });

    // cantidad o costo escritos a mano: se guardan al salir del campo o con Enter
    document.addEventListener('change', function (ev) {
        var caja = ev.target.closest('.accion-carrito');
        if (!caja) { return; }
        if (ev.target.matches('.selector-cantidad input')) {
            guardar(caja, parseInt(ev.target.value, 10));
        } else if (ev.target.matches('.costo-linea input')) {
            var costo = parseFloat(ev.target.value);
            if (!(costo > 0)) {
                mostrarAviso(false, 'El costo debe ser mayor que 0.');
                ev.target.value = parseFloat(caja.dataset.costo).toFixed(2);
                return;
            }
            guardar(caja, cantidadActual(caja), costo.toFixed(2));
        }
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter' && ev.target.matches('.selector-cantidad input, .costo-linea input')) {
            ev.preventDefault();
            ev.target.blur();
        }
    });
})();
