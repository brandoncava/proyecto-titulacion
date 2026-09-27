<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('usuarios');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
}

if (isset($_POST['crear_usuario'])) {

    $nombre = trim($_POST['nombre'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $rol = (int)($_POST['rol'] ?? ROL_EMPLEADO);
    $password = $_POST['password'] ?? password_predeterminada($rol);

    if (!rol_valido($rol)) {
        echo "<script>alert('El perfil seleccionado no es válido.');</script>";
        return;
    }
    $state = (int)($_POST['state'] ?? 1);

    if ($nombre === '' || $username === '' || $correo === '' || $password === '') {

        echo "<script>
                alert('Todos los campos son obligatorios.');
              </script>";

    } elseif (strlen($password) < 6) {

        echo "<script>
                alert('La contraseña debe tener como mínimo 6 caracteres.');
              </script>";

    } else {

        try {

            $consulta = $connect->prepare(
                "SELECT id FROM usuarios WHERE username = :username LIMIT 1"
            );

            $consulta->execute([
                ':username' => $username
            ]);

            if ($consulta->fetch()) {

                echo "<script>
                        alert('El nombre de usuario ya existe.');
                      </script>";

            } else {

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $connect->prepare(
                    "INSERT INTO usuarios
                    (nombre, username, correo, password, rol, state)
                    VALUES
                    (:nombre, :username, :correo, :password, :rol, :state)"
                );

                $stmt->execute([
                    ':nombre' => $nombre,
                    ':username' => $username,
                    ':correo' => $correo,
                    ':password' => $passwordHash,
                    ':rol' => $rol,
                    ':state' => $state
                ]);

                echo "<script>
                        alert('Usuario creado correctamente.');
                        window.location.href = '../../frontend/accesos/mostrar.php';
                      </script>";
            }

        } catch (PDOException $e) {

            echo "<script>
                    alert('No se pudo crear el usuario.');
                  </script>";
        }
    }
}
?>