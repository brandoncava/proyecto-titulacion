<?php
require_once __DIR__ . '/../backend/config/auth.php';

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
  <link rel="icon" type="image/x-icon" href="../backend/img/ck.ico">
  <link rel="stylesheet" type="text/css" href="../backend/css/style.css">
  <link rel="stylesheet" href="../backend/css/loader.css">
</head>
<body class="login-page">
  <div class="loader-container">
    <div class="load_animation">
      <ion-icon name="bag-handle-outline" class="animation"></ion-icon>
    </div>
  </div>

  <div class="wrapper">
    <div class="loader"></div>
  </div>

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
          <input type="text" id="usuario" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" autocomplete="off">
        </div>

        <div class="input-container password">
          <label for="contra">Contraseña</label>
          <input type="password" id="contra" name="password" placeholder="Mínimo de 6 caracteres">
          <i class="far fa-eye-slash"></i>
        </div>

        <label class="checkbox-container">
          <input type="checkbox">
          <span class="checkmark"></span>
          Mantener la sesión
        </label>

        <button name="login" class="signup-btn" type="submit">Entrar</button>
      </form>
    </section>

    <section class="login-info">
      <div class="login-info-content">
        <img src="../backend/img/cklogo.png" alt="CK COMPUTERS" class="ck-logo">
        <p>HOLA DE NUEVO, BIENVENIDO A TU SISTEMA DE VENTAS</p>
      </div>
    </section>
  </main>

  <script src="../backend/js/jquery.min.js"></script>
  <script type="text/javascript" src="../backend/js/script.js"></script>
  <script type="text/javascript" src="../backend/js/validate.js"></script>
  <script type="text/javascript" src="../backend/js/reenvio.js"></script>
  <script src="../backend/js/loader.js"></script>
  <script type="text/javascript">
    setTimeout(function(){
      $(".load_animation").fadeOut(500);
      document.querySelector(".wrapper").classList.add("fade");
    }, 3000);
  </script>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
