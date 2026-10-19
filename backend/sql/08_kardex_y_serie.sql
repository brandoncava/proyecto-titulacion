-- 08: kardex de inventario y enlace de la venta con su serie
-- Ejecutar DESPUES de 05, 06 y 07.
-- Haz un respaldo antes de ejecutar.

-- 1) venta -> serie (1:N): una serie tiene muchas ventas
ALTER TABLE `orders`
  ADD COLUMN `idserie` INT(11) NULL AFTER `tipc`,
  ADD KEY `idx_orders_serie` (`idserie`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`idserie`) REFERENCES `series` (`idserie`);

UPDATE `orders` o
  JOIN `series` s ON s.`tipc` = o.`tipc` AND s.`serie` = o.`serie`
   SET o.`idserie` = s.`idserie`;

-- 2) kardex: cada cambio de stock deja una fila
CREATE TABLE `movimientos_inventario` (
  `idmov` int(11) NOT NULL AUTO_INCREMENT,
  `idprod` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipo` varchar(15) NOT NULL COMMENT 'Saldo inicial, Compra, Venta, Anulacion o Ajuste',
  `cantidad` int(11) NOT NULL COMMENT 'positiva si entra, negativa si sale',
  `stock_resultante` int(11) NOT NULL,
  `idord` int(11) DEFAULT NULL COMMENT 'venta o anulacion',
  `idordpur` int(11) DEFAULT NULL COMMENT 'compra',
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`idmov`),
  KEY `idx_mov_producto` (`idprod`, `fecha`),
  KEY `idx_mov_usuario` (`user_id`),
  KEY `idx_mov_venta` (`idord`),
  KEY `idx_mov_compra` (`idordpur`),
  CONSTRAINT `mov_ibfk_1` FOREIGN KEY (`idprod`)   REFERENCES `productos` (`idprod`),
  CONSTRAINT `mov_ibfk_2` FOREIGN KEY (`user_id`)  REFERENCES `usuarios` (`id`),
  CONSTRAINT `mov_ibfk_3` FOREIGN KEY (`idord`)    REFERENCES `orders` (`idord`),
  CONSTRAINT `mov_ibfk_4` FOREIGN KEY (`idordpur`) REFERENCES `orders_purchase` (`idordpur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 3) saldo inicial: el stock que hay hoy en cada producto
INSERT INTO `movimientos_inventario` (`idprod`, `user_id`, `tipo`, `cantidad`, `stock_resultante`, `fecha`)
SELECT `idprod`, (SELECT MIN(`id`) FROM `usuarios`), 'Saldo inicial',
       CAST(`stock` AS SIGNED), CAST(`stock` AS SIGNED), NOW()
  FROM `productos`;
