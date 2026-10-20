-- 11: pagos mixtos, modificar ventas/compras y ordenes de garantia
-- Ejecutar DESPUES de 10.
-- Haz un respaldo antes de ejecutar.

-- 1) pagos de cada venta: una venta puede pagarse con varios medios
CREATE TABLE `orders_pagos` (
  `idpago` int(11) NOT NULL AUTO_INCREMENT,
  `idord` int(11) NOT NULL,
  `metodo` varchar(20) NOT NULL COMMENT 'Contado, Tarjeta, Yape, Plin o Transferencia',
  `monto` decimal(10,2) NOT NULL,
  `num_operacion` varchar(30) DEFAULT NULL COMMENT 'obligatorio en Yape, Plin y Transferencia',
  PRIMARY KEY (`idpago`),
  KEY `idx_pagos_venta` (`idord`),
  KEY `idx_pagos_metodo` (`metodo`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`idord`) REFERENCES `orders` (`idord`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- las ventas anteriores se pagaron con un solo medio
INSERT INTO `orders_pagos` (`idord`, `metodo`, `monto`)
SELECT `idord`, IF(`method` IN ('Contado', 'Tarjeta'), `method`, 'Contado'), `total_price`
  FROM `orders`;

-- 2) modificar una venta = anularla y emitir otra que la reemplaza
ALTER TABLE `orders`
  ADD COLUMN `reemplaza_a` int(11) NULL AFTER `motivo_anulacion`,
  ADD KEY `idx_orders_reemplaza` (`reemplaza_a`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`reemplaza_a`) REFERENCES `orders` (`idord`);

-- 3) las compras se editan: queda la fecha de la ultima modificacion
ALTER TABLE `orders_purchase`
  ADD COLUMN `modificada_on` datetime NULL AFTER `fecha_emision`;

ALTER TABLE `movimientos_inventario`
  MODIFY `tipo` varchar(15) NOT NULL COMMENT 'Saldo inicial, Compra, Venta, Anulacion, Modificacion o Ajuste';

-- 4) meses de garantia de cada producto (los existentes quedan en 12)
ALTER TABLE `productos`
  ADD COLUMN `garantia_meses` tinyint(3) unsigned NOT NULL DEFAULT 12 AFTER `stock`;

-- 5) orden de recepcion por garantia (frente al cliente) + tramite con el proveedor (RMA)
CREATE TABLE `garantias` (
  `idgar` int(11) NOT NULL AUTO_INCREMENT,
  `idord` int(11) DEFAULT NULL COMMENT 'venta original, NULL si se vendio fuera del sistema',
  `idprod` int(11) NOT NULL,
  `idcli` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_doc` varchar(11) DEFAULT NULL,
  `cliente_tel` varchar(15) DEFAULT NULL,
  `comprobante` varchar(20) DEFAULT NULL COMMENT 'boleta o factura de la compra original',
  `fecha_compra` date NOT NULL,
  `vence` date NOT NULL,
  `vigente` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'garantia en plazo al recibir el producto',
  `numero_serie` varchar(60) DEFAULT NULL,
  `estado_fisico` varchar(255) NOT NULL,
  `accesorios` varchar(255) DEFAULT NULL,
  `falla` text NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Ingresado',
  `idprov` int(11) DEFAULT NULL COMMENT 'proveedor o servicio tecnico de la marca (RMA)',
  `rma_codigo` varchar(50) DEFAULT NULL,
  `rma_fecha_envio` date DEFAULT NULL,
  `resultado` varchar(15) DEFAULT NULL COMMENT 'Reparado, Reemplazado o Rechazado',
  `resultado_detalle` varchar(255) DEFAULT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`idgar`),
  KEY `idx_gar_estado` (`estado`),
  KEY `idx_gar_venta` (`idord`),
  KEY `idx_gar_producto` (`idprod`),
  KEY `idx_gar_cliente` (`idcli`),
  KEY `idx_gar_proveedor` (`idprov`),
  KEY `idx_gar_usuario` (`user_id`),
  CONSTRAINT `gar_ibfk_1` FOREIGN KEY (`idord`)   REFERENCES `orders` (`idord`),
  CONSTRAINT `gar_ibfk_2` FOREIGN KEY (`idprod`)  REFERENCES `productos` (`idprod`),
  CONSTRAINT `gar_ibfk_3` FOREIGN KEY (`idcli`)   REFERENCES `clientes` (`idcli`),
  CONSTRAINT `gar_ibfk_4` FOREIGN KEY (`idprov`)  REFERENCES `proveedores` (`idprov`),
  CONSTRAINT `gar_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- cada cambio de estado de la orden, con fecha y usuario
CREATE TABLE `garantias_historial` (
  `idhis` int(11) NOT NULL AUTO_INCREMENT,
  `idgar` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`idhis`),
  KEY `idx_his_garantia` (`idgar`, `fecha`),
  KEY `idx_his_usuario` (`user_id`),
  CONSTRAINT `his_ibfk_1` FOREIGN KEY (`idgar`)   REFERENCES `garantias` (`idgar`),
  CONSTRAINT `his_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
