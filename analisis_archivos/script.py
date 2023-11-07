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

nombre_db = "resultados_analisis"

# Conecta a mysql
conexion = mysql.connector.connect(**config)
cursor = conexion.cursor()

# Crea la base de datos si no existe
cursor.execute(f"CREATE DATABASE IF NOT EXISTS {nombre_db}")
cursor.execute(f"USE {nombre_db}")

# Crea la tabla 'archivos' si no existe
create_table_query = """
CREATE TABLE IF NOT EXISTS archivos (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ruta_archivo VARCHAR(255),
    archivo BLOB,
    es_malicioso ENUM('Si', 'No'),
    reporte_archivo TEXT
)
""" #Todo esto se ignorará si la base de datos y las tablas ya están creadas, por lo que solo se ejecutará la primera vez
cursor.execute(create_table_query) #ejecuta


params = {"apikey":"84c7ba12ac54fccf5fd234a0a86f1fecceced2be98339b897e3b95ea08ca1969"}

url_normal = "https://www.virustotal.com/vtapi/v2/file/scan" #este endpoint se usará para subir los archivos que pesen menos de 32 mb
url_mas_32 = "https://www.virustotal.com/vtapi/v2/file/scan/upload_url" #Lo mismo, pero que pesen más de 32 MB
url_reporte = "https://www.virustotal.com/vtapi/v2/file/report" #Se usará para consultar el reporte

carpeta_raiz = r""

archivos_encontrados = []   #guarda las rutas absolutas de los archivos a analizar

# Recorre recursivamente la carpeta raíz y sus subcarpetas
for carpeta_actual, subcarpetas, archivos in os.walk(carpeta_raiz):
    for archivo in archivos:
        ruta_relativa = os.path.join(carpeta_actual, archivo)  # Ruta relativa
        ruta_completa = os.path.abspath(ruta_relativa)  # Convierte en ruta absoluta                #TO DO cambiar todo el programa por rutas relativas
        archivos_encontrados.append(ruta_completa)
        
for archivo in archivos_encontrados: #Recorre la lista con las rutas absolutas de los archivos
    tamaño_archivo_bytes = os.path.getsize(archivo) #miramos el tamaño del archivo
    tamaño_archivo_megabytes = tamaño_archivo_bytes / (1024.0 * 1024.0) # Lo pasamos a MB

    if tamaño_archivo_megabytes >= 32:
        peticion_32 = requests.get(url_mas_32, params=params) #realiza una peticion a para obtener un json con la url para subir el archivo
        if peticion_32.status_code == 200:
            peticion_32_json = peticion_32.json() # se transforma en un diccionario python
            upload_url = peticion_32_json['upload_url'] #se accede a la url que devuelve el json
            with open(archivo, 'rb') as file:
                files = {'file': (os.path.basename(archivo), file)}
                peticion_32 = requests.post(upload_url, files=files) #TO DO probar a modifica la variable para que sea una distinta a la anterior para que funcionen +32
                peticion_32_json = peticion_32.json()
                sha256 = peticion_32_json['sha256']

    if tamaño_archivo_megabytes < 32:
        with open(archivo, 'rb') as file:
            files = {'file': (os.path.basename(archivo), file)}
            peticion = requests.post(url_normal, files=files, params=params)
            peticion_json = peticion.json()
            sha256 = peticion_json["sha256"]

    params = {"apikey":"84c7ba12ac54fccf5fd234a0a86f1fecceced2be98339b897e3b95ea08ca1969", "resource":sha256} #añadimos como parametro el sha256 extraído de la petición anterior
    peticion_reporte = requests.get(url_reporte, params=params) #se hace la peticion para obtener el reporte
    peticion_reporte_json = peticion_reporte.json() #lo formateamos para hacer el diccionario python

    carpeta_reporte = r"" #carpeta donde se almacenaran los reportes
    nombre_archivo_reporte = os.path.basename(archivo) + "_reporte.txt" #te da el nombre del archivo
    ruta_reporte_total = os.path.join(carpeta_reporte, nombre_archivo_reporte) #obtienes la ruta absoluta y el nombre del archivo

    with open(ruta_reporte_total, "w",  encoding="utf-8") as report:
        report.write(json.dumps(peticion_reporte_json, indent=4, ensure_ascii=False)) #se escribe el json del reporte

    positivos = peticion_reporte_json["positives"] #esta variable nos permite acceder a los resultados y saber si es positivo

    carpeta_maliciosos = r""
    carpeta_normales = r""
    
    if positivos > 0:
        ruta_nombre_maliciosos = os.path.join(carpeta_maliciosos, os.path.basename(archivo)) #une la ruta de la carpeta con el nombre del archivo
        os.rename(archivo, ruta_nombre_maliciosos) #lo mueve al destino
        print(f"Archivo {os.path.basename(archivo)} movido a {carpeta_maliciosos}")

    elif positivos == 0:
        ruta_nombre_normal = os.path.join(carpeta_normales, os.path.basename(archivo))
        os.rename(archivo, ruta_nombre_normal)
        print(f"Archivo {os.path.basename(archivo)} movido a {carpeta_normales}")

    ruta_archivo_analizado = ruta_nombre_maliciosos if positivos > 0 else ruta_nombre_normal #da la nueva ruta del archivo que se ha analizado
    es_malicioso = 'Si' if positivos > 0 else 'No' #Determina si el archivo es malicioso o no en una variable
    
    insertar_registros = "INSERT INTO archivos (ruta_archivo, archivo, es_malicioso, reporte_archivo) VALUES (%s, %s, %s, %s)"
    with open(ruta_archivo_analizado, 'rb') as archivo_abierto, open(ruta_reporte_total, 'r', encoding="utf-8") as reporte_abierto:
        archivo_blob = archivo_abierto.read()       #TO DO quitar el guardado del contenido en binario del ficheroen la db y crear nueva tabla con los reportes
        reporte_texto = reporte_abierto.read()
        cursor.execute(insertar_registros, (ruta_archivo_analizado, archivo_blob, es_malicioso, reporte_texto))
    
    time.sleep(15) #Hace que se suba un archivo cada 15 segundos

print("Analisis completado y información almacenada en la base de datos")
conexion.commit() #Se guarda el contenido
cursor.close()
conexion.close() #Se cierra la conexión fuera del bucle

