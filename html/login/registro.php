<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];

    $conexion = new mysqli("localhost", "root", "1942", "proyecto");

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $crearTablaUsuarios = "
        CREATE TABLE IF NOT EXISTS usuarios (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $conexion->query($crearTablaUsuarios);

    $consulta = "INSERT INTO usuarios (usuario, password) VALUES ('$usuario', '$password')";

    if ($conexion->query($consulta) === TRUE) {
    // Obtener el ID del nuevo usuario
    $nuevoUsuarioId = $conexion->insert_id;

    // Insertar el mismo ID en la tabla archivos como usuario_id
    $actualizarUsuarioId = "UPDATE archivos SET usuario_id = '$nuevoUsuarioId' WHERE usuario_id IS NULL";
    $conexion->query($actualizarUsuarioId);

        header("Location: index.html");
    } else {
        echo "Error al registrar el usuario: " . $conexion->error;
    }

    $conexion->close();
}
?>