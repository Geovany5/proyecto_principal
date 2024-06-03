<?php
session_start();

// Verificar si la sesión no está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página centrada</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        #contenedor {
            margin: 20px; /* Margen alrededor del contenedor */
            text-align: center; /* Centrar el contenido */
        }
        h1 {
            color: #fff; /* Texto blanco */
            font-size: 32px; /* Tamaño de fuente similar al de menu.php */
            margin-bottom: 20px; /* Espacio inferior */
        }
        label {
            color: #fff; /* Texto blanco */
            font-size: 24px; /* Tamaño de fuente similar al de menu.php */
            margin-bottom: 10px; /* Espacio inferior */
        }
        input[type="file"] {
            font-size: 20px; /* Tamaño de fuente similar al de menu.php */
            margin-bottom: 10px; /* Espacio inferior */
        }
        button[type="submit"] {
            font-size: 24px; /* Tamaño de fuente similar al de menu.php */
        }
    </style>
</head>
<body>
    <div id="contenedor">
        <!-- Tu contenido HTML aquí -->
        <h1><center>Subir y verificar archivos</center></h1>
        <br>
        <form action="verificar.php" method="post" enctype="multipart/form-data">
            <label for="fileInput">Selecciona un archivo:</label>
            <input type="file" name="fileInput" id="fileInput" required>
            <br>
            <br>
            <br>
            <button type="submit">Subir Archivo</button>
        </form>
    </div>
</body>
</html>
