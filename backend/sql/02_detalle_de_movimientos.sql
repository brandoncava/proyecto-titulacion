-- detalle linea por linea de ventas y compras

CREATE TABLE IF NOT EXISTS `orders_detalle` (
  `iddet`    INT(11)        NOT NULL AUTO_INCREMENT,
  `idord`    INT(11)        NOT NULL,
  `idprod`   INT(11)        NOT NULL,
  `nombre`   VARCHAR(255)   NOT NULL COMMENT 'Nombre del producto al momento de la venta',
  `precio`   DECIMAL(10,2)  NOT NULL COMMENT 'Precio histórico, no el actual',
  `cantidad` INT(11)        NOT NULL,
  `subtotal` DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (`iddet`),
  KEY `idx_orders_detalle_orden`    (`idord`),
  KEY `idx_orders_detalle_producto` (`idprod`),
  CONSTRAINT `orders_detalle_ibfk_1` FOREIGN KEY (`idord`)  REFERENCES `orders` (`idord`),
  CONSTRAINT `orders_detalle_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders_purchase_detalle` (
  `iddet`    INT(11)        NOT NULL AUTO_INCREMENT,
  `idordpur` INT(11)        NOT NULL,
  `idprod`   INT(11)        NOT NULL,
  `nombre`   VARCHAR(255)   NOT NULL,
  `precio`   DECIMAL(10,2)  NOT NULL,
  `cantidad` INT(11)        NOT NULL,
  `subtotal` DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (`iddet`),
  KEY `idx_opd_orden`    (`idordpur`),
  KEY `idx_opd_producto` (`idprod`),
  CONSTRAINT `opd_ibfk_1` FOREIGN KEY (`idordpur`) REFERENCES `orders_purchase` (`idordpur`),
  CONSTRAINT `opd_ibfk_2` FOREIGN KEY (`idprod`)   REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
