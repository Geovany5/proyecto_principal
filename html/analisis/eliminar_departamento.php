<?php
session_start();

// Verificar si la sesión está iniciada como administrador
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

$mensaje = ""; // Variable para almacenar mensajes a mostrar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el número de departamento ingresado
    $numero_departamento = $_POST["numero_departamento"];

    // Conectar a la base de datos
    $conexion = new mysqli("localhost", "root", "1942", "proyecto");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Comprobar si el departamento existe en la tabla departamentos
    $consulta_departamento = "SELECT departamento, nombres_usuarios, usuarios_id FROM departamentos WHERE departamento = ?";
    $statement = $conexion->prepare($consulta_departamento);
    $statement->bind_param("i", $numero_departamento);
    $statement->execute();
    $resultado_departamento = $statement->get_result();

    if ($resultado_departamento->num_rows == 0) {
        $mensaje = "Este departamento no existe";
    } else {
        // Eliminar el departamento de la tabla departamentos
        $consulta_eliminar_departamento = "DELETE FROM departamentos WHERE departamento = ?";
        $statement = $conexion->prepare($consulta_eliminar_departamento);
        $statement->bind_param("i", $numero_departamento);
        $statement->execute();

        // Eliminar el departamento de los usuarios en la tabla usuarios
        $consulta_actualizar_usuarios = "UPDATE usuarios SET departamentos = REPLACE(departamentos, ?, '')";
        $statement = $conexion->prepare($consulta_actualizar_usuarios);
        $statement->bind_param("i", $numero_departamento);
        $statement->execute();

        $mensaje = "Departamento eliminado correctamente";
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
    <title>Eliminar Departamento</title>
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
    <h2>Eliminar Departamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="numero_departamento">Número de Departamento:</label>
        <input type="number" id="numero_departamento" name="numero_departamento" min="1" max="999" required><br><br>
        <button type="submit">Eliminar Departamento</button>
    </form>
    <br> <!-- Dejar una línea en blanco -->
    <?php echo $mensaje; ?> <!-- Imprimir el mensaje debajo del formulario -->
</body>
</html>

