<?php
session_start();

// Verificar si la sesión no está iniciada como administrador
if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header("Location: login_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px; /* Aumenta el espacio alrededor del contenido */
        }
        h1, h2, p {
            color: #fff; /* Texto blanco */
        }
        a {
            color: #fff; /* Enlaces blancos */
            text-decoration: none; /* Elimina la subrayado de los enlaces */
        }
        ul {
            list-style-type: none; /* Eliminar viñetas de la lista */
            padding: 0;
        }
        li {
            margin-bottom: 15px; /* Aumenta el espacio entre elementos de la lista */
        }
    </style>
</head>
<body>
    <h1>Panel Administrativo</h1>
    
    <!-- Primer apartado: Registro de todos los archivos subidos -->
    <h2>Registro de todos los archivos subidos</h2>
    <ul>
        <li><h4><a href="registro_total.php">Registro de todos los archivos subidos</a></h4></li>
    </ul>
    
    <!-- Segundo apartado: Administración de departamentos y usuarios -->
    <h2>Administración de departamentos y usuarios</h2>
    <ul>
        <li><h4><a href="crear_departamento.php">Crear departamento</a></h4></li>
        <li><h4><a href="asignar_usuario.php">Asignar usuario a departamento</a></h4></li>
        <li><h4><a href="eliminar_usuario_dep.php">Eliminar usuario de departamento</a></h4></li>
        <li><h4><a href="eliminar_departamento.php">Eliminar un departamento</a></h4></li>
        <li><h4><a href="usuarios_totales.php">Departamentos y usuarios totales</a></h4></li>
    </ul>
</body>
</html>
