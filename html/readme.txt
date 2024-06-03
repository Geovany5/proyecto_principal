Para utilizar este repositorio necesitaremos necesitaremos tener intalado un servidor web Apache2, el cual interprete PHP.
Además deberemos instalar "php8.1", "php8.1-cli", "libapache2-mod-php8.1", "php-mysql" y las respectivas librerías que se utilizan en los scripts de python, que son "os, json, pymongo, mysql.connector, time y requests.
Para que funcione la conexión de los scripts con la base de datos mysql deberemos modificar el archivo "script_db.py" y cambiar la contraseña por la correspondiente, también tendremos que cambiar la contraseña en el archivo "analisis/script.py".
Tendremos que tener instalado mysql y cambiar la contraseña por la que vayamos a poner en los scripts de python, con el comando dentro de sql "alter user "root"@"localhost" identified with mysql_native_password by 'contraseña';", También podemos dejar la contraseña por defecto de los scripts configurando "1942" como contraseña para mysql.
Una vez hecho esto moveremos la carpeta web la cual contiene todo, a "/var/www" o mover todo lo que está dentro de la carpeta web a "/var/www/html", la cual es la carpeta por defecto.

Le daremos permisos 777 a la carpeta html: sudo chmod -R 777 /var/www/html

Antes de crear los usuarios crearemos la base de datos y sus tablas con: python3 script_db.py
La forma de utilizarlo será entrar al localhost desde el navegador web y crear una cuenta, después loguearnos con ella, subir archivos, etc... 
