<?php
session_start();

// Verificar si la sesión está iniciada como administrador
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$mensaje = ""; // Variable para almacenar mensajes a mostrar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre_usuario = $_POST["nombre_usuario"];
    $numero_departamento = $_POST["numero_departamento"];

    // Conectar a la base de datos
    $conexion = new mysqli("localhost", "root", "1942", "proyecto");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Comprobar si el usuario existe en la tabla usuarios
    $consulta_usuario = "SELECT ID FROM usuarios WHERE usuario = ?";
    $statement = $conexion->prepare($consulta_usuario);
    $statement->bind_param("s", $nombre_usuario);
    $statement->execute();
    $resultado_usuario = $statement->get_result();

    if ($resultado_usuario->num_rows == 0) {
        $mensaje = "Este usuario no existe";
    } else {
        // Obtener el ID del usuario
        $fila_usuario = $resultado_usuario->fetch_assoc();
        $usuario_id = $fila_usuario['ID'];

        // Comprobar si el departamento existe en la tabla departamentos
        $consulta_departamento = "SELECT departamento FROM departamentos WHERE departamento = ?";
        $statement = $conexion->prepare($consulta_departamento);
        $statement->bind_param("i", $numero_departamento);
        $statement->execute();
        $resultado_departamento = $statement->get_result();

        if ($resultado_departamento->num_rows == 0) {
            $mensaje = "Este departamento no existe";
        } else {
            // Verificar si el usuario ya está asignado al departamento
            $consulta_asignacion = "SELECT * FROM usuarios WHERE usuario = ? AND FIND_IN_SET(?, departamentos)";
            $statement = $conexion->prepare($consulta_asignacion);
            $statement->bind_param("si", $nombre_usuario, $numero_departamento);
            $statement->execute();
            $resultado_asignacion = $statement->get_result();

            if ($resultado_asignacion->num_rows > 0) {
                $mensaje = "Este usuario ya está en este departamento";
            } else {
                // Obtener los nombres de usuarios y IDs existentes en el departamento
                $consulta_departamento_actual = "SELECT nombres_usuarios, usuarios_id FROM departamentos WHERE departamento = ?";
                $statement = $conexion->prepare($consulta_departamento_actual);
                $statement->bind_param("i", $numero_departamento);
                $statement->execute();
                $resultado_departamento_actual = $statement->get_result();
                $fila_departamento_actual = $resultado_departamento_actual->fetch_assoc();
                $nombres_usuarios_actuales = $fila_departamento_actual['nombres_usuarios'];
                $usuarios_id_actuales = $fila_departamento_actual['usuarios_id'];

                // Verificar si el usuario ya está en la lista de usuarios del departamento
                $usuarios_en_departamento = explode(" ", $nombres_usuarios_actuales);
                if (!in_array($nombre_usuario, $usuarios_en_departamento)) {
                    // Concatenar el nuevo usuario a los nombres de usuarios existentes
                    if (!empty($nombres_usuarios_actuales)) {
                        $nombres_usuarios_nuevos = $nombres_usuarios_actuales . " " . $nombre_usuario;
                    } else {
                        $nombres_usuarios_nuevos = $nombre_usuario;
                    }

                    // Concatenar el nuevo ID de usuario a los IDs de usuarios existentes
                    if (!empty($usuarios_id_actuales)) {
                        $usuarios_id_nuevos = $usuarios_id_actuales . " " . $usuario_id;
                    } else {
                        $usuarios_id_nuevos = $usuario_id;
                    }

                    // Asignar al usuario al departamento
                    $consulta_asignar_usuario = "UPDATE usuarios SET departamentos = CONCAT_WS(' ', departamentos, ?) WHERE ID = ?";
                    $statement = $conexion->prepare($consulta_asignar_usuario);
                    $statement->bind_param("si", $numero_departamento, $usuario_id);
                    $statement->execute();

                    // Actualizar los nombres de usuarios y IDs en el departamento
                    $consulta_actualizar_departamento = "UPDATE departamentos SET nombres_usuarios = ?, usuarios_id = ? WHERE departamento = ?";
                    $statement = $conexion->prepare($consulta_actualizar_departamento);
                    $statement->bind_param("ssi", $nombres_usuarios_nuevos, $usuarios_id_nuevos, $numero_departamento);
                    $statement->execute();

                    $mensaje = "Usuario asignado correctamente al departamento";
                } else {
                    $mensaje = "Este usuario ya está en este departamento";
                }
            }
        }
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Usuario a Departamento</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 20px; /* Aumentar el espacio alrededor del contenido */
            padding: 0;
        }
        h2 {
            color: #fff; /* Texto blanco */
        }
        label {
            display: block;
            margin-bottom: 10px; /* Añadir espacio entre las etiquetas y los campos de entrada */
        }
        input[type="text"],
        input[type="number"],
        button {
            background-color: #333; /* Gris oscuro */
            color: #fff; /* Texto blanco */
            border: none;
            padding: 8px;
            margin-bottom: 10px; /* Añadir espacio entre los campos de entrada y el botón */
            font-size: 18px; /* Tamaño de fuente similar al del menú */
            border-radius: 5px;
        }
        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Asignar Usuario a Departamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="nombre_usuario">Nombre de Usuario:</label>
        <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>
        <label for="numero_departamento">Número de Departamento:</label>
        <input type="number" id="numero_departamento" name="numero_departamento" min="1" max="999" required><br><br>
        <button type="submit">Asignar Usuario</button>
    </form>
    <br> <!-- Dejar una línea en blanco -->
    <?php echo $mensaje; ?> <!-- Imprimir el mensaje debajo del formulario -->
</body>
</html>

