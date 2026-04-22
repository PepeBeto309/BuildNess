<?php

    require __DIR__ . "/php/funciones.php";

    $consulta = obtener_vehiculos();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salvatori - Nuevo Cliente</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>

    <header id="main-header">

        <div class="logo-area">
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

    <section>
        <div class="opciones-Inventario">
            <form>
                <input type="submit" name="Agregar" value="Agregar">
                <input type="submit" name="Editar" value="Editar">
                <input type="submit" name="Eliminar" value="Eliminar">
            </form>
        </div>
    </section>


    <!-- SIDEBAR (igual que index) -->
    <!-- SIDEBAR -->

    <aside id="sidebar">

        <div class="nav-section">GENERAL</div>

        <div class="menu-item">
            <a href="index.html" class="menu-btn active">
                <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
            </a>
        </div>

        <div class="menu-item">

            <input type="checkbox" id="desempeño" class="menu-check">

            <label for="desempeño" class="menu-btn">
                <span><i class="fa-solid fa-chart-line"></i> Desempeño</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="#">KPIs Personal</a></li>
                <li><a href="#">Eficiencia Taller</a></li>
            </ul>

        </div>


        <div class="nav-section">OPERACIONES</div>

        <div class="menu-item">

            <input type="checkbox" id="inventario" class="menu-check">

            <label for="inventario" class="menu-btn">
                <span><i class="fa-solid fa-box"></i> Inventario</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="mostrar_Inventario.php">Stock de Refacciones</a></li>
                <li><a href="#">Entradas/Salidas</a></li>
            </ul>

        </div>

        <div clss="menu-item">
            <input type="checkbox" id="cuponera" class="menu-check">

            <label for="cuponera" class="menu-btn">
                <span><i class="fa-solid fa-ticket"></i> Cuponera</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Promociones vigentes</a></li>
                <li><a href="#">Historial de Canjes</a></li>
            </ul>
        </div>

        <div clss="menu-item">
            <input type="checkbox" id="clientes" class="menu-check">

            <label for="clientes" class="menu-btn">
                <span><i class="fa-solid fa-address-book"></i> Clientes</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="nuevo_cliente.html">Nuevo Cliente</a></li>
                <li><a href="mostrar_clientes.php">Directorio de Clientes</a></li>
                <li><a href="mostrar_vehiculos.php">Directorio de Vehiculos</a></li>
            </ul>
        </div>


        <div class="menu-item">

            <input type="checkbox" id="ordenes" class="menu-check">

            <label for="ordenes" class="menu-btn">
                <span><i class="fa-solid fa-car"></i> Ordenes de Trabajo</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="nueva_orden.html">Nueva orden</a></li>
                <li><a href="#">Seguimiento de orden</a></li>
                <li><a href="#">Historial</a></li>
            </ul>

        </div>

        <!-- ADMINISTRACIÓN -->

        <div class="nav-section">ADMINISTRACIÓN</div>

        <div class="menu-item">
            <input type="checkbox" id="m-bit" class="menu-check">
            <label for="m-bit" class="menu-btn">
                <span><i class="fa-solid fa-book"></i> Bitácora</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Registro de Actividad</a></li>
            </ul>
        </div>


        <div class="menu-item">
            <input type="checkbox" id="m-fin" class="menu-check">
            <label for="m-fin" class="menu-btn">
                <span><i class="fa-solid fa-landmark"></i> Finanzas</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Flujo de Caja</a></li>
                <li><a href="#">Facturacion</a></li>
            </ul>
        </div>


        <div class="menu-item">
            <input type="checkbox" id="m-gas" class="menu-check">
            <label for="m-gas" class="menu-btn">
                <span><i class="fa-solid fa-file-invoice-dollar"></i> Gastos</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Proveedores</a></li>
            </ul>
        </div>


        <div class="nav-section">CONFIGURACIÓN</div>

        <div class="menu-item">
            <a class="menu-btn">
                <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
            </a>
        </div>

    </aside>

    </main>

</body>

</html>

