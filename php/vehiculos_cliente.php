<?php
/**
 * vehiculos_cliente.php
 * Endpoint AJAX — devuelve los vehículos de un cliente.
 * GET ?clave=CLI-XXX
 * Devuelve JSON array de vehículos.
 */
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=utf-8');

$clave = trim($_GET['clave'] ?? '');
if ($clave === '') { echo json_encode([]); exit; }

require_once __DIR__ . '/databaseS.php';

$stmt = mysqli_prepare($db,
    "SELECT Clave_Vehiculo, Marca, Modelo, Anio, Placa, Vin
     FROM Vehiculos
     WHERE Clave_Cliente = ? AND Clave_Vehiculo != 'VEH-SYSTEM'
     ORDER BY Marca, Modelo"
);
if (!$stmt) { echo json_encode([]); exit; }

mysqli_stmt_bind_param($stmt, 's', $clave);
mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$result = [];
while ($row = mysqli_fetch_assoc($res)) {
    $result[] = $row;
}
mysqli_stmt_close($stmt);
echo json_encode($result);
