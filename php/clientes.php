<?php

require __DIR__ . '/validaciones.php';

$conexion = new mysqli("localhost", "root", "", "salvatori");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$accion = $_POST['Guardar'] ?? '';

if ($accion === 'Guardar') {
    $datos = datos_cliente_sanitizados($_POST);
    $errores = validar_registro_cliente($datos);

    if (!empty($errores)) {
        $mensaje = implode('\\n', $errores);
        echo "<script>
            alert('Datos no válidos:\\n{$mensaje}');
            window.location.href = '../nuevo_cliente.html';
        </script>";
        $conexion->close();
        exit;
    }

    $nombres  = $datos['nombres'];
    $apPat    = $datos['apPat'];
    $apMat    = $datos['apMat'];
    $telefono = $datos['tel'];
    $email    = $datos['email'];
    $marca    = $datos['marca'];
    $modelo   = $datos['modelo'];
    $anio     = $datos['anio'];
    $placa    = $datos['placa'];
    $vin      = $datos['vin'];

    $sqlCliente = "INSERT INTO clientes (nombres, apellido_paterno, apellido_materno, telefono, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sqlCliente);
    $stmt->bind_param("sssss", $nombres, $apPat, $apMat, $telefono, $email);

    if ($stmt->execute()) {
        $cliente_id = $conexion->insert_id;

        $sqlVehiculo = "INSERT INTO vehiculos (cliente_id, marca, modelo, anio, placa, vin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtV = $conexion->prepare($sqlVehiculo);
        $stmtV->bind_param("isssss", $cliente_id, $marca, $modelo, $anio, $placa, $vin);

        if ($stmtV->execute()) {
            echo "<script>
            alert('Cliente y vehículo correctamente agregados');
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
