<?php
session_start();

// Verificar si la sesión no está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "1942", "proyecto");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el nombre de usuario actual
$usuario = $_SESSION['usuario'];

// Consultar los departamentos en los que está el usuario
$consulta_departamentos_usuario = "SELECT departamentos FROM usuarios WHERE usuario = ?";
$statement = $conexion->prepare($consulta_departamentos_usuario);
$statement->bind_param("s", $usuario);
$statement->execute();
$resultado_departamentos = $statement->get_result();
$departamentos_usuario = $resultado_departamentos->fetch_assoc()['departamentos'];

// Separar los departamentos por comas
$departamentos_separados = implode(", ", explode(" ", $departamentos_usuario));

// Cerrar la conexión a la base de datos
$conexion->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
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
            font-size: 32px; /* Aumenta el tamaño de la letra */
            margin-bottom: 20px; /* Añade espacio debajo del título */
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 15px; /* Aumenta el espacio entre elementos de la lista */
        }
        a {
            color: #fff; /* Texto blanco */
            text-decoration: none;
            font-size: 24px; /* Aumenta el tamaño de la letra */
        }
        a:hover {
            text-decoration: underline;
        }
        p {
            color: #fff; /* Texto blanco */
            font-size: 24px; /* Aumenta el tamaño de la letra */
            margin-bottom: 15px; /* Aumenta el espacio entre párrafos */
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: left;">
            <p>Bienvenido <?php echo $usuario; ?></p>
            <p>Perteneces al departamento: <?php echo $departamentos_separados; ?></p>
        </div>
        <br>
        <h1>Menú</h1>
        <ul>
            <li><a href="upload.php">Subir archivo</a></li>
            <li><a href="compartir.php">Compartir archivos</a></li>
            <li><a href="registro.php">Registro de mis archivos</a></li>
            <li><a href="login_admin.php">Panel Administrativo</a></li> <!-- Nuevo enlace -->
        </ul>
    </div>
</body>
</html>
