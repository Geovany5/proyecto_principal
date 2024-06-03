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
    $consulta_usuario = "SELECT ID, departamentos FROM usuarios WHERE usuario = ?";
    $statement = $conexion->prepare($consulta_usuario);
    $statement->bind_param("s", $nombre_usuario);
    $statement->execute();
    $resultado_usuario = $statement->get_result();

    if ($resultado_usuario->num_rows == 0) {
        $mensaje = "El usuario no existe";
    } else {
        // Obtener el ID del usuario y los departamentos a los que pertenece
        $fila_usuario = $resultado_usuario->fetch_assoc();
        $usuario_id = $fila_usuario['ID'];
        $departamentos_usuario = $fila_usuario['departamentos'];

        // Comprobar si el departamento existe en la tabla departamentos
        $consulta_departamento = "SELECT departamento, nombres_usuarios, usuarios_id FROM departamentos WHERE departamento = ?";
        $statement = $conexion->prepare($consulta_departamento);
        $statement->bind_param("i", $numero_departamento);
        $statement->execute();
        $resultado_departamento = $statement->get_result();

        if ($resultado_departamento->num_rows == 0) {
            $mensaje = "El departamento no existe";
        } else {
            // Verificar si el usuario está asignado al departamento
            if (strpos($departamentos_usuario, (string)$numero_departamento) === false) {
                $mensaje = "Este usuario no pertenece a este departamento";
            } else {
                // Eliminar al usuario del departamento
                $departamentos_actualizados = str_replace($numero_departamento, "", $departamentos_usuario);
                
                // Actualizar el campo departamentos del usuario
                $consulta_actualizar_usuario = "UPDATE usuarios SET departamentos = ? WHERE ID = ?";
                $statement = $conexion->prepare($consulta_actualizar_usuario);
                $statement->bind_param("si", $departamentos_actualizados, $usuario_id);
                $statement->execute();

                // Eliminar al usuario del departamento en la tabla departamentos
                $fila_departamento = $resultado_departamento->fetch_assoc();
                $nombres_usuarios = $fila_departamento['nombres_usuarios'];
                $usuarios_id = $fila_departamento['usuarios_id'];

                $nuevos_nombres_usuarios = str_replace($nombre_usuario, "", $nombres_usuarios);
                $nuevos_usuarios_id = str_replace($usuario_id, "", $usuarios_id);

                // Actualizar la tabla departamentos
                $consulta_actualizar_departamento = "UPDATE departamentos SET nombres_usuarios = ?, usuarios_id = ? WHERE departamento = ?";
                $statement = $conexion->prepare($consulta_actualizar_departamento);
                $statement->bind_param("ssi", $nuevos_nombres_usuarios, $nuevos_usuarios_id, $numero_departamento);
                $statement->execute();

                $mensaje = "Usuario eliminado del departamento correctamente";
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
    <title>Eliminar Usuario de Departamento</title>
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
    <h2>Eliminar Usuario de Departamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="nombre_usuario">Nombre de Usuario:</label>
        <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>
        <label for="numero_departamento">Número de Departamento:</label>
        <input type="number" id="numero_departamento" name="numero_departamento" min="1" max="999" required><br><br>
        <button type="submit">Eliminar Usuario</button>
    </form>
    <br> <!-- Dejar una línea en blanco -->
    <?php echo $mensaje; ?> <!-- Imprimir el mensaje debajo del formulario -->
</body>
</html>

