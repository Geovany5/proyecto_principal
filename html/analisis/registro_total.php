<?php
session_start();

// Verificar si la sesión no está iniciada como administrador
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}

// Conectar a la base de datos (sustituir con tus propias credenciales)
$conexion = new mysqli("localhost", "root", "1942", "proyecto");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta SQL para obtener todos los archivos subidos por todos los usuarios con sus departamentos
$consulta_archivos = "SELECT usuarios.usuario, usuarios.departamentos, archivos.nombre_archivo, archivos.es_malicioso FROM archivos JOIN usuarios ON archivos.usuario_id = usuarios.ID";
$resultado_archivos = $conexion->query($consulta_archivos);

// Array para almacenar los archivos de cada usuario junto con sus departamentos
$archivos_por_usuario = array();

// Obtener los archivos de cada usuario junto con sus departamentos
while ($fila_archivo = $resultado_archivos->fetch_assoc()) {
    $nombre_usuario = $fila_archivo['usuario'];
    $departamentos = $fila_archivo['departamentos'];
    $nombre_archivo = $fila_archivo['nombre_archivo'];
    $es_malicioso = $fila_archivo['es_malicioso'];

    // Crear un nuevo array para el usuario si aún no existe
    if (!isset($archivos_por_usuario[$nombre_usuario])) {
        $archivos_por_usuario[$nombre_usuario] = array(
            'departamentos' => $departamentos,
            'archivos' => array()
        );
    }

    // Agregar el archivo al array del usuario
    $archivos_por_usuario[$nombre_usuario]['archivos'][] = array(
        'nombre_archivo' => $nombre_archivo,
        'es_malicioso' => $es_malicioso
    );
}

// Mostrar los nombres de los archivos junto con su estado de maliciosidad, enlaces de descarga y departamentos
echo "<h2>Archivos subidos por todos los usuarios:</h2>";
foreach ($archivos_por_usuario as $nombre_usuario => $info_usuario) {
    $departamentos = $info_usuario['departamentos'];
    $archivos = $info_usuario['archivos'];
    $departamento_texto = $departamentos ? "perteneciente al departamento $departamentos" : "";
    echo "<h3>Archivos subidos por el usuario $nombre_usuario $departamento_texto:</h3>";
    echo "<ul>";
    foreach ($archivos as $archivo) {
        $nombre_archivo = $archivo['nombre_archivo'];
        $es_malicioso = $archivo['es_malicioso'];
        
        $estado = $es_malicioso == 'Si' ? 'No disponible por contenido malicioso' : 'No es malicioso';
        echo "<li>Usuario: $nombre_usuario - Archivo: $nombre_archivo - $estado";

        // Generar el nombre del reporte
        $nombre_reporte = $nombre_archivo . "_reporte.txt";
        if ($es_malicioso == 'No') {
            echo " - <a href='normales/$nombre_usuario/$nombre_archivo' download>Descargar archivo</a>";
        } else {
            echo " - <a href='maliciosos/$nombre_usuario/$nombre_archivo' download>Descargar archivo</a>";
        }
        echo " - <a href='reportes/$nombre_usuario/$nombre_reporte' download>Descargar reporte</a>";
        echo "</li>";
    }
    echo "</ul>";
}

// Cerrar la conexión a la base de datos
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Total</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px; /* Aumenta el espacio alrededor del contenido */
        }
        h2, h3 {
            color: #fff; /* Texto blanco */
        }
        ul {
            list-style-type: none; /* Eliminar viñetas de la lista */
            padding: 0;
            margin-top: 10px; /* Aumenta el espacio entre el título y la lista */
        }
        li {
            margin-bottom: 15px; /* Aumenta el espacio entre elementos de la lista */
        }
        a {
            color: #fff; /* Enlaces blancos */
            text-decoration: none; /* Elimina la subrayado de los enlaces */
        }
    </style>
</head>
<body>

</body>
</html>
