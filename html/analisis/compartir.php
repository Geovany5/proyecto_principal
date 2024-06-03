<?php
session_start();

// Verificar si la sesión está iniciada como usuario
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Obtener el nombre de usuario actual
$usuario_actual = $_SESSION['usuario'];

// Conectar a la base de datos (sustituir con tus propias credenciales)
$conexion = new mysqli("localhost", "root", "1942", "proyecto");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consultar el ID del usuario actual
$consulta_id_usuario = "SELECT ID FROM usuarios WHERE usuario = ?";
$statement = $conexion->prepare($consulta_id_usuario);
$statement->bind_param("s", $usuario_actual);
$statement->execute();
$resultado_id_usuario = $statement->get_result();
$id_usuario = $resultado_id_usuario->fetch_assoc()['ID'];

// Función para mostrar mensajes de error o éxito
function mostrarMensaje($mensaje, $es_error = false) {
    echo "<p style='color: " . ($es_error ? "red" : "green") . ";'>$mensaje</p>";
}

// Función para copiar un archivo a otro directorio
function copiarArchivo($nombre_archivo, $directorio_origen, $directorio_destino) {
    if (!file_exists($directorio_destino)) {
        // Crear el directorio si no existe
        mkdir($directorio_destino, 0777, true);
    }
    $ruta_origen = $directorio_origen . "/" . $nombre_archivo;
    $ruta_destino = $directorio_destino . "/" . $nombre_archivo;
    if (copy($ruta_origen, $ruta_destino)) {
        return true;
    } else {
        return false;
    }
}

// Obtener lista de departamentos
$consulta_departamentos = "SELECT departamento FROM departamentos";
$resultado_departamentos = $conexion->query($consulta_departamentos);
$departamentos_array = [];
while ($fila_departamento = $resultado_departamentos->fetch_assoc()) {
    $departamentos_array[] = $fila_departamento['departamento'];
}

// Procesar el envío del archivo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Comprobar si se está compartiendo con un usuario
    if (isset($_POST['usuario_destino']) && isset($_POST['archivo'])) {
        $usuario_destino = $_POST['usuario_destino'];
        $archivo = $_POST['archivo'];

        // Verificar si el archivo pertenece al usuario actual
        $consulta_archivo_usuario = "SELECT * FROM archivos WHERE nombre_archivo = ? AND usuario_id = ?";
        $statement = $conexion->prepare($consulta_archivo_usuario);
        $statement->bind_param("si", $archivo, $id_usuario);
        $statement->execute();
        $resultado_archivo = $statement->get_result();

        if ($resultado_archivo->num_rows > 0) {
            // Verificar si el usuario de destino existe
            $consulta_usuario = "SELECT * FROM usuarios WHERE usuario = ?";
            $statement = $conexion->prepare($consulta_usuario);
            $statement->bind_param("s", $usuario_destino);
            $statement->execute();
            $resultado_usuario = $statement->get_result();

            if ($resultado_usuario->num_rows > 0) {
                // Copiar el archivo al directorio compartido del usuario de destino
                $directorio_compartido = "compartidos/$usuario_destino";
                if (copiarArchivo($archivo, "normales/$usuario_actual", $directorio_compartido)) {
                    mostrarMensaje("El archivo se compartió con éxito con el usuario $usuario_destino.");
                } else {
                    mostrarMensaje("Hubo un error al compartir el archivo.", true);
                }
            } else {
                mostrarMensaje("El usuario destino no existe.", true);
            }
        } else {
            mostrarMensaje("No tienes permiso para compartir este archivo o no existe.", true);
        }
    }

    // Comprobar si se está compartiendo con un departamento
    if (isset($_POST['departamento_destino']) && isset($_POST['archivo'])) {
        $departamento_destino = $_POST['departamento_destino'];
        $archivo = $_POST['archivo'];

        // Verificar si el archivo pertenece al usuario actual
        $consulta_archivo_usuario = "SELECT * FROM archivos WHERE nombre_archivo = ? AND usuario_id = ?";
        $statement = $conexion->prepare($consulta_archivo_usuario);
        $statement->bind_param("si", $archivo, $id_usuario);
        $statement->execute();
        $resultado_archivo = $statement->get_result();

        if ($resultado_archivo->num_rows > 0) {
            // Dividir los departamentos por espacio
            $departamentos_usuario = explode(' ', $departamento_destino);

            // Recorrer cada departamento
            foreach ($departamentos_usuario as $departamento) {
                // Verificar si el departamento de destino existe
                if (in_array($departamento, $departamentos_array)) {
                    // Copiar el archivo al directorio compartido del departamento de destino
                    $directorio_compartido = "compartidos/$departamento";
                    if (copiarArchivo($archivo, "normales/$usuario_actual", $directorio_compartido)) {
                        mostrarMensaje("El archivo se compartió con éxito con el departamento $departamento.");
                    } else {
                        mostrarMensaje("Hubo un error al compartir el archivo.", true);
                    }
                } else {
                    mostrarMensaje("El departamento destino no existe.", true);
                }
            }
        } else {
            mostrarMensaje("No tienes permiso para compartir este archivo o no existe.", true);
        }
    }
}

// Consultar los departamentos a los que pertenece el usuario actual
$consulta_departamentos_usuario = "SELECT departamentos FROM usuarios WHERE ID = ?";
$statement = $conexion->prepare($consulta_departamentos_usuario);
$statement->bind_param("i", $id_usuario);
$statement->execute();
$resultado_departamentos_usuario = $statement->get_result();
$departamentos_usuario = [];
if ($resultado_departamentos_usuario->num_rows > 0) {
    // Obtener la cadena de departamentos del usuario
    $cadena_departamentos = $resultado_departamentos_usuario->fetch_assoc()['departamentos'];
    // Dividir la cadena por espacios y almacenar en un array
    $departamentos_usuario = explode(' ', $cadena_departamentos);
}

// Cerrar la conexión a la base de datos
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir Archivo</title>
    <style>
        body {
            background-color: #111; /* Negro */
            color: #fff; /* Texto blanco */
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h1, h2, p {
            color: #fff; /* Texto blanco */
        }
        input[type="text"], input[type="number"], button {
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
    
    <h2>Eres el usuario <?php echo $usuario_actual; ?></h2>
    <br>
    <h1>Compartir Archivo</h1>

    <h2>Enviar archivo a usuario</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="usuario_destino">Usuario destino:</label>
        <input type="text" id="usuario_destino" name="usuario_destino" required><br><br>
        <label for="archivo_usuario">Archivo a compartir:</label>
        <input type="text" id="archivo_usuario" name="archivo" required><br><br>
        <button type="submit">Enviar a usuario</button>
    </form>

    <h2>Enviar archivo a departamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="departamento_destino">Número de departamento destino:</label>
        <input type="text" id="departamento_destino" name="departamento_destino" required><br><br>
        <label for="archivo_departamento">Archivo a compartir:</label>
        <input type="text" id="archivo_departamento" name="archivo" required><br><br>
        <button type="submit">Enviar a departamento</button>
    </form>

    <h2>Ficheros recibidos de otros usuarios</h2>
    <?php
    // Directorio compartido del usuario actual
    $directorio_compartido_usuario = "compartidos/$usuario_actual";

    // Verificar si el directorio existe
    if (file_exists($directorio_compartido_usuario) && is_dir($directorio_compartido_usuario)) {
        // Obtener lista de archivos en el directorio compartido
        $archivos_compartidos = scandir($directorio_compartido_usuario);

        // Filtrar archivos para omitir . y ..
        $archivos_compartidos = array_diff($archivos_compartidos, array('..', '.'));

        // Mostrar enlaces de descarga para cada archivo
        foreach ($archivos_compartidos as $archivo) {
            $ruta_archivo = "$directorio_compartido_usuario/$archivo";
            echo "<p><a href='$ruta_archivo' download>$archivo</a></p>";
        }
    } else {
        echo "<h4><p>No hay archivos recibidos de otros usuarios.</p></h4>";
    }
    ?>

    <h2>Ficheros recibidos en cada departamento</h2>
    <?php
    // Mostrar los archivos en los departamentos a los que pertenece el usuario
    foreach ($departamentos_usuario as $departamento) {
        // Verificar que el valor del departamento sea numérico
        if (is_numeric($departamento)) {
            $directorio_compartido_departamento = "compartidos/$departamento";
            echo "<h3>Archivos del departamento $departamento</h3>";
            if (file_exists($directorio_compartido_departamento) && is_dir($directorio_compartido_departamento)) {
                $archivos_departamento = scandir($directorio_compartido_departamento);
                $archivos_departamento = array_diff($archivos_departamento, array('..', '.'));
                foreach ($archivos_departamento as $archivo) {
                    $ruta_archivo = "$directorio_compartido_departamento/$archivo";
                    echo "<p><a href='$ruta_archivo' download>$archivo</a></p>";
                }
            } else {
                echo "<p>No hay archivos recibidos en este departamento.</p>";
            }
        }
    }
    ?>
</body>
</html>
