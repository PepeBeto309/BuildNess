<?php
/**
 * ot_activas_vehiculo.php
 * Endpoint AJAX — devuelve las órdenes de trabajo activas de un vehículo.
 * GET ?clave=VEH-XXX
 * Devuelve JSON array de OTs.
 */
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$clave = trim($_GET['clave'] ?? '');
if ($clave === '') {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/databaseS.php';

$stmt = mysqli_prepare($db,
    "SELECT OT_Num, Fecha, Estado, Trabajo_Realizado
     FROM OT
     WHERE Clave_Vehiculo = ? 
       AND Estado != 'TERMINADO' 
       AND Estado != 'CANCELADO'
       AND OT_Num NOT IN ('OT-SYSTEM', 'OT-BAJA')
     ORDER BY Fecha DESC, OT_Num DESC"
);

if (!$stmt) {
    echo json_encode([]);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $clave);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$result = [];
while ($row = mysqli_fetch_assoc($res)) {
    $result[] = $row;
}
mysqli_stmt_close($stmt);

echo json_encode($result);
