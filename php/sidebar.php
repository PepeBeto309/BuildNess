<?php
$sidebar_activo = $sidebar_activo ?? '';

$sidebar_inventario_abierto = !empty($sidebar_inventario_abierto);
$sidebar_clientes_abierto   = !empty($sidebar_clientes_abierto);
$sidebar_ordenes_abierto    = !empty($sidebar_ordenes_abierto);

// Auto-abrir la sección cuando alguno de sus hijos está activo
if (in_array($sidebar_activo, ['inventario_stock', 'inventario_entradas'])) {
    $sidebar_inventario_abierto = true;
}
if (in_array($sidebar_activo, ['clientes_nuevo', 'clientes_dir', 'vehiculos_dir'])) {
    $sidebar_clientes_abierto = true;
}
if (in_array($sidebar_activo, ['ordenes_nueva', 'ordenes_seguimiento', 'ordenes_historial'])) {
    $sidebar_ordenes_abierto = true;
}

$chk_inventario = $sidebar_inventario_abierto ? ' checked' : '';
$chk_clientes   = $sidebar_clientes_abierto   ? ' checked' : '';
$chk_ordenes    = $sidebar_ordenes_abierto     ? ' checked' : '';

// Helpers para clases activas
function act_menu($sidebar_activo, $key) {
    return $sidebar_activo === $key ? ' active' : '';
}
function act_sub($sidebar_activo, $key) {
    return $sidebar_activo === $key ? ' active' : '';
}

$act_dashboard           = act_menu($sidebar_activo, 'dashboard');
$act_inv_stock           = act_sub($sidebar_activo, 'inventario_stock');
$act_inv_entradas        = act_sub($sidebar_activo, 'inventario_entradas');
$act_cli_nuevo           = act_sub($sidebar_activo, 'clientes_nuevo');
$act_cli_dir             = act_sub($sidebar_activo, 'clientes_dir');
$act_veh_dir             = act_sub($sidebar_activo, 'vehiculos_dir');
$act_ord_nueva           = act_sub($sidebar_activo, 'ordenes_nueva');
$act_ord_seguimiento     = act_sub($sidebar_activo, 'ordenes_seguimiento');
$act_ord_historial       = act_sub($sidebar_activo, 'ordenes_historial');
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

    <!-- Inventario -->
    <div class="menu-item">
        <input type="checkbox" id="inventario" class="menu-check"<?php echo $chk_inventario; ?>>
        <label for="inventario" class="menu-btn">
            <span><i class="fa-solid fa-box"></i> Inventario</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li>
                <a href="mostrar_Inventario.php" class="<?php echo $act_inv_stock; ?>">
                    <i class="fa-solid fa-cubes-stacked" style="width:14px;margin-right:4px;"></i>
                    Stock de Refacciones
                </a>
            </li>
            <li>
                <a href="entradas_salidas.php" class="<?php echo $act_inv_entradas; ?>">
                    <i class="fa-solid fa-right-left" style="width:14px;margin-right:4px;"></i>
                    Entradas/Salidas
                </a>
            </li>
        </ul>
    </div>

    <!-- Clientes -->
    <div class="menu-item">
        <input type="checkbox" id="clientes" class="menu-check"<?php echo $chk_clientes; ?>>
        <label for="clientes" class="menu-btn">
            <span><i class="fa-solid fa-address-book"></i> Clientes</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li>
                <a href="nuevo_cliente.php" class="<?php echo $act_cli_nuevo; ?>">
                    <i class="fa-solid fa-user-plus" style="width:14px;margin-right:4px;"></i>
                    Nuevo Cliente
                </a>
            </li>
            <li>
                <a href="mostrar_clientes.php" class="<?php echo $act_cli_dir; ?>">
                    <i class="fa-solid fa-address-card" style="width:14px;margin-right:4px;"></i>
                    Directorio de Clientes
                </a>
            </li>
            <li>
                <a href="mostrar_vehiculos.php" class="<?php echo $act_veh_dir; ?>">
                    <i class="fa-solid fa-car" style="width:14px;margin-right:4px;"></i>
                    Directorio de Vehículos
                </a>
            </li>
        </ul>
    </div>

    <!-- Órdenes de Trabajo -->
    <div class="menu-item">
        <input type="checkbox" id="ordenes" class="menu-check"<?php echo $chk_ordenes; ?>>
        <label for="ordenes" class="menu-btn">
            <span><i class="fa-solid fa-clipboard-list"></i> Ordenes de Trabajo</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li>
                <a href="nueva_orden.php" class="<?php echo $act_ord_nueva; ?>">
                    <i class="fa-solid fa-file-circle-plus" style="width:14px;margin-right:4px;"></i>
                    Nueva Orden
                </a>
            </li>
            <li>
                <a href="#" class="<?php echo $act_ord_seguimiento; ?>">
                    <i class="fa-solid fa-route" style="width:14px;margin-right:4px;"></i>
                    Seguimiento de Orden
                </a>
            </li>
            <li>
                <a href="#" class="<?php echo $act_ord_historial; ?>">
                    <i class="fa-solid fa-clock-rotate-left" style="width:14px;margin-right:4px;"></i>
                    Historial
                </a>
            </li>
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
            <li><a href="#"><i class="fa-solid fa-list-ul" style="width:14px;margin-right:4px;"></i>Registro de Actividad</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="m-fin" class="menu-check">
        <label for="m-fin" class="menu-btn">
            <span><i class="fa-solid fa-landmark"></i> Finanzas</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#"><i class="fa-solid fa-money-bill-transfer" style="width:14px;margin-right:4px;"></i>Flujo de Caja</a></li>
            <li><a href="#"><i class="fa-solid fa-file-invoice" style="width:14px;margin-right:4px;"></i>Facturación</a></li>
        </ul>
    </div>

    <div class="menu-item">
        <input type="checkbox" id="m-gas" class="menu-check">
        <label for="m-gas" class="menu-btn">
            <span><i class="fa-solid fa-file-invoice-dollar"></i> Gastos</span>
            <i class="fa-solid fa-chevron-right chevron"></i>
        </label>
        <ul class="submenu">
            <li><a href="#"><i class="fa-solid fa-truck" style="width:14px;margin-right:4px;"></i>Proveedores</a></li>
        </ul>
    </div>

    <div class="nav-section">SESIÓN</div>
    <div class="menu-item">
        <a href="php/logout.php" class="menu-btn">
            <span><i class="fa-solid fa-right-from-bracket" style="color:#ba1a1a;"></i> Cerrar Sesión</span>
        </a>
    </div>

</aside>

<div id="sidebar-overlay"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var menuToggle = document.getElementById('menu-toggle');
    var sidebar    = document.getElementById('sidebar');
    var overlay    = document.getElementById('sidebar-overlay');

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

