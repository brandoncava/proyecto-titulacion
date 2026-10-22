<?php
require_once __DIR__ . '/../backend/config/app.php';

if (hay_sesion()) {
    header('Location: ' . panel_inicial(rol_actual()));
    exit;
}

include_once '../backend/php/login.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CK COMPUTERS</title>
  <link rel="icon" type="image/svg+xml" href="../backend/img/ck-icono.svg">
  <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="../backend/css/style.css">
</head>
<body class="login-page">
  <main class="login">
    <section class="login-form">
      <form method="POST" role="form" onsubmit="return validacion()">
        <div class="form-title">
          <h2>Iniciar sesión</h2>
          <p>Ingresa tus datos para continuar.</p>
        </div>

        <?php if (isset($errMsg)) { ?>
          <div class="login-error"><?php echo e($errMsg); ?></div>
        <?php } ?>

        <div class="input-container">
          <label for="usuario">Nombre de usuario</label>
          <input type="text" id="usuario" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" autocomplete="username" required>
        </div>

        <div class="input-container password">
          <label for="contra">Contraseña</label>
          <input type="password" id="contra" name="password" placeholder="Mínimo de 6 caracteres" autocomplete="current-password" required>
          <button type="button" class="ver-clave" id="ver-clave" title="Mostrar contraseña" aria-label="Mostrar contraseña"><span class="las la-eye"></span></button>
        </div>

        <label class="checkbox-container">
          <input type="checkbox" name="recordar" value="1"<?php echo !empty($_POST['recordar']) ? ' checked' : ''; ?>>
          <span class="checkmark"></span>
          Mantener la sesión
        </label>

        <button name="login" class="signup-btn" type="submit">Entrar</button>
      </form>
    </section>

    <section class="login-info">
      <div class="login-info-content">
        <img src="../backend/img/cklogo.svg" alt="CK COMPUTERS" class="ck-logo">
        <p>HOLA DE NUEVO, BIENVENIDO A TU SISTEMA DE VENTAS</p>
      </div>
    </section>
  </main>

  <script>
  // muestra u oculta la contraseña
  document.getElementById('ver-clave').addEventListener('click', function () {
    var campo = document.getElementById('contra');
    var mostrar = campo.type === 'password';
    campo.type = mostrar ? 'text' : 'password';
    this.firstElementChild.className = mostrar ? 'las la-eye-slash' : 'las la-eye';
    this.title = mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña';
    this.setAttribute('aria-label', this.title);
  });
  </script>
  <script type="text/javascript" src="../backend/js/reenvio.js"></script>
</body>
</html>
