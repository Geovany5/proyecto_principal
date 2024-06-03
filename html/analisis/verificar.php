<?php
session_start();

// Verificar si la sesión no está iniciada
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login/index.html");
    exit;
}

// Obtener el nombre de usuario de la sesión
$usuario = $_SESSION['usuario'];

// Directorio donde se almacenarán los archivos cargados
$uploadDirectory = 'uploads/';

// Verificar si se ha enviado un archivo
if (isset($_FILES['fileInput'])) {
    $file = $_FILES['fileInput'];

    // Obtener información sobre el archivo
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Verificar si no hay errores
    if ($fileError === 0) {
        // Mover el archivo al directorio de destino
        $destination = $uploadDirectory . $fileName;
        move_uploaded_file($fileTmpName, $destination);

        // Ejecutar el script de Python
        $pythonScriptPath = 'script.py';  // Reemplaza con la ruta real de tu script Python
        $command = "python3 $pythonScriptPath $destination $usuario > /dev/null 2>&1 &";  // Ejecutar en segundo plano

        // Obtener resultados del script Python
        $results = shell_exec($command);

        // Esperar 10 segundos antes de redirigir
        sleep(15);

        // Redirigir a reporte.php con el nombre del archivo
        header("Location: reporte.php?archivo=" . urlencode($fileName));
        exit;
    } else {
        echo "Error al subir el archivo.";
    }
}
?>
