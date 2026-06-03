<?php
/**
 * Script de diagnóstico temporal - ELIMINAR después de uso.
 * Acceder desde el navegador: http://localhost:8080/debug_usuarios.php
 */
require_once __DIR__ . '/php/databaseS.php';

header('Content-Type: text/html; charset=utf-8');
echo '<style>body{font-family:monospace;padding:2em;background:#111;color:#0f0;}</style>';
echo '<h2 style="color:#fff">🔍 Diagnóstico BuildNess - Tabla Usuarios</h2>';

// 1. Verificar conexión
echo '<h3 style="color:#ff0">1. Conexión a DB</h3>';
echo "Host: " . ($db ? '✅ Conectado' : '❌ Fallo') . "<br>";

// 2. Verificar si la tabla existe
$res = mysqli_query($db, "SHOW TABLES LIKE 'Usuarios'");
if (mysqli_num_rows($res) === 0) {
    echo '<p style="color:red">❌ La tabla <strong>Usuarios</strong> NO existe. Debes ejecutar el SQL de inicialización.</p>';
    echo '<p><a href="init_db.php" style="color:cyan">👉 Haz clic aquí para crear la tabla e insertar el admin por defecto</a></p>';
    exit;
}
echo '<p style="color:lime">✅ Tabla Usuarios existe</p>';

// 3. Estructura de la tabla
echo '<h3 style="color:#ff0">2. Estructura de la tabla</h3><pre>';
$res = mysqli_query($db, "DESCRIBE Usuarios");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | KEY: ' . $row['Key'] . "\n";
}
echo '</pre>';

// 4. Usuarios registrados
echo '<h3 style="color:#ff0">3. Usuarios en la tabla</h3><pre>';
$res = mysqli_query($db, "SELECT * FROM Usuarios");
if (mysqli_num_rows($res) === 0) {
    echo "⚠️ No hay usuarios registrados.\n";
    echo '<a href="init_db.php" style="color:cyan">👉 Insertar administrador por defecto</a>';
} else {
    while ($row = mysqli_fetch_assoc($res)) {
        echo "ID: " . ($row['id_usuario'] ?? $row['id'] ?? '?') . "\n";
        echo "Nombre: " . $row['nombre'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Password (primeros 20 chars): " . substr($row['password'], 0, 20) . "...\n";
        echo "Rol: " . ($row['rol'] ?? 'N/A') . "\n";
        echo "Activo: " . ($row['activo'] ?? 'N/A') . "\n";
        echo "---\n";
    }
}
echo '</pre>';

// 5. Test de credenciales directamente
echo '<h3 style="color:#ff0">4. Test de contraseña Admin2025*</h3>';
$res = mysqli_query($db, "SELECT * FROM Usuarios WHERE email = 'admin@taller.com' LIMIT 1");
if ($row = mysqli_fetch_assoc($res)) {
    $pass_plain = 'Admin2025*';
    $stored = $row['password'];
    echo "Contraseña almacenada: <span style='color:yellow'>" . htmlspecialchars($stored) . "</span><br>";
    
    $match_plain = ($pass_plain === $stored);
    $match_hash  = password_verify($pass_plain, $stored);
    
    echo "¿Coincide en texto plano?: " . ($match_plain ? '✅ SÍ' : '❌ NO') . "<br>";
    echo "¿Coincide como hash bcrypt?: " . ($match_hash ? '✅ SÍ' : '❌ NO') . "<br>";
    
    if (!$match_plain && !$match_hash) {
        echo '<p style="color:red">❌ La contraseña no coincide de ninguna manera. Usa el script de inicialización para resetearla.</p>';
        echo '<a href="init_db.php" style="color:cyan">👉 Resetear contraseña del administrador</a>';
    }
} else {
    echo '<p style="color:red">❌ No existe un usuario con email admin@taller.com</p>';
    echo '<a href="init_db.php" style="color:cyan">👉 Insertar admin por defecto</a>';
}
?>
