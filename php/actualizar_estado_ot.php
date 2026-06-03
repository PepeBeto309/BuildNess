<?php
/**
 * actualizar_estado_ot.php
 * Endpoint AJAX — actualiza el estado de una orden de trabajo por su OT_Num.
 * POST (URL-encoded o JSON)
 * Devuelve JSON { success: true/false, error: '...' }
 */
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Leer parámetros de POST
$ot_num = trim($_POST['ot_num'] ?? '');
$estado = trim($_POST['estado'] ?? '');

if ($ot_num === '' || $estado === '') {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos. Se requiere ot_num y estado.']);
    exit;
}

require_once __DIR__ . '/databaseS.php';

// Validar que la orden existe
$stmt_check = mysqli_prepare($db, "SELECT OT_Num FROM OT WHERE OT_Num = ? LIMIT 1");
if ($stmt_check) {
    mysqli_stmt_bind_param($stmt_check, 's', $ot_num);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    $exists = mysqli_stmt_num_rows($stmt_check) > 0;
    mysqli_stmt_close($stmt_check);

    if (!$exists) {
        echo json_encode(['success' => false, 'error' => 'La orden especificada no existe.']);
        exit;
    }
}

// Actualizar estado
$stmt = mysqli_prepare($db, "UPDATE OT SET Estado = ? WHERE OT_Num = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la actualización en la base de datos']);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $estado, $ot_num);
$success = mysqli_stmt_execute($stmt);
$error_msg = $success ? null : mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => $success,
    'error' => $error_msg
]);
