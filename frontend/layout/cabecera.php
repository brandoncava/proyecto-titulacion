<?php
// Inicio comun de las paginas del panel: <head>, loader, sidebar, cabecera y titulo.
// La pagina define antes de incluirlo:
//   $seccion  clave del menu que queda marcado (ver $MENU)
//   $migas    ruta que se muestra bajo el titulo, ej. 'Productos / Nuevo'
//   $tablas   true si la pagina usa DataTables (opcional)
// Se cierra con layout/pie.php.
if (!isset($seccion)) {
    exit;
}

$tablas = $tablas ?? false;

// clave => [permiso, ruta desde frontend/, icono, texto]
$MENU = [
    'dashboard'   => ['reportes',    'administrador/escritorio.php', 'la-home',          'Dashboard'],
    'productos'   => ['productos',   'productos/mostrar.php',        'la-shopping-cart', 'Productos'],
    'categorias'  => ['catalogos',   'categorias/mostrar.php',       'la-paperclip',     'Categorias'],
    'marcas'      => ['catalogos',   'marcas/mostrar.php',           'la-tags',          'Marcas'],
    'accesos'     => ['usuarios',    'accesos/mostrar.php',          'la-user-friends',  'Accesos'],
    'clientes'    => ['clientes',    'clientes/mostrar.php',         'la-user-friends',  'Clientes'],
    'proveedores' => ['proveedores', 'proveedores/mostrar.php',      'la-user-friends',  'Proveedores'],
    'ventas'      => ['ventas',      'ventas/venta.php',             'la-money-bill',    'Ventas'],
    'compras'     => ['compras',     'compra/mostrar.php',           'la-store',         'Compras'],
    'series'      => ['series',      'series/mostrar.php',           'la-file-invoice',  'Series'],
    'garantias'   => ['garantias',   'garantias/mostrar.php',        'la-tools',         'Garantías'],
    'reportes'    => ['reportes',    'reportes/ventas.php',          'la-chart-bar',     'Reportes'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>CK COMPUTERS</title>
    <link rel="stylesheet" href="../../backend/css/admin.css">
    <link rel="stylesheet" href="../../backend/css/loader.css">
    <link rel="icon" type="image/svg+xml" href="../../backend/img/ck-icono.svg">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../../backend/css/font.css">
<?php if ($tablas): ?>
    <link rel="stylesheet" type="text/css" href="../../backend/css/datatable.css">
    <link rel="stylesheet" type="text/css" href="../../backend/css/buttonsdataTables.css">
    <link rel="stylesheet" type="text/css" href="../../backend/css/tablas.css">
<?php endif; ?>
</head>
<body>
    <div class="loader-container">
        <div class="load_animation">
            <ion-icon name="bag-handle-outline" class="animation"></ion-icon>
        </div>
    </div>
    <input type="checkbox" id="menu-toggle">
    <!-- boton redondo en el borde del menu: pliega/despliega en escritorio -->
    <label for="menu-toggle" class="boton-plegar" title="Plegar o desplegar el menú">
        <span class="las la-angle-left"></span>
    </label>
    <div class="sidebar">
        <div class="side-header">
            <h3 class="logo">
                <img class="logo-completo" src="../../backend/img/cklogo.svg" alt="CK Computer's">
                <span class="logo-icono" aria-hidden="true"></span>
            </h3>
        </div>

        <div class="side-content">
            <div class="side-menu">
                <ul>
<?php foreach ($MENU as $clave => [$permiso, $ruta, $icono, $texto]): ?>
<?php if (puede($permiso)): ?>
                    <li>
                        <a href="../<?php echo $ruta; ?>"<?php echo $clave === $seccion ? ' class="active"' : ''; ?>>
                            <span class="las <?php echo $icono; ?>"></span>
                            <small><?php echo $texto; ?></small>
                        </a>
                    </li>
<?php endif; ?>
<?php endforeach; ?>
                </ul>
            </div>

            <!-- tarjeta del usuario al pie del menu -->
            <div class="perfil">
                <a class="perfil-salir" href="../salir.php" title="Cerrar sesión" onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
                    <span class="las la-sign-out-alt"></span>
                </a>
                <div class="perfil-foto bg-img" style="background-image: url(../../backend/img/user13.png)"></div>
                <div class="perfil-datos">
                    <h4><?php echo e($_SESSION['username']); ?></h4>
                    <small><?php echo e(nombre_rol(rol_actual())); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">

        <header>
            <div class="header-content">
                <!-- en celular el menu esta fuera de pantalla: se abre desde aqui -->
                <label for="menu-toggle" class="menu-movil" title="Abrir el menú">
                    <span class="las la-bars"></span>
                </label>

                <div class="saludo">
                    <h1>Bienvenido <strong><?php echo e($_SESSION['nombre']); ?></strong></h1>
                    <small>Home / <?php echo e($migas); ?></small>
                </div>

            </div>
        </header>

        <main>
