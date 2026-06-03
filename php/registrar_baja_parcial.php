<?php
/**
 * registrar_baja_parcial.php
 * Procesa la deducción parcial de una refacción del stock.
 * Reduce la cantidad en la fila OT-SYSTEM de la refacción y añade una fila con la OT de salida.
 */
require_once __DIR__ . '/auth.php';
requerir_autenticacion();
require_once __DIR__ . '/databaseS.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../entradas_salidas.php');
    exit;
}

$codigo_refaccion = trim($_POST['codigo_refaccion'] ?? '');
$cantidad_baja    = (int)($_POST['cantidad'] ?? 0);
$tipo_baja        = trim($_POST['tipo_baja'] ?? 'general'); // 'ot' o 'general'
$ot_num           = trim($_POST['ot_num'] ?? '');

if ($codigo_refaccion === '' || $cantidad_baja <= 0) {
    header('Location: ../entradas_salidas.php?error=datos_invalidos');
    exit;
}

if ($tipo_baja === 'ot' && $ot_num === '') {
    header('Location: ../entradas_salidas.php?error=ot_requerida');
    exit;
}

if ($tipo_baja === 'general') {
    $ot_num = 'OT-BAJA';
}

// 1. Consultar el artículo en el inventario activo (OT-SYSTEM)
$stmt_check = mysqli_prepare($db, "SELECT Pieza, Cantidad, Tipo, Precio_Unitario FROM Inventario WHERE Codigo = ? AND OT_Num = 'OT-SYSTEM' LIMIT 1");
if (!$stmt_check) {
    header('Location: ../entradas_salidas.php?error=db_error');
    exit;
}
mysqli_stmt_bind_param($stmt_check, 's', $codigo_refaccion);
mysqli_stmt_execute($stmt_check);
$res = mysqli_stmt_get_result($stmt_check);
$refaccion = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt_check);

if (!$refaccion) {
    header('Location: ../entradas_salidas.php?error=no_existe');
    exit;
}

$cantidad_disponible = (int)$refaccion['Cantidad'];

if ($cantidad_baja > $cantidad_disponible) {
    header('Location: ../entradas_salidas.php?error=cantidad_insuficiente&disponible=' . $cantidad_disponible);
    exit;
}

// 2. Realizar las operaciones en una transacción
mysqli_begin_transaction($db);

try {
    // a. Decrementar la cantidad del inventario activo
    $stmt_dec = mysqli_prepare($db, "UPDATE Inventario SET Cantidad = Cantidad - ? WHERE Codigo = ? AND OT_Num = 'OT-SYSTEM'");
    if (!$stmt_dec) {
        throw new Exception("Error preparing update query");
    }
    mysqli_stmt_bind_param($stmt_dec, 'is', $cantidad_baja, $codigo_refaccion);
    mysqli_stmt_execute($stmt_dec);
    mysqli_stmt_close($stmt_dec);

    // b. Insertar registro de la baja
    $fecha = date('Y-m-d');
    $stmt_ins = mysqli_prepare($db, "INSERT INTO Inventario (Codigo, Fecha_Salida, OT_Num, Pieza, Cantidad, Precio_Unitario, Tipo, Total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_ins) {
        throw new Exception("Error preparing insert query");
    }
    
    $total_costo = (float)$refaccion['Precio_Unitario'] * $cantidad_baja;
    
    mysqli_stmt_bind_param($stmt_ins, 'ssssidsd', 
        $codigo_refaccion, 
        $fecha, 
        $ot_num, 
        $refaccion['Pieza'], 
        $cantidad_baja, 
        $refaccion['Precio_Unitario'], 
        $refaccion['Tipo'],
        $total_costo
    );
    mysqli_stmt_execute($stmt_ins);
    mysqli_stmt_close($stmt_ins);

    mysqli_commit($db);
    header('Location: ../entradas_salidas.php?msg=baja_ok');
    exit;
} catch (\Exception $e) {
    mysqli_rollback($db);
    header('Location: ../entradas_salidas.php?error=transaction_error');
    exit;
}
