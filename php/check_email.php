<?php
/**
 * check_email.php
 * Endpoint AJAX para verificar en tiempo real si un correo ya está registrado.
 * Acepta: POST { email: string }
 * Devuelve: JSON { available: bool, message: string }
 */

header('Content-Type: application/json; charset=utf-8');

// Solo permitir peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$email = trim($_POST['email'] ?? '');

// Validar que se proporcionó un correo
if ($email === '') {
    echo json_encode(['available' => null, 'message' => '']);
    exit;
}

// Validar formato básico del correo
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['available' => false, 'message' => 'Formato de correo no válido.']);
    exit;
}

// Conectar a la base de datos
require_once __DIR__ . '/databaseS.php';

// Verificar si el correo ya existe
$stmt = mysqli_prepare($db, "SELECT id_usuario FROM Usuarios WHERE email = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$exists  = mysqli_fetch_assoc($result) !== null;
mysqli_stmt_close($stmt);

if ($exists) {
    echo json_encode([
        'available' => false,
        'message'   => 'Este correo ya está registrado.'
    ]);
} else {
    echo json_encode([
        'available' => true,
        'message'   => 'Correo disponible.'
    ]);
}
