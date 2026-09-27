-- perfiles, soft delete y tipos de datos

-- state como tinyint (1 = activo, 0 = dado de baja)
ALTER TABLE `categoria`    MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `marca`        MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `productos`    MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `proveedores`  MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `clientes`     MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE `usuarios`     MODIFY `state` TINYINT(1) NOT NULL DEFAULT 1;

-- 1=admin, 2=cajero, 3=empleado
ALTER TABLE `usuarios` MODIFY `rol` TINYINT(1) NOT NULL DEFAULT 3
  COMMENT '1=Administrador, 2=Cajero, 3=Empleado';
ALTER TABLE `clientes` MODIFY `rol` TINYINT(1) NOT NULL DEFAULT 0;

-- fechas de venta/compra de texto (dd-Mon-aaaa) a datetime
ALTER TABLE `orders`          ADD COLUMN `placed_on_new` DATETIME NULL AFTER `placed_on`;
UPDATE `orders`          SET `placed_on_new` = STR_TO_DATE(`placed_on`, '%d-%b-%Y');
ALTER TABLE `orders`          DROP COLUMN `placed_on`;
ALTER TABLE `orders`          CHANGE `placed_on_new` `placed_on` DATETIME NOT NULL;

ALTER TABLE `orders_purchase` ADD COLUMN `placed_on_new` DATETIME NULL AFTER `placed_on`;
UPDATE `orders_purchase` SET `placed_on_new` = STR_TO_DATE(`placed_on`, '%d-%b-%Y');
ALTER TABLE `orders_purchase` DROP COLUMN `placed_on`;
ALTER TABLE `orders_purchase` CHANGE `placed_on_new` `placed_on` DATETIME NOT NULL;

-- FK de orders a usuarios
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

-- quita la coma inicial de total_products
UPDATE `orders`          SET `total_products` = TRIM(LEADING ', ' FROM `total_products`);
UPDATE `orders_purchase` SET `total_products` = TRIM(LEADING ', ' FROM `total_products`);
