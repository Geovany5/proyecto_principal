<?php
session_start();

// Verificar si la sesión de administrador está iniciada
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Definir una variable para almacenar el mensaje
$mensaje = "";

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["departamento"])) {
    // Obtener el número de departamento enviado por el formulario
    $departamento = $_POST["departamento"];

    // Conectar a la base de datos (sustituir con tus propias credenciales)
    $conexion = new mysqli("localhost", "root", "1942", "proyecto");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Consulta SQL para comprobar si el departamento ya existe
    $consulta_existencia_departamento = "SELECT departamento FROM departamentos WHERE departamento = ?";
    $statement = $conexion->prepare($consulta_existencia_departamento);
    $statement->bind_param("i", $departamento);
    $statement->execute();
    $resultado = $statement->get_result();

    if ($resultado->num_rows > 0) {
        $mensaje = "Este departamento ya existe.";
    } else {
        // Insertar el nuevo departamento en la base de datos
        $consulta_insertar_departamento = "INSERT INTO departamentos (departamento) VALUES (?)";
        $statement = $conexion->prepare($consulta_insertar_departamento);
        $statement->bind_param("i", $departamento);
        if ($statement->execute()) {
            $mensaje = "Departamento creado correctamente.";
        } else {
            $mensaje = "Error al crear el departamento.";
        }
    }

    // Cerrar la conexión a la base de datos
    $statement->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Departamento</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px; /* Aumenta el espacio alrededor del contenido */
        }
        h2 {
            color: #fff; /* Texto blanco */
        }
        label {
            display: block; /* Coloca cada etiqueta en una línea nueva */
            margin-bottom: 10px; /* Aumenta el espacio entre etiquetas y campos de entrada */
        }
        input[type="number"] {
            background-color: #333; /* Gris oscuro */
            color: #fff; /* Texto blanco */
            border: none;
            padding: 8px;
            margin-bottom: 15px; /* Aumenta el espacio entre campos de entrada */
            font-size: 18px; /* Tamaño de fuente similar al de menu.php */
            border-radius: 5px;
        }
        button {
            background-color: #333; /* Gris oscuro */
            color: #fff; /* Texto blanco */
            border: none;
            padding: 10px 20px;
            font-size: 18px; /* Tamaño de fuente similar al de menu.php */
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #555; /* Gris un poco más claro al pasar el ratón */
        }
    </style>
</head>
<body>
    <h2>Crear Departamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="departamento">Número de Departamento:</label>
        <input type="number" id="departamento" name="departamento" min="1" max="999" required><br>
        <button type="submit">Crear Departamento</button>
    </form>
    <br>
    <?php echo $mensaje; ?> <!-- Imprimir el mensaje debajo del formulario -->
</body>
</html>
