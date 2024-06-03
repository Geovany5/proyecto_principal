<?php
session_start();

// Verificar si la sesión está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Obtener el nombre de usuario de la sesión
$usuario = $_SESSION['usuario'];

// Conectar a la base de datos (sustituir con tus propias credenciales)
$conexion = new mysqli("localhost", "root", "1942", "proyecto");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta SQL para obtener el usuario_id del usuario
$consulta_usuario_id = "SELECT ID FROM usuarios WHERE usuario = ?";
$statement = $conexion->prepare($consulta_usuario_id);
$statement->bind_param("s", $usuario);
$statement->execute();
$resultado = $statement->get_result();

// Obtener el usuario_id
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $usuario_id = $fila['ID'];

    // Consulta SQL para obtener los nombres de los archivos subidos por el usuario
    $consulta_archivos_usuario = "SELECT nombre_archivo, es_malicioso FROM archivos WHERE usuario_id = ?";
    $statement = $conexion->prepare($consulta_archivos_usuario);
    $statement->bind_param("i", $usuario_id);
    $statement->execute();
    $resultado_archivos = $statement->get_result();

    // Mostrar los nombres de los archivos junto con su estado de maliciosidad y enlaces de descarga
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Registro de archivos</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 20px;
        }
        h2 {
            color: #fff; /* Texto blanco */
            font-size: 32px; /* Tamaño de fuente similar al de menu.php */
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            margin-bottom: 10px;
            font-size: 24px; /* Tamaño de fuente similar al de menu.php */
        }
        a {
            color: #fff; /* Texto blanco */
            text-decoration: none;
            font-size: 24px; /* Tamaño de fuente similar al de menu.php */
        }
    </style>
</head>
<body>
    <div class='container'>";

    echo "<h2>Archivos subidos por el usuario $usuario:</h2>";
    echo "<ul>";
    while ($fila_archivo = $resultado_archivos->fetch_assoc()) {
        $nombre_archivo = $fila_archivo['nombre_archivo'];
        $es_malicioso = $fila_archivo['es_malicioso'];
        $estado = $es_malicioso == 'Si' ? 'No disponible por contenido malicioso' : 'No es malicioso';
        echo "<li style='font-size: 24px;'>$nombre_archivo - <span style='font-size: 24px;'>$estado</span>";

        $nombre_reporte = $nombre_archivo . "_reporte.txt";
        if ($es_malicioso == 'No') {
            echo " - <a href='normales/$usuario/$nombre_archivo' download>Descargar archivo</a>";
        } else {
            echo " - <a href='maliciosos/$usuario/$nombre_archivo' download>Descargar archivo</a>";
        }
        echo " - <a href='reportes/$usuario/$nombre_reporte' download>Descargar reporte</a>";
        echo "</li>";
    }
    echo "</ul>";

    echo "</div>
</body>
</html>";

} else {
    echo "Usuario no encontrado.";
}

// Cerrar la conexión a la base de datos
$statement->close();
$conexion->close();
?>
