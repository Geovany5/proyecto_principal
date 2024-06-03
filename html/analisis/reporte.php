<?php
session_start();

// Verificar si la sesión no está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Obtener el nombre de usuario actual
$usuario_actual = $_SESSION['usuario'];

// Verificar si se ha proporcionado el nombre del archivo
if (isset($_GET['archivo'])) {
    $archivoNombre = $_GET['archivo'];

    // Directorio de los reportes
    $reportesDirectory = 'reportes/' . $usuario_actual . '/';

    // Construir el nombre del archivo de reporte conservando la extensión
    $reporteNombre = $archivoNombre . '_reporte.txt';
    $reportePath = $reportesDirectory . $reporteNombre;

    // Verificar si el archivo de reporte existe
    if (file_exists($reportePath)) {
        // Imprimir el nombre del archivo de reporte
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reporte</title>
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
        h1 {
            color: #fff; /* Texto blanco */
            font-size: 32px; /* Tamaño de fuente similar al de menu.php */
            text-align: center;
        }
        h3 {
            color: #fff; /* Texto blanco */
            font-size: 24px; /* Tamaño de fuente similar al de menu.php */
        }
        pre {
            color: #fff; /* Texto blanco */
            font-size: 20px; /* Tamaño de fuente similar al de menu.php */
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Reporte de " . htmlspecialchars($archivoNombre) . "</h1>";

        // Mostrar el contenido del archivo de reporte
        echo "<h3>Contenido del Reporte:</h3>";
        echo "<pre>" . htmlspecialchars(file_get_contents($reportePath)) . "</pre>";
        echo "</div>
</body>
</html>";
    } else {
        echo "<p>No se encontró el reporte para el archivo '$archivoNombre'.</p>";
    }
} else {
    echo "<p>No se proporcionó el nombre del archivo.</p>";
}
?>
