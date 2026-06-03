<?php

    require __DIR__ . "/php/funciones.php";

    $mensaje = '';

    if (isset($_GET['eliminar'])) {
        $id = $_GET['eliminar'];
        if ($id !== '' && borrar_clientes($id)) {
            header('Location: mostrar_clientes.php?msg=eliminado');
            exit;
        }
        $mensaje = 'No se pudo eliminar el cliente.';
    }

    if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
        $mensaje = 'Cliente eliminado correctamente.';
    }

    $consulta = obtener_clientes();

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
                <p>Bienvenido, Hernan Martinez</p>
            </div>
        </div>

        <div class="header-right">

            <div class="tool-icons">
                <i class="fa-regular fa-note-sticky"></i>
                <i class="fa-solid fa-triangle-exclamation">
                    <span class="notification-dot"></span>
                </i>
                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>
            </div>

            <div class="user-profile-circle">
                <img src="https://ui-avatars.com/api/?name=Hernan+Martinez&background=217346&color=fff">
            </div>

        </div>

    </header>


    <?php
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
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php while($cliente = mysqli_fetch_assoc($consulta)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente['Clave_Cliente']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['Nombre']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['Telefono']); ?></td>
                    <td><?php echo htmlspecialchars($cliente['Email']); ?></td>
                    <td>
                        <a href="mostrar_clientes.php?eliminar=<?php echo urlencode($cliente['Clave_Cliente']); ?>"
                           class="btn-tabla btn-tabla-borrar"
                           title="Eliminar cliente"
                           onclick="return confirm('¿Eliminar este cliente y sus vehículos?');">
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

