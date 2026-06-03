<?php
$sidebar_activo = $sidebar_activo ?? '';
$sidebar_inventario_abierto = !empty($sidebar_inventario_abierto);
$sidebar_clientes_abierto = !empty($sidebar_clientes_abierto);
$sidebar_ordenes_abierto = !empty($sidebar_ordenes_abierto);

$chk_inventario = $sidebar_inventario_abierto ? ' checked' : '';
$chk_clientes = $sidebar_clientes_abierto ? ' checked' : '';
$chk_ordenes = $sidebar_ordenes_abierto ? ' checked' : '';
$act_dashboard = $sidebar_activo === 'dashboard' ? ' active' : '';
?>
<aside id="sidebar">

    <div class="nav-section">GENERAL</div>

    <div class="menu-item">
        <a href="dashboard.php" class="menu-btn<?php echo $act_dashboard; ?>">
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
        <input type="checkbox" id="inventario" class="menu-check"<?php echo $chk_inventario; ?>>
        <label for="inventario" class="menu-btn">
            <span><i class="fa-solid fa-box"></i> Inventario</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="mostrar_Inventario.php">Stock de Refacciones</a></li>
            <li><a href="entradas_salidas.php">Entradas/Salidas</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="clientes" class="menu-check"<?php echo $chk_clientes; ?>>
        <label for="clientes" class="menu-btn">
            <span><i class="fa-solid fa-address-book"></i> Clientes</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="nuevo_cliente.php">Nuevo Cliente</a></li>
            <li><a href="mostrar_clientes.php">Directorio de Clientes</a></li>
            <li><a href="mostrar_vehiculos.php">Directorio de Vehiculos</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="ordenes" class="menu-check"<?php echo $chk_ordenes; ?>>
        <label for="ordenes" class="menu-btn">
            <span><i class="fa-solid fa-car"></i> Ordenes de Trabajo</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="nueva_orden.php">Nueva orden</a></li>
            <li><a href="#">Seguimiento de orden</a></li>
            <li><a href="#">Historial</a></li>
        </ul>
    </div>

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

    <div class="nav-section">SESIÓN</div>
    <div class="menu-item">
        <a href="php/logout.php" class="menu-btn">
            <span><i class="fa-solid fa-right-from-bracket" style="color: #ba1a1a;"></i> Cerrar Sesión</span>
        </a>
    </div>

</aside>

<div id="sidebar-overlay"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var menuToggle = document.getElementById('menu-toggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('is-visible');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('is-visible');
        });

        // Cerrar sidebar si se hace clic en un enlace en móviles
        var sidebarLinks = sidebar.querySelectorAll('.menu-btn:not(label), .submenu a');
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('is-visible');
            });
        });
    }
});
</script>

