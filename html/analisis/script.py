import sys
import time
import requests
import json
import os
import mysql.connector

# Configuración de la base de datos
config = {
    'user': 'root',
    'password': '1942',
    'host': 'localhost',
}

nombre_db = "proyecto"

# Conecta a MySQL
conexion = mysql.connector.connect(**config)
cursor = conexion.cursor()

# Crea la base de datos si no existe
cursor.execute(f"CREATE DATABASE IF NOT EXISTS {nombre_db}")
cursor.execute(f"USE {nombre_db}")

# Crea la tabla 'archivos' si no existe
create_table_query = """
CREATE TABLE IF NOT EXISTS archivos (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255),
    ruta_archivo VARCHAR(255),
    es_malicioso ENUM('Si', 'No'),
    reporte_archivo TEXT,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(ID)
)
"""
cursor.execute(create_table_query)

#Pon tu ApiKey aquí

params = {"apikey": ""}

url_normal = "https://www.virustotal.com/vtapi/v2/file/scan"
url_mas_32 = "https://www.virustotal.com/vtapi/v2/file/scan/upload_url"
url_reporte = "https://www.virustotal.com/vtapi/v2/file/report"

carpeta_raiz = "uploads"
carpeta_reporte = "reportes"
carpeta_maliciosos = "maliciosos"
carpeta_normales = "normales"

#nombre del usuario actual
nombre_usuario = sys.argv[2]

#consulta para obtener id del usuario en relacion al nombre de usuario
consulta_usuario_id = "SELECT ID FROM usuarios WHERE usuario = %s"
cursor.execute(consulta_usuario_id, (nombre_usuario,))
usuario_id = cursor.fetchone()[0]

# Crear subcarpetas específicas para el usuario
ruta_reporte_usuario = os.path.join(carpeta_reporte, nombre_usuario)
ruta_maliciosos_usuario = os.path.join(carpeta_maliciosos, nombre_usuario)
ruta_normales_usuario = os.path.join(carpeta_normales, nombre_usuario)

os.makedirs(ruta_reporte_usuario, exist_ok=True)
os.makedirs(ruta_maliciosos_usuario, exist_ok=True)
os.makedirs(ruta_normales_usuario, exist_ok=True)

# Asegurarse de que las subcarpetas tengan permisos 777
os.chmod(ruta_reporte_usuario, 0o777)
os.chmod(ruta_maliciosos_usuario, 0o777)
os.chmod(ruta_normales_usuario, 0o777)

archivos_encontrados = []   

for carpeta_actual, subcarpetas, archivos in os.walk(carpeta_raiz):
    for archivo in archivos:
        ruta_completa = os.path.abspath(os.path.join(carpeta_actual, archivo))
        archivos_encontrados.append(ruta_completa)

for archivo in archivos_encontrados:
    tamaño_archivo_bytes = os.path.getsize(archivo)
    tamaño_archivo_megabytes = tamaño_archivo_bytes / (1024.0 * 1024.0)

    if tamaño_archivo_megabytes >= 32:
        peticion_32 = requests.get(url_mas_32, params=params)
        if peticion_32.status_code == 200:
            peticion_32_json = peticion_32.json()
            upload_url = peticion_32_json['upload_url']
            with open(archivo, 'rb') as file:
                files = {'file': (os.path.basename(archivo), file)}
                peticion_32 = requests.post(upload_url, files=files)
                peticion_32_json = peticion_32.json()
                sha256 = peticion_32_json['sha256']

    if tamaño_archivo_megabytes < 32:
        with open(archivo, 'rb') as file:
            files = {'file': (os.path.basename(archivo), file)}
            peticion = requests.post(url_normal, files=files, params=params)
            peticion_json = peticion.json()
            sha256 = peticion_json["sha256"]

    params = {"apikey": "84c7ba12ac54fccf5fd234a0a86f1fecceced2be98339b897e3b95ea08ca1969", "resource": sha256}
    peticion_reporte = requests.get(url_reporte, params=params)
    peticion_reporte_json = peticion_reporte.json()

    nombre_archivo_reporte = os.path.basename(archivo) + "_reporte.txt"
    ruta_reporte_total = os.path.join(ruta_reporte_usuario, nombre_archivo_reporte)

    with open(ruta_reporte_total, "w", encoding="utf-8") as report:
        report.write(json.dumps(peticion_reporte_json, indent=4, ensure_ascii=False))

    positivos = peticion_reporte_json["positives"]

    if positivos > 0:
        ruta_nombre_maliciosos = os.path.join(ruta_maliciosos_usuario, os.path.basename(archivo))
        os.rename(archivo, ruta_nombre_maliciosos)
        print(f"Archivo {os.path.basename(archivo)} movido a {carpeta_maliciosos}")

    elif positivos == 0:
        ruta_nombre_normal = os.path.join(ruta_normales_usuario, os.path.basename(archivo))
        os.rename(archivo, ruta_nombre_normal)
        print(f"Archivo {os.path.basename(archivo)} movido a {carpeta_normales}")

    ruta_archivo_analizado = ruta_nombre_maliciosos if positivos > 0 else ruta_nombre_normal
    es_malicioso = 'Si' if positivos > 0 else 'No'
    ruta_absoluta_archivo_analizado = os.path.abspath(ruta_archivo_analizado)
    nombre_archivo = os.path.basename(archivo)
    insertar_registros = "INSERT INTO archivos (nombre_archivo, ruta_archivo, es_malicioso, reporte_archivo, usuario_id) VALUES (%s, %s, %s, %s, %s)"
    with open(ruta_reporte_total, 'r', encoding="utf-8") as reporte_abierto:
        reporte_texto = reporte_abierto.read()
        cursor.execute(insertar_registros, (nombre_archivo, ruta_absoluta_archivo_analizado, es_malicioso, reporte_texto, usuario_id))

    # Eliminar todos los archivos en la carpeta maliciosos/nombre_usuario
    carpeta_usuario_maliciosos = os.path.join(carpeta_maliciosos, nombre_usuario)
    if os.path.exists(carpeta_usuario_maliciosos) and os.path.isdir(carpeta_usuario_maliciosos):
        for archivo_malicioso in os.listdir(carpeta_usuario_maliciosos):
            ruta_archivo_malicioso = os.path.join(carpeta_usuario_maliciosos, archivo_malicioso)
            if os.path.isfile(ruta_archivo_malicioso):
                os.remove(ruta_archivo_malicioso)

    time.sleep(15)

print("El análisis se ha completado y la información se ha almacenado en la base de datos")
conexion.commit()
cursor.close()
conexion.close()
