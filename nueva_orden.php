<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salvatori - Nueva Orden</title>

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
    $sidebar_activo = 'ordenes';
    $sidebar_ordenes_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main id="content">

        <div class="form-page">

            <div class="form-title">
                Nueva Orden de Trabajo
            </div>

            <form>

                <div class="ot-layout">

                    <div>

                        <div class="form-section">

                            <h3>Datos del Cliente</h3>

                            <div class="input-group">
                                <label>Cliente</label>
                                <select>
                                    <option></option>
                                </select>
                            </div>

                            <div class="input-group">
                                <label>Vehículo</label>
                                <select>
                                    <option></option>
                                </select>
                            </div>

                            <div class="input-group">
                                <label>Kilometraje</label>
                                <input type="number">
                            </div>

                        </div>


                        <div class="form-section">

                            <h3>Trabajo a realizar</h3>

                            <div class="input-group">
                                <textarea></textarea>
                            </div>

                        </div>


                        <div class="form-section">

                            <h3>Cotización</h3>

                            <div class="input-group">
                                <label>Horas de mano de obra</label>
                                <input type="number">
                            </div>

                            <div class="input-group">
                                <label>Total refacciones</label>
                                <input type="number">
                            </div>

                            <div class="input-group">
                                <label>Mecánico</label>
                                <select></select>
                            </div>

                        </div>

                    </div>


                    <div class="ot-side">

                        <h3>Datos OT</h3>

                        <div class="input-group">
                            <label>Estado</label>
                            <select>
                                <option>COTIZACIÓN</option>
                                <option>NUEVO</option>
                                <option>PENDIENTE</option>
                                <option>TERMINADO</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label>Monto</label>
                            <input type="number">
                        </div>

                        <div class="ot-actions">

                            <button>Agregar servicio</button>
                            <button>Facturar</button>
                            <button>Editar OT</button>
                            <button>Terminar</button>
                            <button>Cotizar</button>
                            <button>Generar PDF</button>

                        </div>

                    </div>

                </div>


                <div class="form-actions">

                    <button class="btn btn-primary">
                        Guardar OT
                    </button>

                </div>


                <div class="total-box">

                    <div>
                        <label>Total sugerido</label>
                        <br>
                        <input type="text" value="$0.00">
                    </div>

                    <div>
                        <label>Mano de obra total</label>
                        <br>
                        <input type="text" value="$0.00">
                    </div>

                </div>

            </form>

        </div>

    </main>

</body>
</html>
