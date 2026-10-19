-- 10: una sola serie activa por comprobante
-- Si ya habia varias activas, se deja la mas antigua de cada tipo (la que usaba la venta).
-- Ejecutar DESPUES del 09.

UPDATE `series` s
  JOIN (SELECT `tipc`, MIN(`idserie`) AS `idserie`
          FROM `series`
         WHERE `state` = 1
         GROUP BY `tipc`) a ON a.`tipc` = s.`tipc`
   SET s.`state` = IF(s.`idserie` = a.`idserie`, 1, 0);
