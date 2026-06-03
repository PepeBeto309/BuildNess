<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();

require __DIR__ . "/php/funciones.php";

$mensaje = '';

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($id !== '' && borrar_vehiculos($id)) {
        header('Location: mostrar_vehiculos.php?msg=eliminado');
        exit;
    }
    $mensaje = 'No se pudo eliminar el vehículo.';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $mensaje = 'Vehículo eliminado correctamente.';
}

$consulta = obtener_vehiculos();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salvatori - Nuevo Cliente</title>

    <link rel="stylesheet" href="css/styles.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="js/theme.js"></script>
</head>

<body>

    <header id="main-header">

        <div class="logo-area">
            <button id="menu-toggle" class="menu-toggle-btn" aria-label="Abrir menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="logo-box">
                <img src="assets/img/logo-salvatori.png">
            </div>

            <div>
                <h1>Salvatori</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?></p>
            </div>
        </div>

        <div class="header-right">
            <button id="theme-toggle" aria-label="Cambiar tema">
                <i class="fa-regular fa-moon"></i>
            </button>
            <div class="tool-icons">
                <i class="fa-regular fa-note-sticky"></i>
                <i class="fa-solid fa-triangle-exclamation">
                    <span class="notification-dot"></span>
                </i>
                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>
            </div>

            <div class="user-profile-circle">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff">
            </div>

        </div>

    </header>


    <?php
    $sidebar_activo           = 'vehiculos_dir';
    $sidebar_clientes_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>


    <main id="content">

    <?php if ($mensaje !== ''): ?>
        <p class="form-alerta"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <table class="tabla-salvatori">
        <thead>
            <tr>
                <th>Clave</th>
                <th>Dueño</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Año</th>
                <th>Placa</th>
                <th>VIN</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php while($vehiculo = mysqli_fetch_assoc($consulta)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vehiculo['Clave_Vehiculo']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['dueno']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['Marca']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['Modelo']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['Año']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['Placa']); ?></td>
                    <td><?php echo htmlspecialchars($vehiculo['Vin']); ?></td>
                    <td>
                        <a href="mostrar_vehiculos.php?eliminar=<?php echo urlencode($vehiculo['Clave_Vehiculo']); ?>"
                           class="btn-tabla btn-tabla-borrar"
                           title="Eliminar vehículo"
                           onclick="return confirm('¿Eliminar este vehículo?');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

    </main>

</body>

</html>

