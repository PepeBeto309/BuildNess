<?php
/**
 * Script de inicialización - BuildNess
 * ELIMINAR después de usarlo por seguridad.
 * Acceder: http://localhost:8080/init_db.php
 */
require_once __DIR__ . '/php/databaseS.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Init DB - BuildNess</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
    h1 { color: #60a5fa; margin-bottom: 1.5rem; font-size: 1.5rem; }
    h2 { color: #94a3b8; font-size: 1rem; margin: 1.5rem 0 0.5rem; }
    .card { background: #1e293b; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1rem; }
    .ok   { color: #4ade80; }
    .err  { color: #f87171; }
    .warn { color: #facc15; }
    .info { color: #60a5fa; }
    pre { background: #0f172a; padding: 1rem; border-radius: 0.5rem; font-size: 0.8rem; white-space: pre-wrap; word-break: break-all; }
    .btn { display: inline-block; margin-top: 1.5rem; padding: 0.75rem 2rem; background: #2563eb; color: #fff; 
           border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: background 0.2s; }
    .btn:hover { background: #1d4ed8; }
    .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 700; }
    .badge-ok { background: #14532d; color: #4ade80; }
    .badge-err { background: #7f1d1d; color: #f87171; }
    hr { border-color: #334155; margin: 1.5rem 0; }
</style>
</head>
<body>
<h1>🛠️ BuildNess — Inicialización de Base de Datos</h1>

<?php
$log = [];

function exec_sql($db, string $sql, string $desc): void {
    global $log;
    if (mysqli_query($db, $sql)) {
        $log[] = ['ok', "✅ $desc"];
    } else {
        $log[] = ['err', "❌ $desc — " . mysqli_error($db)];
    }
}

// ─────────────────────────────────────────────
// 1. Crear tabla Usuarios si no existe
// ─────────────────────────────────────────────
exec_sql($db, "
CREATE TABLE IF NOT EXISTS Usuarios (
  id_usuario    INT           NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100)  NOT NULL,
  email         VARCHAR(100)  NOT NULL,
  password      VARCHAR(255)  NOT NULL,
  rol           ENUM('administrador', 'usuario') NOT NULL DEFAULT 'usuario',
  activo        BOOLEAN       NOT NULL DEFAULT TRUE,
  fecha_alta    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_baja    DATETIME      NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", "Tabla Usuarios creada/verificada");

// ─────────────────────────────────────────────
// 2. Insertar o actualizar admin por defecto
// ─────────────────────────────────────────────
$res  = mysqli_query($db, "SELECT * FROM Usuarios WHERE email = 'admin@taller.com' LIMIT 1");
$admin = $res ? mysqli_fetch_assoc($res) : null;

if ($admin) {
    // Actualizar password a texto plano Admin2025*
    $stmt = mysqli_prepare($db, "UPDATE Usuarios SET password = 'Admin2025*', activo = 1, rol = 'administrador' WHERE email = 'admin@taller.com'");
    if (mysqli_stmt_execute($stmt)) {
        $log[] = ['warn', "⚠️ Admin ya existía — contraseña reseteada a <strong>Admin2025*</strong>"];
    } else {
        $log[] = ['err', "❌ No se pudo resetear contraseña: " . mysqli_error($db)];
    }
    mysqli_stmt_close($stmt);
} else {
    $stmt = mysqli_prepare($db, "INSERT INTO Usuarios (nombre, email, password, rol, activo) VALUES ('Administrador', 'admin@taller.com', 'Admin2025*', 'administrador', 1)");
    if (mysqli_stmt_execute($stmt)) {
        $log[] = ['ok', "✅ Administrador insertado correctamente"];
    } else {
        $log[] = ['err', "❌ Error insertando admin: " . mysqli_error($db)];
    }
    mysqli_stmt_close($stmt);
}

// ─────────────────────────────────────────────
// 3. Mostrar resultados
// ─────────────────────────────────────────────
echo '<div class="card">';
echo '<h2>📋 Resultados</h2><br>';
foreach ($log as [$type, $msg]) {
    $cls = $type === 'ok' ? 'ok' : ($type === 'err' ? 'err' : 'warn');
    echo "<p class='$cls' style='margin:0.4rem 0;'>$msg</p>";
}
echo '</div>';

// ─────────────────────────────────────────────
// 4. Verificación final del usuario admin
// ─────────────────────────────────────────────
$res  = mysqli_query($db, "SELECT * FROM Usuarios WHERE email = 'admin@taller.com' LIMIT 1");
$row  = $res ? mysqli_fetch_assoc($res) : null;

echo '<div class="card">';
echo '<h2>👤 Estado del Administrador</h2><br>';
if ($row) {
    $pass_ok = ($row['password'] === 'Admin2025*') || password_verify('Admin2025*', $row['password']);
    $id_col  = $row['id_usuario'] ?? $row['id'] ?? '?';
    echo "<p>ID: <span class='info'>$id_col</span></p>";
    echo "<p>Email: <span class='info'>" . htmlspecialchars($row['email']) . "</span></p>";
    echo "<p>Nombre: <span class='info'>" . htmlspecialchars($row['nombre']) . "</span></p>";
    echo "<p>Rol: <span class='info'>" . htmlspecialchars($row['rol']) . "</span></p>";
    echo "<p>Activo: <span class='" . ($row['activo'] ? 'ok' : 'err') . "'>" . ($row['activo'] ? 'Sí ✅' : 'No ❌') . "</span></p>";
    echo "<p>Contraseña Admin2025*: <span class='" . ($pass_ok ? 'ok' : 'err') . "'>" . ($pass_ok ? 'Válida ✅' : 'NO coincide ❌') . "</span></p>";
} else {
    echo "<p class='err'>❌ No se encontró el administrador. Revisa los errores arriba.</p>";
}
echo '</div>';

echo '<div class="card">';
echo '<h2>🔑 Credenciales de Acceso</h2><br>';
echo '<pre>Email:      admin@taller.com
Contraseña: Admin2025*
URL:        http://localhost:8080/index.php</pre>';
echo '</div>';

echo '<div class="card" style="background:#7f1d1d20;border:1px solid #7f1d1d;">';
echo '<p class="err">⚠️ <strong>SEGURIDAD:</strong> Elimina este archivo (<code>init_db.php</code>) y <code>debug_usuarios.php</code> después de usarlos.</p>';
echo '</div>';
?>

<a href="index.php" class="btn">→ Ir a la página principal</a>

</body>
</html>
