<?php
session_start();

// Verificar si se enviaron datos mediante POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conectar a la base de datos (sustituir con tus propias credenciales)
    $conexion = new mysqli("localhost", "root", "1942", "proyecto");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Verificar si la tabla usuarios no existe y crearla
    $crearTablaUsuarios = "
        CREATE TABLE IF NOT EXISTS usuarios (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $conexion->query($crearTablaUsuarios);

    // Obtener datos del formulario
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];

    // Hashear la contraseña
    $hashedPassword = hash('sha256', $password);

    // Consultar la base de datos para obtener la contraseña hasheada
    $consulta = $conexion->prepare("SELECT password FROM usuarios WHERE usuario = ?");
    $consulta->bind_param("s", $usuario);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        // Usuario encontrado
        $fila = $resultado->fetch_assoc();
        $hashAlmacenado = $fila["password"];

        // Comparar el hash almacenado con el hash enviado desde el cliente
        if ($hashedPassword === $hashAlmacenado) {
            $_SESSION['loggedin'] = true; // Establecer variable de sesión
            $_SESSION['usuario'] = $usuario; // Establecer nombre de usuario en sesión
            header("Location: ../analisis/menu.php");
            exit;
        } else {
            echo "<h1><center>Error al iniciar sesión: Credenciales incorrectas</center></h1>";
        }
    } else {
        echo "<h1><center>Error al iniciar sesión: Credenciales incorrectas</center></h1>";
    }

    // Cerrar la conexión a la base de datos
    $consulta->close();
    $conexion->close();
}
?>
