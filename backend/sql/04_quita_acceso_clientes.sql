-- los clientes no inician sesion en el sistema: se quitan las columnas de acceso
-- (ademas username/password eran NOT NULL sin valor por defecto y el alta de
-- clientes fallaba en MySQL/MariaDB con modo estricto)

ALTER TABLE `clientes`
  DROP COLUMN `username`,
  DROP COLUMN `password`,
  DROP COLUMN `rol`;
