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
    $clave_cliente = trim($_POST['clave_cliente'] ?? '');
    $datos = datos_cliente_sanitizados($_POST);
    
    // Validar datos de cliente
    $errores = [];
    $camposNombre = [
        'nombres' => 'Nombres',
        'apPat' => 'Apellido paterno',
        'apMat' => 'Apellido materno',
    ];
    foreach ($camposNombre as $campo => $etiqueta) {
        $valor = trim($datos[$campo] ?? '');
        if ($valor === '' || !preg_match(PATRON_NOMBRE, $valor)) {
            $errores[] = "{$etiqueta}: solo letras, de 2 a 60 caracteres.";
        }
    }
    $telefono = trim($datos['tel'] ?? '');
    if ($telefono === '' || !preg_match(PATRON_TELEFONO, $telefono)) {
        $errores[] = 'Teléfono: ingrese 10 dígitos (opcional +52 al inicio).';
    }
    $email = trim($datos['email'] ?? '');
    if ($email === '' || !preg_match(PATRON_EMAIL, $email)) {
        $errores[] = 'Email: formato no válido.';
    }

    // El vehículo es opcional si estamos editando un cliente
    $tiene_vehiculo = false;
    if ($clave_cliente === '') {
        $tiene_vehiculo = true;
    } else {
        if (trim($datos['marca']) !== '' || trim($datos['modelo']) !== '' || trim($datos['anio']) !== '' || trim($datos['placa']) !== '' || trim($datos['vin']) !== '') {
            $tiene_vehiculo = true;
        }
    }

    if ($tiene_vehiculo) {
        $marca = trim($datos['marca'] ?? '');
        if ($marca === '' || !preg_match(PATRON_MARCA_MODELO, $marca)) {
            $errores[] = 'Marca: solo letras y números, hasta 40 caracteres.';
        }
        $modelo = trim($datos['modelo'] ?? '');
        if ($modelo === '' || !preg_match(PATRON_MARCA_MODELO, $modelo)) {
            $errores[] = 'Modelo: solo letras y números, hasta 40 caracteres.';
        }
        $anio = trim((string) ($datos['anio'] ?? ''));
        if ($anio === '' || !preg_match(PATRON_ANIO, $anio)) {
            $errores[] = 'Año: debe estar entre 1980 y 2035.';
        }
        $placa = strtoupper(trim($datos['placa'] ?? ''));
        if ($placa === '' || !preg_match(PATRON_PLACA, $placa)) {
            $errores[] = 'Placa: formato no válido (ej. ABC1234 o ABC-12-34).';
        }
        $vin = strtoupper(trim($datos['vin'] ?? ''));
        if ($vin === '' || !preg_match(PATRON_VIN, $vin)) {
            $errores[] = 'VIN: debe tener exactamente 17 caracteres alfanuméricos.';
        }
    }

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
    $nombreCompleto = trim("$nombres $apPat $apMat");

    if ($clave_cliente !== '') {
        // ACTUALIZAR cliente existente
        $sqlCliente = "UPDATE Clientes SET Nombre = ?, Telefono = ?, Email = ? WHERE Clave_Cliente = ?";
        $stmt = $conexion->prepare($sqlCliente);
        $stmt->bind_param("ssss", $nombreCompleto, $telefono, $email, $clave_cliente);

        if ($stmt->execute()) {
            $stmt->close();
            if ($tiene_vehiculo) {
                // Registrar nuevo vehículo
                $clave_vehiculo = substr("VEH-" . strtoupper(uniqid()), 0, 20);
                $marca   = $datos['marca'];
                $modelo  = $datos['modelo'];
                $anio    = $datos['anio'];
                $placa   = $datos['placa'];
                $vin     = $datos['vin'];

                $sqlVehiculo = "INSERT INTO Vehiculos (Clave_Vehiculo, Clave_Cliente, Marca, Modelo, Anio, Placa, Vin) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtV = $conexion->prepare($sqlVehiculo);
                $stmtV->bind_param("ssssiss", $clave_vehiculo, $clave_cliente, $marca, $modelo, $anio, $placa, $vin);
                if ($stmtV->execute()) {
                    $stmtV->close();
                    echo "<script>
                    alert('Cambios guardados y nuevo vehículo registrado exitosamente');
                    window.location.href = '../nuevo_cliente.php';
                    </script>";
                } else {
                    echo "Error en vehículo: " . $stmtV->error;
                    $stmtV->close();
                }
            } else {
                echo "<script>
                alert('Datos del cliente actualizados exitosamente');
                window.location.href = '../nuevo_cliente.php';
                </script>";
            }
        } else {
            echo "Error en cliente: " . $stmt->error;
            $stmt->close();
        }
    } else {
        // INSERTAR cliente nuevo + vehículo nuevo
        $clave_cliente = substr("CLI-" . strtoupper(uniqid()), 0, 20);
        $clave_vehiculo = substr("VEH-" . strtoupper(uniqid()), 0, 20);
        $marca   = $datos['marca'];
        $modelo  = $datos['modelo'];
        $anio    = $datos['anio'];
        $placa   = $datos['placa'];
        $vin     = $datos['vin'];

        $sqlCliente = "INSERT INTO Clientes (Clave_Cliente, Nombre, Telefono, Email) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sqlCliente);
        $stmt->bind_param("ssss", $clave_cliente, $nombreCompleto, $telefono, $email);

        if ($stmt->execute()) {
            $stmt->close();
            $sqlVehiculo = "INSERT INTO Vehiculos (Clave_Vehiculo, Clave_Cliente, Marca, Modelo, Anio, Placa, Vin) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtV = $conexion->prepare($sqlVehiculo);
            $stmtV->bind_param("ssssiss", $clave_vehiculo, $clave_cliente, $marca, $modelo, $anio, $placa, $vin);

            if ($stmtV->execute()) {
                $stmtV->close();
                echo "<script>
                alert('Cliente y vehículo correctamente agregados');
                window.location.href = '../nuevo_cliente.php';
                </script>";
            } else {
                echo "Error en vehículo: " . $stmtV->error;
                $stmtV->close();
            }
        } else {
            echo "Error en cliente: " . $stmt->error;
            $stmt->close();
        }
    }
}

$conexion->close();
