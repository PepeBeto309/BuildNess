<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();
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
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?></p>
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff">
            </div>

        </div>

    </header>


    <!-- SIDEBAR -->
    <?php
    $sidebar_activo           = 'clientes_nuevo';
    $sidebar_clientes_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>


    <main id="content">

        <div class="form-page">

            <div class="form-title">
                Nuevo Cliente
            </div>

            <form name="formClientes" id="formClientes" method="post" action="php/clientes.php" novalidate>

                <div class="form-grid">

                    <div class="form-section">

                        <h3>Datos del Cliente</h3>

                        <div class="input-group">
                            <label>Nombres</label>
                            <input type="text" name="nombres" id="nombres" required autocomplete="given-name">
                        </div>

                        <div class="input-group">
                            <label>Apellido Paterno</label>
                            <input type="text" name="apPat" id="apPat" required autocomplete="family-name">
                        </div>

                        <div class="input-group">
                            <label>Apellido Materno</label>
                            <input type="text" name="apMat" id="apMat" required autocomplete="additional-name">
                        </div>

                        <div class="input-group">
                            <label>Teléfono</label>
                            <input type="tel" name="tel" id="tel" required placeholder="5512345678">
                        </div>

                        <div class="input-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" required autocomplete="email">
                        </div>

                    </div>


                    <div class="form-section">

                        <h3>Datos del Vehículo</h3>

                        <div class="input-group">
                            <label>Marca</label>
                            <input type="text" name="marca" id="marca">
                        </div>

                        <div class="input-group">
                            <label>Modelo</label>
                            <input type="text" name="modelo" id="modelo">
                        </div>

                        <div class="input-group">
                            <label>Año</label>
                            <input type="number" name="año" id="año">
                        </div>

                        <div class="input-group">
                            <label>Placa</label>
                            <input type="text" name="placa" id="placa" required maxlength="10" placeholder="ABC1234">
                        </div>

                        <div class="input-group">
                            <label>VIN</label>
                            <input type="text" name="VIN" id="VIN" required maxlength="17" placeholder="17 caracteres">
                        </div>

                    </div>

                </div>

                <div class="form-actions">

                    <input type="submit" value="Guardar" name="Guardar">

                    <button type="button" class="btn">
                        Editar
                    </button>

                </div>

            </form>

        </div>

    </main>

    <script src="js/validacion_cliente.js"></script>
</body>
</html>
