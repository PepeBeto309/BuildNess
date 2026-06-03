<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();

require __DIR__ . '/php/funciones.php';

$entradas = obtener_movimientos_inventario('entrada');
$salidas = obtener_movimientos_inventario('salida');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salvatori - Entradas y Salidas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>

    <header id="main-header">
        <div class="logo-area">
            <button id="menu-toggle" class="menu-toggle-btn" aria-label="Abrir menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="logo-box">
                <img src="assets/img/logo-salvatori.png" alt="Salvatori">
            </div>
            <div>
                <h1>Salvatori</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?></p>
            </div>
        </div>
        <div class="header-right">
            <div class="tool-icons">
                <i class="fa-regular fa-note-sticky"></i>
                <i class="fa-solid fa-triangle-exclamation"><span class="notification-dot"></span></i>
                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>
            </div>
            <div class="user-profile-circle">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff" alt="">
            </div>
        </div>
    </header>

    <?php
    $sidebar_activo             = 'inventario_entradas';
    $sidebar_inventario_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main class="inventario-main">
        <div class="inventario-toolbar">
            <h2><i class="fa-solid fa-right-left"></i> Entradas y salidas</h2>
            <a href="mostrar_Inventario.php" class="btn-inventario">
                <i class="fa-solid fa-boxes-stacked"></i> Volver al stock
            </a>
        </div>

        <section class="movimientos-seccion">
            <h3 class="movimientos-titulo movimientos-titulo--entrada">
                <i class="fa-solid fa-arrow-down"></i> Altas (entradas)
            </h3>
            <table class="tabla-salvatori">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($entradas && mysqli_num_rows($entradas) > 0): ?>
                        <?php while ($fila = mysqli_fetch_assoc($entradas)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['registrado_en'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $fila['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nota'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="inventario-tabla-vacio">No hay altas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="movimientos-seccion">
            <h3 class="movimientos-titulo movimientos-titulo--salida">
                <i class="fa-solid fa-arrow-up"></i> Bajas (salidas)
            </h3>
            <table class="tabla-salvatori">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($salidas && mysqli_num_rows($salidas) > 0): ?>
                        <?php while ($fila = mysqli_fetch_assoc($salidas)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['registrado_en'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $fila['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nota'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="inventario-tabla-vacio">No hay bajas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>

</html>
