<?php
/**
 * guardar_ot.php
 * Procesa el formulario de nueva orden de trabajo (nueva_orden.php).
 * Almacena la orden en la tabla OT y calcula montos asociados.
 */
require_once __DIR__ . '/auth.php';
requerir_autenticacion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../nueva_orden.php');
    exit;
}

require_once __DIR__ . '/databaseS.php';

// Obtener datos
$ot_num          = trim($_POST['ot_num'] ?? '');
$clave_cliente   = trim($_POST['clave_cliente'] ?? '');
$clave_vehiculo  = trim($_POST['clave_vehiculo'] ?? '');
$km_entrada      = (int)($_POST['km_entrada'] ?? 0);
$descripcion     = trim($_POST['descripcion'] ?? '');
$horas_mano_obra = (float)($_POST['horas_mano_obra'] ?? 0.0);
$costo_hora      = (float)($_POST['costo_hora'] ?? 0.0);
$total_refacciones = (float)($_POST['total_refacciones'] ?? 0.0);
$mecanico_nombre = trim($_POST['mecanico'] ?? '');
$estado          = trim($_POST['estado'] ?? 'NUEVO');
$monto           = (float)($_POST['monto'] ?? 0.0);

// Validaciones
if ($ot_num === '' || $clave_cliente === '' || $clave_vehiculo === '') {
    header('Location: ../nueva_orden.php?error=campos_requeridos');
    exit;
}

// 1. Manejar el Mecánico
$mecanico_id = null;
if ($mecanico_nombre !== '') {
    // Buscar mecánico por nombre
    $stmt = mysqli_prepare($db, "SELECT MecanicoID FROM Mecanicos WHERE Nombre = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $mecanico_nombre);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($row) {
            $mecanico_id = $row['MecanicoID'];
        } else {
            // Crear un nuevo mecánico
            $mecanico_id = 'MEC-' . strtoupper(substr(uniqid(), -8));
            $stmt_ins = mysqli_prepare($db, "INSERT INTO Mecanicos (MecanicoID, Nombre, SueldoMensual, Activo, FechaAlta) VALUES (?, ?, 0.00, 1, CURDATE())");
            if ($stmt_ins) {
                mysqli_stmt_bind_param($stmt_ins, 'ss', $mecanico_id, $mecanico_nombre);
                mysqli_stmt_execute($stmt_ins);
                mysqli_stmt_close($stmt_ins);
            }
        }
    }
}

// 2. Calcular montos
$costo_mo = $horas_mano_obra * $costo_hora;
$total_sugerido = $costo_mo + $total_refacciones;
$total_cobrado = $monto;
$utilidad = $total_cobrado - $total_refacciones - $costo_mo;

// IVA y subtotal
$subtotal = $total_cobrado / 1.16;
$iva = $total_cobrado - $subtotal;

// Fecha actual
$fecha = date('Y-m-d');

// 3. Insertar la OT
$stmt_ot = mysqli_prepare($db, "INSERT INTO OT (
    OT_Num, Fecha, Clave_Cliente, Clave_Vehiculo, MecanicoID,
    Trabajo_Realizado, Horas_Facturadas, Costo_MO_Hr, Costo_MO, Refacciones,
    Total_Sugerido, Total_Cobrado, Utilidad, Estado, Odometer_In,
    Subtotal, Iva, WebRowID
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt_ot) {
    $web_row_id = 'WEB-' . uniqid();
    mysqli_stmt_bind_param(
        $stmt_ot,
        'ssssssdddddddsidds',
        $ot_num, $fecha, $clave_cliente, $clave_vehiculo, $mecanico_id,
        $descripcion, $horas_mano_obra, $costo_hora, $costo_mo, $total_refacciones,
        $total_sugerido, $total_cobrado, $utilidad, $estado, $km_entrada,
        $subtotal, $iva, $web_row_id
    );

    if (mysqli_stmt_execute($stmt_ot)) {
        mysqli_stmt_close($stmt_ot);
        header('Location: ../historial.php?success=ot_guardada');
        exit;
    } else {
        $error = mysqli_stmt_error($stmt_ot);
        mysqli_stmt_close($stmt_ot);
        header('Location: ../nueva_orden.php?error=db_error&detail=' . urlencode($error));
        exit;
    }
} else {
    header('Location: ../nueva_orden.php?error=prepare_error');
    exit;
}
