<?php

//$id = $_POST['id'];
function obtener_clientes() {
    try {
        require 'databaseS.php';


        $sql = "SELECT * FROM clientes;";

        $consulta = mysqli_query($db, $sql);

        return $consulta;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}

function obtener_vehiculos() {
    try {
        require 'databaseS.php';


        $sql = "SELECT * FROM vehiculos;";

        $consulta = mysqli_query($db, $sql);

        return $consulta;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}

function borrar_clientes($id) {
    try {
        require 'databaseS.php';


        $sql = "DELETE FROM clientes WHERE id = ? ;";

        $stmt = $conexion->prepare($sql);
        $stmt-> bind_param("i", $id);

        $resultado = $stmt->execute();

        $stmt->close();
        return $resultado;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}

function borrar_vehiculos($id) {
    try {
        require 'databaseS.php';


        $sql = "DELETE FROM vehiculos WHERE id = ? ;";

        $stmt = $conexion->prepare($sql);
        $stmt-> bind_param("i", $id);

        $resultado = $stmt->execute();

        $stmt->close();
        return $resultado;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}
?>