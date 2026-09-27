<?php

require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['login'])) {

    $errMsg = '';

    $username = trim($_POST['username'] ?? '');
    $passwordPlano = $_POST['password'] ?? '';

    if ($username === '') {
        $errMsg = 'Digite su usuario.';
    } elseif ($passwordPlano === '') {
        $errMsg = 'Digite su contraseña.';
    }

    if ($errMsg === '') {

        try {
            $stmt = $connect->prepare(
                'SELECT id, nombre, username, correo, password, rol, state
                   FROM usuarios
                  WHERE username = :username
                  LIMIT 1'
            );

            $stmt->execute([':username' => $username]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $guardado = $data['password'] ?? '';
            $valida = false;

            if ($data !== false) {
                if (password_verify($passwordPlano, $guardado)) {
                    $valida = true;
                }
            }

            if (!$valida) {

                // mismo mensaje para user inexistente o clave mala
                $errMsg = 'Usuario o contraseña incorrectos.';

            } elseif ((int)$data['state'] !== 1) {

                $errMsg = 'Este usuario se encuentra desactivado.';

            } elseif (!rol_valido($data['rol'])) {

                $errMsg = 'El usuario no tiene un perfil valido asignado.';

            } else {

                session_regenerate_id(true);

                $_SESSION['id'] = $data['id'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['username'] = $data['username'];
                $_SESSION['correo'] = $data['correo'];
                $_SESSION['rol'] = (int)$data['rol'];
                $_SESSION['state'] = (int)$data['state'];

                header('Location: ' . panel_inicial($data['rol']));
                exit;
            }

        } catch (PDOException $e) {
            error_log('Error en el inicio de sesion: ' . $e->getMessage());
            $errMsg = 'No se pudo procesar el inicio de sesión.';
        }
    }
}
