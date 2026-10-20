# CK Computers — Sistema de ventas e inventario

Sistema web para CK Computers (Trujillo): ventas, compras, productos, stock, categorías, marcas, clientes, proveedores, garantías, reportes y usuarios con perfiles.

## Funciones principales

- **Ventas** con boleta (DNI) o factura (RUC), IGV y series de comprobantes.
- **Pagos mixtos**: una venta se puede pagar con varios medios (contado, tarjeta, Yape, Plin, transferencia); Yape, Plin y transferencia piden el N° de operación.
- **Modificar venta**: la venta se carga en el carrito y, al finalizar, la original se anula y se emite un comprobante nuevo que la reemplaza.
- **Compras** con el comprobante del proveedor; se pueden **modificar** (el stock se ajusta con la diferencia) o anular.
- **Kardex**: cada entrada y salida de stock queda registrada.
- **Órdenes de garantía**: recepción del producto del cliente (con PDF), validación del plazo de garantía de cada producto y seguimiento del trámite con la marca o proveedor (RMA) en 6 estados.
- **Reportes** con filtros: ventas, compras, productos y clientes (con sus medios de pago).
- **Dashboard** del mes: ventas comparadas con el mes anterior, compras, ticket promedio, garantías en curso, ventas de los últimos 30 días, stock bajo y más vendidos.
- **Clientes** con correo opcional y autorización para recibir promociones, guardando la fecha en que la dio (Ley 29733).

Hecho en PHP + MySQL/MariaDB, sin frameworks. Genera boletas y comprobantes en PDF con FPDF.

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP 8 + MariaDB), o cualquier servidor con PHP 7.4 o superior y MySQL/MariaDB
- Apache con `mod_rewrite` activado (viene activado en XAMPP)

## Instalación

1. **Copia el proyecto** en `C:\xampp\htdocs\ckcomputers_system`.
   La carpeta debe llamarse así, porque `.htaccess` usa `RewriteBase /ckcomputers_system/`.

2. **Crea la base de datos.** En phpMyAdmin (`http://localhost/phpmyadmin`):
   - crea una base llamada `ckcomputers_db` con cotejamiento `utf8mb4_general_ci`
   - selecciónala, ve a **Importar** y sube `database/ckcomputers_db.sql` (estructura completa con todos los cambios de `backend/sql` y datos de prueba)

3. **Configura la conexión.** Copia `backend/config/config.example.php` como `backend/config/config.php` y ajusta los datos si tu MySQL no usa `root` sin contraseña:

   ```php
   return [
       'host' => 'localhost',
       'user' => 'root',
       'pass' => '',
       'name' => 'ckcomputers_db',
   ];
   ```

4. Abre **http://localhost/ckcomputers_system**.

## Usuarios de prueba

Todos los datos de la base son de prueba.

| Usuario    | Contraseña      | Perfil        | Qué puede hacer |
|------------|-----------------|---------------|-----------------|
| `admin01`  | `Admin2026!`    | Administrador | Todo: dashboard, reportes, usuarios, garantías, modificar y anular ventas y compras |
| `admin02`  | `Admin2026!`    | Administrador | Igual que `admin01` |
| `cajero01` | `Cajero2026!`   | Cajero        | Ventas, productos (ver), clientes y garantías |
| `emple01`  | `Empleado2026!` | Empleado      | Compras, productos, proveedores, categorías, marcas y garantías |

Los permisos de cada perfil se definen en `backend/config/roles.php`.

## Estructura

```
ckcomputers_system/
├── database/ckcomputers_db.sql   base de datos completa (estructura + datos de prueba)
├── frontend/                     páginas del sistema, una carpeta por módulo
│   ├── layout/                   cabecera, menú, pie y encabezado de formularios comunes a todas las páginas
│   ├── comun/                    carrito y selección de productos compartidos por ventas y compras
│   └── login.php
├── backend/
│   ├── config/                   conexión, sesión, permisos, utilidades y base de los PDF
│   ├── php/                      procesan los formularios (guardar, editar, anular...)
│   ├── css/  js/  img/
│   ├── fpdf/                     librería para los PDF
│   └── sql/                      cambios de la base ya incluidos en database/ckcomputers_db.sql
└── index.php                     redirige al login
```

## Notas

- `backend/config/config.php` no se sube al repositorio porque tiene las credenciales locales.
- Las fotos que suben los usuarios se guardan en `backend/img/subidas/`. En el repositorio solo están las de los productos de ejemplo.

## Cambios recientes (inventario, IGV y comprobantes)

- Precio de compra separado del precio de venta: el costo se escribe en la compra, sin IGV (como la factura del proveedor); `productos.precio` es el precio de venta e incluye IGV (18%).
- La venta pide el documento del cliente: RUC genera factura (serie F001); DNI o sin documento genera boleta (serie B001). Con boleta mayor a S/ 700 el DNI es obligatorio.
- Serie y correlativo en la tabla `series`; la boleta o factura en PDF muestra serie-número, documento del cliente y el desglose del IGV.
- En compras se registra el comprobante como Factura (por defecto) o Boleta, y se indica si el comprobante del proveedor incluye IGV o no.
- Control de stock: la venta no deja agregar más unidades de las que hay (el listado muestra el stock y "Sin stock"), y al confirmar se vuelve a validar y se descuenta.
- No incluye la emisión electrónica válida ante SUNAT: el PDF es la representación impresa de la venta registrada.
- Kardex: cada compra, venta, anulación y ajuste de stock se anota en `movimientos_inventario`; la venta queda enlazada con su serie (`orders.idserie`).
- Scripts de cambios: `backend/sql/01` a `13` (ya incluidos en `database/ckcomputers_db.sql`; solo hacen falta para actualizar una base anterior, en ese orden).
- Las compras se pueden anular (solo el administrador): se descuenta el stock y queda en el kardex. Se registra la serie, número y fecha del comprobante del proveedor y no se permite repetirlo.
- Módulo de series (administrador) para activar series de boleta y factura (solo una activa por comprobante); reporte de compras; el código de producto es único y el stock se valida como número.
