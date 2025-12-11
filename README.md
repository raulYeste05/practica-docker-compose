# Práctica Docker Compose – Balanceo con NGINX y MySQL

Esta práctica consiste en montar con Docker Compose:

- Un proxy inverso NGINX
- Tres contenedores PHP que muestran una encuesta
- Un contenedor PHP que muestra un chiste
- Una base de datos MySQL para guardar los votos
- Balanceo de carga entre los servidores de la encuesta

---

# 1. Servicios del proyecto

## Dockerfile del servicio de chistes

dockerfile
FROM php:7.4-apache
COPY . /var/www/html

## Dockerfile del servicio de encuesta
FROM php:7.4-apache
COPY . /var/www/html
RUN docker-php-ext-install pdo pdo_mysql


Este Dockerfile instala las extensiones necesarias para que PHP se conecte a MySQL.

## 2. Archivo NGINX (nginx.conf)
worker_processes 1;
events { worker_connections 1024; }

http {

    upstream encuesta_backend {
        server encuesta1 weight=3;  # Este servidor recibe más tráfico
        server encuesta2 weight=1;
        server encuesta3 weight=1;
    }

    upstream chiste_backend {
        server chiste;
    }

    server {
        listen 80;
        server_name www.freedomforlinares.com;

        location / {
            proxy_pass http://encuesta_backend;
        }
    }

    server {
        listen 80;
        server_name www.chiquito.com;

        location / {
            proxy_pass http://chiste_backend;
        }
    }
}


El weight hace que encuesta1 reciba más peticiones que encuesta2 y encuesta3.
Así comprobamos el balanceo de carga.

## 3. docker-compose.yml
version: "3.8"

services:

  # Proxy inverso
  proxy:
    image: nginx:latest
    container_name: proxy
    ports:
      - "80:80"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - encuesta1
      - encuesta2
      - encuesta3
      - chiste
      - db

  # Encuesta (3 réplicas)
  encuesta1:
    build: ./encuesta
    container_name: encuesta1
    depends_on:
      - db

  encuesta2:
    build: ./encuesta
    container_name: encuesta2
    depends_on:
      - db

  encuesta3:
    build: ./encuesta
    container_name: encuesta3
    depends_on:
      - db

  # Servicio de chistes
  chiste:
    build: ./chiste
    container_name: chiste

  # Base de datos MySQL
  db:
    image: mysql:8
    container_name: mysql-db
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: encuesta
    ports:
      - "3307:3306"
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:

## 4. Código PHP de la encuesta con MySQL
<?php

$host = "db";
$dbname = "encuesta";
$user = "root";
$pass = "root";

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voto = $_POST["voto"];
    $stmt = $pdo->prepare("INSERT INTO votos (voto) VALUES (:voto)");
    $stmt->execute(['voto' => $voto]);
}

$stmt = $pdo->query("SELECT 
                        SUM(voto='si') AS si, 
                        SUM(voto='no') AS no 
                    FROM votos");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>¿Independizar Linares de Jaén?</h1>";
echo '<form method="POST">';
echo '<button name="voto" value="si">Sí</button>';
echo '<button name="voto" value="no">No</button>';
echo '</form>';

echo "<h2>Resultados</h2>";
echo "Sí: {$data['si']}<br>";
echo "No: {$data['no']}<br>";

echo "<p>Servidor: " . gethostname() . "</p>";


## 5. Archivo hosts
Se deben añadir estas líneas al archivo de configuracion para usar los dominios:

127.0.0.1 www.freedomforlinares.com
127.0.0.1 www.chiquito.com

## 6. Ejecución del proyecto
Primero ejecutamos docker-compose up --build para cargar todos los contenedores luego podermos acceder a Encuesta:http://www.freedomforlinares.com  y a Chistes:http://www.chiquito.com.
Al recargar la página de la encuesta varias veces, veremos que:
La encuesta muestra el nombre del servidor (cada contenedor tiene uno distinto)
El servidor encuesta1 aparece más veces porque tiene weight=3 encuesta2 y encuesta3 aparecen menos veces porque tienen weight=1.
Esto demuestra que el balanceo funciona correctamente.

En la página:
http://www.chiquito.com
Se muestra un chiste aleatorio



