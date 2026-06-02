<?php

$conexion = mysqli_connect('localhost','root','','salvatori');

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$accion = $_POST['Guardar'];


?>