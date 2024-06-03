<?php
session_start();

// Verificar si la sesión no está iniciada como administrador
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "1942", "proyecto");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener la lista de departamentos y usuarios
$consulta_departamentos = "SELECT d.departamento, d.nombres_usuarios, d.usuarios_id FROM departamentos d
                           LEFT JOIN usuarios u ON FIND_IN_SET(u.ID, d.usuarios_id) > 0
                           GROUP BY d.departamento";
$resultado_departamentos = $conexion->query($consulta_departamentos);

// Obtener la lista de usuarios sin departamento asignado
$consulta_usuarios_sin_departamento = "SELECT usuario FROM usuarios WHERE departamentos IS NULL OR departamentos = ''";
$resultado_usuarios_sin_departamento = $conexion->query($consulta_usuarios_sin_departamento);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departamentos y usuarios totales</title>
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
        h3 {
            color: #ddd; /* Texto gris claro */
        }
    </style>
</head>
<body>
    <h2>Departamentos y usuarios totales</h2>
    <?php
    // Mostrar los departamentos y sus usuarios
    while ($fila_departamento = $resultado_departamentos->fetch_assoc()) {
        $departamento = $fila_departamento['departamento'];
        $usuarios = $fila_departamento['nombres_usuarios'];
        echo "<h3>Departamento $departamento</h3>";
        if (!empty($usuarios)) {
            $usuarios_array = explode(" ", $usuarios);
            foreach ($usuarios_array as $usuario) {
                echo "$usuario<br>";
            }
        } else {
            echo "No hay usuarios asignados a este departamento.<br>";
        }
    }

    // Mostrar usuarios sin departamento asignado
    if ($resultado_usuarios_sin_departamento->num_rows > 0) {
        echo "<h3>Usuarios sin un departamento asignado</h3>";
        while ($fila_usuario_sin_departamento = $resultado_usuarios_sin_departamento->fetch_assoc()) {
            $usuario_sin_departamento = $fila_usuario_sin_departamento['usuario'];
            echo "$usuario_sin_departamento<br>";
        }
    } else {
        echo "<h3>No hay usuarios sin un departamento asignado</h3>";
    }
    ?>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$conexion->close();
?>
