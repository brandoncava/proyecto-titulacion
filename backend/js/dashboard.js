// grafico de columnas "ventas de los ultimos 30 dias" del dashboard (SVG sin librerias).
// Datos en window.VENTAS_30_DIAS: [{dia, etiqueta, total, ventas}]
(function () {
    var datos = window.VENTAS_30_DIAS || [];
    var caja = document.getElementById('grafico-ventas');
    if (!caja || !datos.length) { return; }

    var NS = 'http://www.w3.org/2000/svg';
    var ALTO, M = { arriba: 22, derecha: 8, abajo: 26, izquierda: 64 };
    var moneda = function (n) { return 'S/ ' + n.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var corto = function (n) { return n >= 1000 ? 'S/ ' + (n / 1000).toLocaleString('es-PE', { maximumFractionDigits: 1 }) + ' mil' : 'S/ ' + n; };

    function el(tag, attrs, padre) {
        var e = document.createElementNS(NS, tag);
        Object.keys(attrs).forEach(function (k) { e.setAttribute(k, attrs[k]); });
        if (padre) { padre.appendChild(e); }
        return e;
    }

    // tope "redondo" del eje y (1, 2 o 5 x 10^n) con 4 divisiones
    function topeEje(max) {
        if (max <= 0) { return 100; }
        var paso = max / 4, base = Math.pow(10, Math.floor(Math.log10(paso)));
        var limpio = [1, 2, 2.5, 5, 10].map(function (f) { return f * base; }).find(function (p) { return p >= paso; });
        return limpio * 4;
    }

    // columna con la punta redondeada (4px) y la base recta
    function columna(x, y, w, h) {
        var r = Math.min(4, w / 2, h);
        return 'M' + x + ',' + (y + h) + 'V' + (y + r) + 'Q' + x + ',' + y + ' ' + (x + r) + ',' + y +
               'H' + (x + w - r) + 'Q' + (x + w) + ',' + y + ' ' + (x + w) + ',' + (y + r) + 'V' + (y + h) + 'Z';
    }

    var tip = document.createElement('div');
    tip.className = 'grafico-tip';
    tip.hidden = true;
    var tipValor = document.createElement('strong');
    var tipDetalle = document.createElement('span');
    tip.appendChild(tipValor);
    tip.appendChild(tipDetalle);
    caja.appendChild(tip);

    function dibujar() {
        var viejo = caja.querySelector('svg');
        if (viejo) { viejo.remove(); }

        var ancho = caja.clientWidth;
        ALTO = Math.max(230, caja.clientHeight);
        var w = ancho - M.izquierda - M.derecha, h = ALTO - M.arriba - M.abajo;
        var max = Math.max.apply(null, datos.map(function (d) { return d.total; }));
        var tope = topeEje(max);
        var ranura = w / datos.length;
        var barra = Math.max(2, Math.min(24, ranura - 2));
        var y = function (v) { return M.arriba + h - v / tope * h; };

        var svg = el('svg', { width: ancho, height: ALTO, viewBox: '0 0 ' + ancho + ' ' + ALTO });
        caja.insertBefore(svg, tip);

        // cuadricula y eje y
        for (var i = 0; i <= 4; i++) {
            var v = tope / 4 * i, yy = Math.round(y(v)) + 0.5;
            el('line', { x1: M.izquierda, x2: ancho - M.derecha, y1: yy, y2: yy, class: i === 0 ? 'g-base' : 'g-linea' }, svg);
            el('text', { x: M.izquierda - 10, y: yy + 4, class: 'g-eje', 'text-anchor': 'end' }, svg).textContent = corto(v);
        }

        var iMax = datos.reduce(function (a, d, j) { return d.total > datos[a].total ? j : a; }, 0);

        datos.forEach(function (d, j) {
            var x = M.izquierda + ranura * j + (ranura - barra) / 2;
            var alto = d.total / tope * h;
            var col = null;
            if (alto > 0) {
                col = el('path', { d: columna(x, y(d.total), barra, alto), class: 'g-barra' }, svg);
            }
            // fechas del eje x: cada 5 dias y el ultimo
            if (j % 5 === 0 || j === datos.length - 1) {
                el('text', { x: x + barra / 2, y: ALTO - 6, class: 'g-eje', 'text-anchor': 'middle' }, svg).textContent = d.etiqueta;
            }
            // una sola etiqueta directa: el dia de mayor venta
            if (j === iMax && d.total > 0) {
                el('text', { x: x + barra / 2, y: y(d.total) - 7, class: 'g-valor', 'text-anchor': 'middle' }, svg).textContent = corto(Math.round(d.total));
            }
            // zona de hover: toda la ranura, no solo la barra
            var zona = el('rect', { x: M.izquierda + ranura * j, y: M.arriba, width: ranura, height: h, class: 'g-zona' }, svg);
            zona.addEventListener('pointerenter', function () {
                if (col) { col.classList.add('activa'); }
                tipValor.textContent = moneda(d.total);
                tipDetalle.textContent = d.etiqueta + ' · ' + d.ventas + (d.ventas === 1 ? ' comprobante' : ' comprobantes');
                tip.hidden = false;
                var izq = Math.min(Math.max(x + barra / 2 - tip.offsetWidth / 2, 0), ancho - tip.offsetWidth);
                tip.style.left = izq + 'px';
                tip.style.top = Math.max(y(d.total) - tip.offsetHeight - 10, 0) + 'px';
            });
            zona.addEventListener('pointerleave', function () {
                if (col) { col.classList.remove('activa'); }
                tip.hidden = true;
            });
        });
    }

    var espera;
    window.addEventListener('resize', function () {
        clearTimeout(espera);
        espera = setTimeout(dibujar, 150);
    });
    dibujar();
})();
