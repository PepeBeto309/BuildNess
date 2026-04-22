<?php



$conexion = new mysqli("localhost", "root", "", "salvatori");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$accion = $_POST['Guardar'] ?? '';

if($accion == 'Guardar'){
    $nombres  = $_POST['nombres'] ?? '';
    $apPat    = $_POST['apPat'] ?? '';
    $apMat    = $_POST['apMat'] ?? '';
    $telefono = $_POST['tel'] ?? '';
    $email    = $_POST['email'] ?? '';

    $marca  = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $anio   = $_POST['año'] ?? '';
    $placa  = $_POST['placa'] ?? '';
    $vin    = $_POST['VIN'] ?? '';

    // INSERTAR CLIENTE
    $sqlCliente = "INSERT INTO clientes (nombres, apellido_paterno, apellido_materno, telefono, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sqlCliente);
    $stmt->bind_param("sssss", $nombres, $apPat, $apMat, $telefono, $email);

    if($stmt->execute()){
        // ¡ESTO ES CLAVE! Obtenemos el ID del cliente recién creado
        $cliente_id = $conexion->insert_id;

        // INSERTAR VEHÍCULO (usando el $cliente_id)
        $sqlVehiculo = "INSERT INTO vehiculos (cliente_id, marca, modelo, anio, placa, vin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtV = $conexion->prepare($sqlVehiculo);
        $stmtV->bind_param("isssss", $cliente_id, $marca, $modelo, $anio, $placa, $vin);
        
        if($stmtV->execute()){
            echo "<script>
            alert('Cliente y Vehiculo correctamente agregados');
            window.location.href = '../nuevo_cliente.html';
            </script>";
        } else {
            echo "Error en vehículo: " . $stmtV->error;
        }
        $stmtV->close();
    } else {
        echo "Error en cliente: " . $stmt->error;
    }
    $stmt->close();
}
$conexion->close();
?>