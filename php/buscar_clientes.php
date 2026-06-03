<?php
/**
 * buscar_clientes.php
 * Endpoint AJAX — busca clientes por nombre, teléfono o email.
 * GET ?q=texto (mínimo 2 caracteres)
 * Devuelve JSON array de clientes.
 */
require_once __DIR__ . '/auth.php';
if (!esta_autenticado()) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/databaseS.php';

$like = '%' . $q . '%';
$stmt = mysqli_prepare($db,
    "SELECT Clave_Cliente, Nombre, Telefono, Email
     FROM Clientes
     WHERE Clave_Cliente != 'CLI-SYSTEM'
       AND (Nombre LIKE ? OR Telefono LIKE ? OR Email LIKE ?)
     ORDER BY Nombre
     LIMIT 20"
);
if (!$stmt) { echo json_encode([]); exit; }

mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
mysqli_stmt_execute($stmt);
$res    = mysqli_stmt_get_result($stmt);
$result = [];
while ($row = mysqli_fetch_assoc($res)) {
    $result[] = $row;
}
mysqli_stmt_close($stmt);
echo json_encode($result);
