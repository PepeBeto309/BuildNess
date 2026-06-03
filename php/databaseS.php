<?php

// Lee las credenciales de las variables de entorno (Docker).
// Si no existen (entorno XAMPP local), usa los valores por defecto.
$db_host = getenv('DB_HOST')     ?: 'localhost';
$db_user = getenv('DB_USER')     ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME')     ?: 'salvatori';

$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$db) {
    http_response_code(500);
    echo 'Error de conexión a la base de datos: ' . mysqli_connect_error();
    exit;
}

mysqli_set_charset($db, 'utf8mb4');
?>