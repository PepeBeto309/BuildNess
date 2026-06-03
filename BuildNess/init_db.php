<?php
/**
 * Script de inicialización - ELIMINAR después de uso.
 * Acceder desde el navegador: http://localhost:8080/init_db.php
 * Crea la tabla Usuarios si no existe y resetea/inserta el admin por defecto.
 */
require_once __DIR__ . '/php/databaseS.php';

header('Content-Type: text/html; charset=utf-8');
echo '<style>body{font-family:monospace;padding:2em;background:#111;color:#0f0;} a{color:cyan;} .ok{color:lime;} .err{color:red;} .warn{color:yellow;}</style>';
echo '<h2 style="color:#fff">🛠️ Inicialización de Usuarios - BuildNess</h2>';

// 1. Crear tabla Usuarios si no existe
$sql_crear = "CREATE TABLE IF NOT EXISTS Usuarios (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if (mysqli_query($db, $sql_crear)) {
    echo '<p class="ok">✅ Tabla Usuarios verificada/creada correctamente.</p>';
} else {
    echo '<p class="err">❌ Error al crear tabla: ' . mysqli_error($db) . '</p>';
    exit;
}

// 2. Verificar si ya existe el administrador
$res = mysqli_query($db, "SELECT * FROM Usuarios WHERE email = 'admin@taller.com' LIMIT 1");
$admin = mysqli_fetch_assoc($res);

if ($admin) {
    // Ya existe: actualizar la contraseña a Admin2025* en texto plano
    $stmt = mysqli_prepare($db, "UPDATE Usuarios SET password = ?, activo = 1 WHERE email = 'admin@taller.com'");
    $pass = 'Admin2025*';
    mysqli_stmt_bind_param($stmt, 's', $pass);
    if (mysqli_stmt_execute($stmt)) {
        echo '<p class="warn">⚠️ Ya existía el administrador. Contraseña reseteada a <strong>Admin2025*</strong> (texto plano).</p>';
    } else {
        echo '<p class="err">❌ Error al actualizar contraseña: ' . mysqli_error($db) . '</p>';
    }
    mysqli_stmt_close($stmt);
} else {
    // No existe: insertar
    $stmt = mysqli_prepare($db, "INSERT INTO Usuarios (nombre, email, password, rol, activo) VALUES ('Administrador', 'admin@taller.com', 'Admin2025*', 'administrador', 1)");
    if (mysqli_stmt_execute($stmt)) {
        echo '<p class="ok">✅ Administrador insertado con email: <strong>admin@taller.com</strong> y contraseña: <strong>Admin2025*</strong></p>';
    } else {
        echo '<p class="err">❌ Error al insertar admin: ' . mysqli_error($db) . '</p>';
    }
    mysqli_stmt_close($stmt);
}

// 3. Verificación final
echo '<h3 style="color:#ff0">Verificación final:</h3><pre>';
$res = mysqli_query($db, "SELECT * FROM Usuarios WHERE email = 'admin@taller.com' LIMIT 1");
$row = mysqli_fetch_assoc($res);
if ($row) {
    echo "Email:    " . $row['email'] . "\n";
    echo "Nombre:   " . $row['nombre'] . "\n";
    echo "Password: " . $row['password'] . "\n";
    echo "Rol:      " . $row['rol'] . "\n";
    echo "Activo:   " . ($row['activo'] ? 'Sí' : 'No') . "\n";
}
echo '</pre>';

echo '<p class="ok">✅ ¡Listo! Ya puedes iniciar sesión.</p>';
echo '<p><strong>Email:</strong> admin@taller.com<br><strong>Contraseña:</strong> Admin2025*</p>';
echo '<br><a href="index.php">👉 Ir a la página principal para iniciar sesión</a>';
echo '<br><br><p class="warn">⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo (init_db.php) después de usarlo por seguridad.</p>';
?>
