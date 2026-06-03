<?php
/**
 * get_ot_detalles.php
 * Endpoint AJAX — devuelve los detalles de una orden de trabajo por su OT_Num.
 * GET ?ot_num=OT-XXXX
 * Devuelve JSON.
 */
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$ot_num = trim($_GET['ot_num'] ?? '');
if ($ot_num === '') {
    echo json_encode(['error' => 'OT_Num es requerido']);
    exit;
}

require_once __DIR__ . '/databaseS.php';

$stmt = mysqli_prepare($db,
    "SELECT o.*, m.Nombre AS MecanicoNombre
     FROM OT o
     LEFT JOIN Mecanicos m ON o.MecanicoID = m.MecanicoID
     WHERE o.OT_Num = ?
     LIMIT 1"
);

if (!$stmt) {
    echo json_encode(['error' => 'Error al preparar la consulta de base de datos']);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $ot_num);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ot = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$ot) {
    echo json_encode(['error' => 'Orden de trabajo no encontrada']);
    exit;
}

// Convertir campos numéricos de cadena a float/int para mayor precisión en JS
$ot['Horas_Facturadas'] = (float)$ot['Horas_Facturadas'];
$ot['Costo_MO_Hr'] = (float)$ot['Costo_MO_Hr'];
$ot['Costo_MO'] = (float)$ot['Costo_MO'];
$ot['Refacciones'] = (float)$ot['Refacciones'];
$ot['Total_Sugerido'] = (float)$ot['Total_Sugerido'];
$ot['Total_Cobrado'] = (float)$ot['Total_Cobrado'];
$ot['Odometer_In'] = (int)$ot['Odometer_In'];

echo json_encode($ot);
