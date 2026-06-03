<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salvatori - Portal</title>

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
                <img src="assets/img/logo-salvatori.png" alt="Salvatori Logo">
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff" width="100%">
            </div>

        </div>

    </header>


    <!-- SIDEBAR -->
    <?php
    $sidebar_activo = 'dashboard';
    require __DIR__ . '/php/sidebar.php';
    ?>


    <!-- CONTENIDO -->

    <main id="content">

        <section class="stats-grid">

            <div class="card">

                <div>

                    <div class="card-label">Ventas del mes</div>
                    <div class="card-value">$42,500</div>

                    <div class="card-note positive">
                        ↑ 12% mayor que el mes pasado
                    </div>

                </div>

                <div class="card-icon" style="background:#e2efda;color:#217346;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Stock en alerta</div>
                    <div class="card-value">8</div>

                    <div class="card-note">
                        Repuestos requieren atención
                    </div>

                </div>

                <div class="card-icon" style="background:#fff5b1;color:#b08800;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Vehículos en taller</div>
                    <div class="card-value">5</div>

                    <div class="card-note">
                        En espera de atención
                    </div>

                </div>

                <div class="card-icon" style="background:#ddf4ff;color:#0969da;">
                    <i class="fa-solid fa-car"></i>
                </div>

            </div>

        </section>


        <section class="analytics-row">

            <div class="panel-box">

                <div class="panel-header">

                    <h3>Tendencia de ingresos</h3>

                    <select class="period-filter">
                        <option>Semanal</option>
                        <option>Último mes</option>
                        <option>Trimestre</option>
                        <option>Año</option>
                    </select>

                </div>

                <div class="chart-visual">

                    <div class="bar" style="height:60%">
                        <span class="month-label">Lunes</span>
                    </div>

                    <div class="bar" style="height:40%">
                        <span class="month-label">Martes</span>
                    </div>

                    <div class="bar" style="height:75%">
                        <span class="month-label">Miercoles</span>
                    </div>

                    <div class="bar" style="height:55%">
                        <span class="month-label">Jueves</span>
                    </div>

                    <div class="bar" style="height:90%">
                        <span class="month-label">Viernes</span>
                    </div>

                    <div class="bar" style="height:45%">
                        <span class="month-label">Sabado</span>
                    </div>

                    <div class="bar" style="height:15%">
                        <span class="month-label">Domingo</span>
                    </div>

                </div>

            </div>


            <div class="panel-box">

                <div class="panel-header">
                    <h3>Servicios frecuentes y costo promedio</h3>
                </div>

                <div class="servicios-list">

                    <div class="servicio-item">
                        <span>Cambio de aceite</span>
                        <span>$850</span>
                    </div>

                    <div class="servicio-item">
                        <span>Alineación y balanceo</span>
                        <span>$600</span>
                    </div>

                    <div class="servicio-item">
                        <span>Diagnóstico general</span>
                        <span>$750</span>
                    </div>

                    <div class="servicio-item">
                        <span>Revisión de frenos</span>
                        <span>$650</span>
                    </div>

                    <div class="servicio-item">
                        <span>Escaneo OBD</span>
                        <span>$400</span>
                    </div>

                </div>

            </div>

        </section>


        <section class="table-box">

            <table>

                <thead>

                    <tr>
                        <th>ID Orden</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Estado</th>
                        <th>Monto</th>
                    </tr>

                </thead>

                <tbody>

                    <tr>
                        <td>#1001</td>
                        <td>Juan Pérez</td>
                        <td>Nissan Sentra</td>
                        <td><span class="status st-nuevo">Nuevo</span></td>
                        <td>$4500</td>
                    </tr>

                    <tr>
                        <td>#1002</td>
                        <td>María López</td>
                        <td>Toyota Corolla</td>
                        <td><span class="status st-pendiente">Pendiente</span></td>
                        <td>$1200</td>
                    </tr>

                    <tr>
                        <td>#1003</td>
                        <td>Carlos Ruiz</td>
                        <td>Honda Civic</td>
                        <td><span class="status st-terminado">Terminado</span></td>
                        <td>$13000</td>
                    </tr>

                    <tr>
                        <td>#1004</td>
                        <td>Ana Torres</td>
                        <td>Chevrolet Aveo</td>
                        <td><span class="status st-cancelado">Cancelado</span></td>
                        <td>$0</td>
                    </tr>

                </tbody>

            </table>

        </section>

    </main>

</body>
</html>
