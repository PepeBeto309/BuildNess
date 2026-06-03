<?php
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) {
    http_response_code(403);
    die("Acceso denegado. Por favor, inicie sesión.");
}

require __DIR__ . '/validaciones.php';

// Credenciales desde variables de entorno (Docker) con fallback para XAMPP
$db_host = getenv('DB_HOST')     ?: 'localhost';
$db_user = getenv('DB_USER')     ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME')     ?: 'salvatori';

$conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
$conexion->set_charset('utf8mb4');

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
            window.location.href = '../nuevo_cliente.php';
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

    $nombreCompleto = trim("$nombres $apPat $apMat");
    $clave_cliente = substr("CLI-" . strtoupper(uniqid()), 0, 20);
    $clave_vehiculo = substr("VEH-" . strtoupper(uniqid()), 0, 20);

    $sqlCliente = "INSERT INTO Clientes (Clave_Cliente, Nombre, Telefono, Email) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sqlCliente);
    $stmt->bind_param("ssss", $clave_cliente, $nombreCompleto, $telefono, $email);

    if ($stmt->execute()) {
        $sqlVehiculo = "INSERT INTO Vehiculos (Clave_Vehiculo, Clave_Cliente, Marca, Modelo, Anio, Placa, Vin) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtV = $conexion->prepare($sqlVehiculo);
        $stmtV->bind_param("ssssiss", $clave_vehiculo, $clave_cliente, $marca, $modelo, $anio, $placa, $vin);

        if ($stmtV->execute()) {
            echo "<script>
            alert('Cliente y vehículo correctamente agregados');
            window.location.href = '../nuevo_cliente.php';
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
