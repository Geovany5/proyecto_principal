import mysql.connector
from pymongo import MongoClient

# Configuración de la base de datos MySQL
mysql_config = {
    'user': 'root',
    'password': '1942',
    'host': 'localhost',
}

# Conectarse a MySQL
mysql_conexion = mysql.connector.connect(**mysql_config)
mysql_cursor = mysql_conexion.cursor()

# Crear la base de datos 'proyecto'
mysql_cursor.execute("CREATE DATABASE IF NOT EXISTS proyecto")
mysql_cursor.execute("USE proyecto")

# Crear la tabla 'usuarios' si no existe
crear_tabla_usuarios = """
CREATE TABLE IF NOT EXISTS usuarios (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    departamentos VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
"""
mysql_cursor.execute(crear_tabla_usuarios)

# Crear la tabla 'archivos' si no existe
crear_tabla_archivos = """
CREATE TABLE IF NOT EXISTS archivos (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ruta_archivo VARCHAR(255),
    es_malicioso ENUM('Si', 'No'),
    reporte_archivo TEXT,
    nombre_archivo VARCHAR(255),
    usuario_id INT
)
"""
mysql_cursor.execute(crear_tabla_archivos)

# Crear la tabla 'departamentos' si no existe
crear_tabla_departamentos = """
CREATE TABLE IF NOT EXISTS departamentos (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    departamento INT UNIQUE NOT NULL,
    nombres_usuarios TEXT,
    usuarios_id TEXT
)
"""
mysql_cursor.execute(crear_tabla_departamentos)

# Cerrar la conexión a la base de datos MySQL
mysql_cursor.close()
mysql_conexion.close()

print("La base de datos y las tablas en MySQL han sido creadas correctamente.")

# Conectar a MongoDB
mongo_client = MongoClient("mongodb://localhost:27017/")
mongo_db = mongo_client.proyecto

# Crear colecciones en MongoDB
mongo_db.create_collection("usuarios")
mongo_db.create_collection("archivos")
mongo_db.create_collection("departamentos")

print("La base de datos y las colecciones en MongoDB han sido creadas correctamente.")
