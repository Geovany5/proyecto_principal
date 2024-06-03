<?php
session_start();

// Verificar si la sesión no está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Verificar si ya se ha enviado el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar las credenciales del administrador
    $admin_username = "admin";
    $admin_password = "1234567890";

    if ($_POST["username"] === $admin_username && $_POST["password"] === $admin_password) {
        // Credenciales válidas, marcar al usuario como logueado y redirigir al panel de administrador
        $_SESSION['loggedin_admin'] = true; // Nueva variable de sesión para indicar que es administrador
        header("Location: admin.php");
        exit;
    } else {
        // Credenciales incorrectas, mostrar mensaje de error
        $error_message = "Credenciales incorrectas. Inténtalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión como Administrador</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h1, h2, p, label {
            color: #fff; /* Texto blanco */
        }
        input[type="text"], input[type="password"], button {
            background-color: #333; /* Gris oscuro */
            color: #fff; /* Texto blanco */
            border: none;
            padding: 8px;
            margin: 5px 0;
            font-size: 18px; /* Tamaño de fuente similar al de menu.php */
            border-radius: 5px;
        }
        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Iniciar sesión como Administrador</h1>
    <?php
    // Mostrar mensaje de error si existe
    if (isset($error_message)) {
        echo "<p>$error_message</p>";
    }
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Iniciar sesión</button>
    </form>
</body>
</html>
