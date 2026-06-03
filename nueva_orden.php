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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="js/theme.js"></script>

    <style>
        /* ── Buscador de cliente ───────────────────────────────────── */
        .cliente-search-wrapper {
            position: relative;
        }

        .cliente-search-wrapper .search-input-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .cliente-search-wrapper input[type="text"] {
            flex: 1;
            font-family: inherit;
            padding: 9px 12px 9px 36px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: border-color var(--dur-fast) var(--ease-out),
                        box-shadow var(--dur-fast) var(--ease-out);
        }

        .cliente-search-wrapper input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,127,75,.15);
        }

        .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 13px;
            pointer-events: none;
        }

        .cliente-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            z-index: 200;
            max-height: 240px;
            overflow-y: auto;
            display: none;
        }

        .cliente-dropdown.is-open { display: block; }

        .cliente-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background var(--dur-fast) var(--ease-out);
        }

        .cliente-dropdown-item:last-child { border-bottom: none; }
        .cliente-dropdown-item:hover      { background: var(--bg-hover); }

        .cliente-dropdown-item .cli-nombre {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .cliente-dropdown-item .cli-meta {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .cliente-dropdown-item.no-results {
            color: var(--text-muted);
            font-size: 13px;
            font-style: italic;
            cursor: default;
        }

        .cliente-seleccionado {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--primary-subtle);
            border: 1px solid var(--primary-light);
            border-radius: var(--radius-sm);
            margin-top: 8px;
        }

        .cliente-seleccionado.visible { display: flex; }

        .cliente-seleccionado .cli-tag-nombre {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            flex: 1;
        }

        .cliente-seleccionado .cli-tag-clear {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            padding: 2px 4px;
            border-radius: 4px;
            transition: color var(--dur-fast);
        }

        .cliente-seleccionado .cli-tag-clear:hover { color: var(--st-cancelado); }

        /* ── Lista de vehículos ───────────────────────────────────── */
        .vehiculo-radio-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 4px;
        }

        .vehiculo-radio-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--dur-fast) var(--ease-out);
            background: var(--bg-surface);
        }

        .vehiculo-radio-label:hover {
            border-color: var(--primary);
            background: var(--bg-hover);
        }

        .vehiculo-radio-label input[type="radio"] {
            margin-top: 3px;
            accent-color: var(--primary);
            flex-shrink: 0;
        }

        .vehiculo-radio-label.selected {
            border-color: var(--primary);
            background: var(--primary-subtle);
        }

        .vehiculo-info-nombre {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .vehiculo-info-meta {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .vehiculos-placeholder {
            font-size: 13px;
            color: var(--text-muted);
            font-style: italic;
            padding: 8px 0;
        }

        /* ── Botones del panel OT — variantes semánticas ─────────── */
        .btn-ot {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all var(--dur-fast) var(--ease-out);
            width: 100%;
            text-align: left;
        }

        .btn-ot:active { transform: translateY(1px); }

        /* Primary — Guardar / Agregar servicio */
        .btn-ot-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: var(--shadow-xs);
        }
        .btn-ot-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: var(--shadow-sm);
        }

        /* Secondary — Cotizar / Editar */
        .btn-ot-secondary {
            background: var(--bg-surface);
            color: var(--text-secondary);
            border-color: var(--border);
        }
        .btn-ot-secondary:hover {
            background: var(--bg-hover);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Info — Facturar */
        .btn-ot-info {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
        }
        .btn-ot-info:hover {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        /* Success — Terminar */
        .btn-ot-success {
            background: var(--bg-completado);
            color: var(--st-completado);
            border-color: rgba(22,163,74,.35);
        }
        .btn-ot-success:hover {
            background: var(--st-completado);
            color: #fff;
            border-color: var(--st-completado);
        }

        /* Warning — PDF */
        .btn-ot-warning {
            background: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }
        .btn-ot-warning:hover {
            background: #b45309;
            color: #fff;
            border-color: #b45309;
        }

        /* Separador visual entre grupos de botones */
        .btn-ot-separator {
            height: 1px;
            background: var(--border);
            margin: 4px 0;
        }

        /* ── Total box mejorado ───────────────────────────────────── */
        .total-box-v2 {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .total-card {
            flex: 1;
            min-width: 140px;
            background: var(--bg-base);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 16px;
        }

        .total-card label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .total-card .total-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -.5px;
        }

        /* Spinner de búsqueda */
        .search-spinner {
            display: none;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }
        .search-spinner.is-loading { display: block; }

        /* Estilos para tarjetas seleccionables de OT activas */
        .ot-activa-card {
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            background: var(--bg-surface);
            cursor: pointer;
            transition: all var(--dur-fast) var(--ease-out);
        }
        .ot-activa-card:hover {
            border-color: var(--primary);
            background: var(--bg-hover);
        }
        .ot-activa-card.selected {
            border-color: var(--primary);
            background: var(--primary-subtle);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        /* Botones de acción OT deshabilitados por defecto */
        .btn-ot.is-disabled {
            opacity: 0.5;
            pointer-events: none;
            cursor: not-allowed;
            filter: grayscale(0.6);
        }
    </style>
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff">
            </div>
        </div>
    </header>

    <!-- SIDEBAR -->
    <?php
    $sidebar_activo           = 'ordenes_nueva';
    $sidebar_ordenes_abierto  = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main id="content">
        <div class="form-page">

            <div class="form-title" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <i class="fa-solid fa-file-circle-plus" style="color:var(--primary);margin-right:8px;"></i>
                    <span id="ot-form-title">Nueva Orden de Trabajo</span>
                </div>
                <button type="button" id="btn-cancelar-edicion" class="btn btn-ot-secondary" style="display:none; width:auto; padding:5px 12px; font-size:12px; margin-left:10px;">
                    <i class="fa-solid fa-xmark"></i> Cancelar Edición
                </button>
            </div>

            <form id="ot-form" action="php/guardar_ot.php" method="POST">

                <!-- OT_Num generado en el cliente, el servidor lo puede sobreescribir -->
                <input type="hidden" name="action"    id="ot-action-hidden" value="nueva_ot">
                <input type="hidden" name="ot_num"    id="ot-num-hidden" value="">
                <input type="hidden" name="clave_cliente"  id="clave-cliente-hidden" value="">
                <input type="hidden" name="clave_vehiculo" id="clave-vehiculo-hidden" value="">

                <div class="ot-layout">

                    <!-- ══ Columna izquierda ══════════════════════════════════ -->
                    <div>

                        <!-- Sección: Datos del Cliente -->
                        <div class="form-section" style="margin-bottom:16px;">
                            <h3><i class="fa-solid fa-user" style="margin-right:6px;"></i>Datos del Cliente</h3>

                            <!-- Buscador de cliente -->
                            <div class="input-group">
                                <label for="cliente-search">Buscar Cliente</label>
                                <div class="cliente-search-wrapper">
                                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                    <div class="search-input-row">
                                        <input
                                            type="text"
                                            id="cliente-search"
                                            placeholder="Nombre, teléfono o correo…"
                                            autocomplete="off"
                                            aria-label="Buscar cliente"
                                        >
                                    </div>
                                    <div class="search-spinner" id="cliente-spinner"></div>
                                    <div class="cliente-dropdown" id="cliente-dropdown" role="listbox"></div>
                                </div>
                                <!-- Chip del cliente seleccionado -->
                                <div class="cliente-seleccionado" id="cliente-chip">
                                    <i class="fa-solid fa-circle-check" style="color:var(--primary);"></i>
                                    <span class="cli-tag-nombre" id="cliente-chip-nombre"></span>
                                    <button type="button" class="cli-tag-clear" id="cliente-chip-clear"
                                            title="Cambiar cliente" aria-label="Quitar cliente">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Lista de Vehículos del cliente -->
                            <div class="input-group" id="vehiculos-section">
                                <label>Vehículo</label>
                                <div class="vehiculo-radio-group" id="vehiculo-list">
                                    <span class="vehiculos-placeholder" id="vehiculos-placeholder">
                                        Selecciona un cliente para ver sus vehículos.
                                    </span>
                                </div>
                            </div>

                            <!-- Kilometraje -->
                            <div class="input-group">
                                <label for="km-entrada">Kilometraje de entrada</label>
                                <input
                                    type="number"
                                    id="km-entrada"
                                    name="km_entrada"
                                    min="0"
                                    step="1"
                                    placeholder="Ej. 85000"
                                >
                            </div>
                        </div>

                        <!-- Sección: Trabajo a realizar -->
                        <div class="form-section" style="margin-bottom:16px;">
                            <h3><i class="fa-solid fa-wrench" style="margin-right:6px;"></i>Trabajo a Realizar</h3>
                            <div class="input-group">
                                <label for="descripcion-trabajo">Descripción</label>
                                <textarea
                                    id="descripcion-trabajo"
                                    name="descripcion"
                                    rows="5"
                                    placeholder="Describe el servicio a realizar…"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Sección: Cotización -->
                        <div class="form-section">
                            <h3><i class="fa-solid fa-calculator" style="margin-right:6px;"></i>Cotización</h3>

                            <div class="input-group">
                                <label for="horas-mano-obra">Horas de mano de obra</label>
                                <input
                                    type="number"
                                    id="horas-mano-obra"
                                    name="horas_mano_obra"
                                    min="0"
                                    step="0.5"
                                    placeholder="Ej. 3"
                                >
                            </div>

                            <div class="input-group">
                                <label for="costo-hora">Costo por hora ($)</label>
                                <input
                                    type="number"
                                    id="costo-hora"
                                    name="costo_hora"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej. 350"
                                >
                            </div>

                            <div class="input-group">
                                <label for="total-refacciones">Total refacciones ($)</label>
                                <input
                                    type="number"
                                    id="total-refacciones"
                                    name="total_refacciones"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ej. 1200"
                                >
                            </div>

                            <div class="input-group">
                                <label for="mecanico">Mecánico asignado</label>
                                <input
                                    type="text"
                                    id="mecanico"
                                    name="mecanico"
                                    placeholder="Nombre del mecánico"
                                >
                            </div>
                        </div>

                    </div><!-- /columna izquierda -->

                    <!-- ══ Panel derecho (OT) ══════════════════════════════════ -->
                    <div class="ot-side">
                        <h3><i class="fa-solid fa-clipboard-list" style="margin-right:6px;"></i>Datos OT</h3>

                        <div class="input-group">
                            <label for="estado-ot">Estado inicial</label>
                            <select id="estado-ot" name="estado">
                                <option value="COTIZACION">COTIZACIÓN</option>
                                <option value="NUEVO" selected>NUEVO</option>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="TERMINADO">TERMINADO</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="monto-ot">Monto acordado ($)</label>
                            <input
                                type="number"
                                id="monto-ot"
                                name="monto"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >
                        </div>

                        <!-- Totales calculados -->
                        <div class="total-box-v2">
                            <div class="total-card">
                                <label>Total sugerido</label>
                                <div class="total-value" id="total-sugerido">$0.00</div>
                            </div>
                            <div class="total-card">
                                <label>Mano de obra</label>
                                <div class="total-value" id="total-mano-obra">$0.00</div>
                            </div>
                        </div>

                        <!-- Separador -->
                        <div class="btn-ot-separator" style="margin:16px 0;"></div>

                        <!-- ── Órdenes activas de este vehículo ──────── -->
                        <div id="ot-activas-section" style="display:none; margin-bottom:14px;">
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px;">
                                <i class="fa-solid fa-file-circle-check" style="margin-right:5px;color:var(--st-pendiente);"></i>
                                Órdenes activas de este vehículo
                            </div>
                            <div id="ot-activas-list"></div>
                        </div>
                        <!-- Separador 2 -->
                        <div id="ot-activas-sep" class="btn-ot-separator" style="margin:0 0 12px 0; display:none;"></div>

                        <!-- Acciones OT -->
                        <div class="ot-actions">
                            <button type="button" class="btn-ot btn-ot-primary" id="btn-agregar-servicio">
                                <i class="fa-solid fa-plus"></i> Agregar Servicio
                            </button>
                            <button type="button" class="btn-ot btn-ot-info is-disabled" id="btn-facturar">
                                <i class="fa-solid fa-file-invoice-dollar"></i> Facturar
                            </button>
                            <button type="button" class="btn-ot btn-ot-secondary is-disabled" id="btn-editar">
                                <i class="fa-solid fa-pen-to-square"></i> Editar OT
                            </button>
                            <button type="button" class="btn-ot btn-ot-success is-disabled" id="btn-terminar">
                                <i class="fa-solid fa-circle-check"></i> Marcar Terminada
                            </button>
                            <button type="button" class="btn-ot btn-ot-secondary" id="btn-cotizar">
                                <i class="fa-solid fa-file-lines"></i> Cotizar
                            </button>
                            <button type="button" class="btn-ot btn-ot-warning is-disabled" id="btn-pdf">
                                <i class="fa-solid fa-file-pdf"></i> Generar PDF
                            </button>
                        </div>
                    </div><!-- /ot-side -->

                </div><!-- /ot-layout -->

                <!-- Botón principal -->
                <div class="form-actions" style="margin-top:22px;">
                    <button type="submit" class="btn btn-primary" id="btn-guardar-ot">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Orden de Trabajo
                    </button>
                    <a href="dashboard.php" class="btn">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                </div>

            </form>
        </div><!-- /form-page -->
    </main>

    <!-- ================================================================
         JAVASCRIPT — Búsqueda cliente + carga vehículos + cálculos
         ================================================================ -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // ── Elementos DOM ──────────────────────────────────────────────
        const searchInput      = document.getElementById('cliente-search');
        const dropdown         = document.getElementById('cliente-dropdown');
        const spinner          = document.getElementById('cliente-spinner');
        const chip             = document.getElementById('cliente-chip');
        const chipNombre       = document.getElementById('cliente-chip-nombre');
        const chipClear        = document.getElementById('cliente-chip-clear');
        const claveClienteHidden = document.getElementById('clave-cliente-hidden');
        const claveVehiculoHidden= document.getElementById('clave-vehiculo-hidden');
        const vehiculoList     = document.getElementById('vehiculo-list');
        const vehiculosPlaceholder = document.getElementById('vehiculos-placeholder');

        const horasInput       = document.getElementById('horas-mano-obra');
        const costoHoraInput   = document.getElementById('costo-hora');
        const refaccionesInput = document.getElementById('total-refacciones');
        const totalSugeridoEl  = document.getElementById('total-sugerido');
        const totalManoObraEl  = document.getElementById('total-mano-obra');

        // ── Generar OT_Num automático ──────────────────────────────────
        const now  = new Date();
        const pad  = n => String(n).padStart(2, '0');
        const otNum = 'OT-' + now.getFullYear()
                    + pad(now.getMonth() + 1)
                    + pad(now.getDate())
                    + '-' + Math.floor(Math.random() * 9000 + 1000);
        document.getElementById('ot-num-hidden').value = otNum;

        // ── Formato moneda ─────────────────────────────────────────────
        const fmt = n => '$' + Number(n || 0).toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // ── Recalcular totales ──────────────────────────────────────────
        function recalcular() {
            const horas      = parseFloat(horasInput.value)       || 0;
            const costoHora  = parseFloat(costoHoraInput.value)   || 0;
            const refacc     = parseFloat(refaccionesInput.value) || 0;
            const manoObra   = horas * costoHora;
            totalManoObraEl.textContent  = fmt(manoObra);
            totalSugeridoEl.textContent  = fmt(manoObra + refacc);
        }

        [horasInput, costoHoraInput, refaccionesInput].forEach(el => {
            el?.addEventListener('input', recalcular);
        });

        // ── Impedir negativos al escribir directamente ──────────────────
        document.querySelectorAll('input[type="number"]').forEach(inp => {
            inp.addEventListener('input', () => {
                if (parseFloat(inp.value) < 0) inp.value = '';
            });
        });

        // ── Buscador de clientes con debounce ──────────────────────────
        let debounce = null;

        searchInput.addEventListener('input', () => {
            clearTimeout(debounce);
            const q = searchInput.value.trim();

            if (q.length < 2) {
                dropdown.innerHTML = '';
                dropdown.classList.remove('is-open');
                return;
            }

            spinner.classList.add('is-loading');

            debounce = setTimeout(() => {
                fetch('php/buscar_clientes.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        spinner.classList.remove('is-loading');
                        renderDropdown(data);
                    })
                    .catch(() => {
                        spinner.classList.remove('is-loading');
                        dropdown.innerHTML = '<div class="cliente-dropdown-item no-results">Error de red. Intenta de nuevo.</div>';
                        dropdown.classList.add('is-open');
                    });
            }, 300);
        });

        function renderDropdown(clientes) {
            dropdown.innerHTML = '';
            if (clientes.length === 0) {
                dropdown.innerHTML = '<div class="cliente-dropdown-item no-results">Sin resultados para esta búsqueda.</div>';
            } else {
                clientes.forEach(cli => {
                    const item = document.createElement('div');
                    item.className = 'cliente-dropdown-item';
                    item.setAttribute('role', 'option');
                    item.innerHTML = `
                        <div class="cli-nombre">${escapeHtml(cli.Nombre)}</div>
                        <div class="cli-meta">
                            ${cli.Telefono ? '<i class="fa-solid fa-phone" style="font-size:10px;"></i> ' + escapeHtml(cli.Telefono) : ''}
                            ${cli.Email    ? ' &nbsp; <i class="fa-solid fa-envelope" style="font-size:10px;"></i> ' + escapeHtml(cli.Email) : ''}
                        </div>`;
                    item.addEventListener('click', () => seleccionarCliente(cli));
                    dropdown.appendChild(item);
                });
            }
            dropdown.classList.add('is-open');
        }

        function seleccionarCliente(cli) {
            // Guardar clave
            claveClienteHidden.value = cli.Clave_Cliente;

            // Mostrar chip
            chipNombre.textContent = cli.Nombre
                + (cli.Telefono ? ' · ' + cli.Telefono : '');
            chip.classList.add('visible');

            // Ocultar buscador
            searchInput.value = '';
            searchInput.style.display = 'none';
            document.querySelector('.search-icon').style.display = 'none';
            dropdown.classList.remove('is-open');

            // Cargar vehículos
            cargarVehiculos(cli.Clave_Cliente);
        }

        chipClear.addEventListener('click', () => {
            claveClienteHidden.value     = '';
            claveVehiculoHidden.value    = '';
            chip.classList.remove('visible');
            searchInput.style.display    = '';
            document.querySelector('.search-icon').style.display = '';
            searchInput.value            = '';
            searchInput.focus();

            // Limpiar selección de OT y salir de edición
            selectedOtNum = null;
            if (typeof actualizarBotonesOT === 'function') actualizarBotonesOT();
            if (typeof cancelarEdicion === 'function') cancelarEdicion();

            // Limpiar vehículos
            vehiculoList.innerHTML = '';
            vehiculosPlaceholder.style.display = '';
            vehiculoList.appendChild(vehiculosPlaceholder);
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', e => {
            if (!e.target.closest('.cliente-search-wrapper')) {
                dropdown.classList.remove('is-open');
            }
        });

        // ── Carga dinámica de vehículos ────────────────────────────────
        function cargarVehiculos(claveCliente) {
            vehiculoList.innerHTML = '<span class="vehiculos-placeholder"><i class="fa-solid fa-spinner fa-spin"></i> Cargando vehículos…</span>';

            fetch('php/vehiculos_cliente.php?clave=' + encodeURIComponent(claveCliente))
                .then(r => r.json())
                .then(data => renderVehiculos(data))
                .catch(() => {
                    vehiculoList.innerHTML = '<span class="vehiculos-placeholder">Error al cargar vehículos.</span>';
                });
        }

        function renderVehiculos(vehiculos) {
            vehiculoList.innerHTML = '';

            if (vehiculos.length === 0) {
                vehiculoList.innerHTML = '<span class="vehiculos-placeholder">Este cliente no tiene vehículos registrados.</span>';
                return;
            }

            vehiculos.forEach((v, i) => {
                const label   = document.createElement('label');
                label.className = 'vehiculo-radio-label';

                const placa = v.Placa ? 'Placa: ' + v.Placa : (v.Vin ? 'VIN: ' + v.Vin : 'Sin placa/VIN');
                const anio  = v.Anio  ? v.Anio + ' · '     : '';

                label.innerHTML = `
                    <input type="radio" name="clave_vehiculo_radio"
                           value="${escapeHtml(v.Clave_Vehiculo)}"
                           id="veh-${i}" ${i === 0 ? 'checked' : ''}>
                    <div>
                        <div class="vehiculo-info-nombre">
                            ${escapeHtml(v.Marca)} ${escapeHtml(v.Modelo)}
                        </div>
                        <div class="vehiculo-info-meta">${anio}${placa}</div>
                    </div>`;

                // Al elegir, actualizar hidden + estilo
                label.querySelector('input').addEventListener('change', () => {
                    claveVehiculoHidden.value = v.Clave_Vehiculo;
                    document.querySelectorAll('.vehiculo-radio-label')
                            .forEach(l => l.classList.remove('selected'));
                    label.classList.add('selected');
                });

                if (i === 0) {
                    label.classList.add('selected');
                    claveVehiculoHidden.value = v.Clave_Vehiculo;
                }

                vehiculoList.appendChild(label);
            });

            // Auto-load OTs for first vehicle
            if (vehiculos.length > 0) {
                cargarOTActivas(vehiculos[0].Clave_Vehiculo);
            }
        }

        // ── Variables de Selección y Botones de Órdenes ────────────────
        let selectedOtNum = null;
        const btnFacturar = document.getElementById('btn-facturar');
        const btnEditar   = document.getElementById('btn-editar');
        const btnTerminar = document.getElementById('btn-terminar');
        const btnPdf      = document.getElementById('btn-pdf');
        const btnCancelarEdicion = document.getElementById('btn-cancelar-edicion');

        function actualizarBotonesOT() {
            const hasSelection = !!selectedOtNum;
            [btnFacturar, btnEditar, btnTerminar, btnPdf].forEach(btn => {
                if (btn) {
                    if (hasSelection) {
                        btn.classList.remove('is-disabled');
                    } else {
                        btn.classList.add('is-disabled');
                    }
                }
            });
        }

        // ── Cargar OTs activas por vehículo ───────────────────────────
        function cargarOTActivas(claveVehiculo) {
            const section  = document.getElementById('ot-activas-section');
            const sep      = document.getElementById('ot-activas-sep');
            const list     = document.getElementById('ot-activas-list');

            list.innerHTML = '<span class="vehiculos-placeholder"><i class="fa-solid fa-spinner fa-spin"></i> Buscando órdenes activas…</span>';
            section.style.display = 'block';
            sep.style.display = 'block';

            fetch('php/ot_activas_vehiculo.php?clave=' + encodeURIComponent(claveVehiculo))
                .then(r => r.json())
                .then(ots => {
                    if (ots.length === 0) {
                        section.style.display = 'none';
                        sep.style.display = 'none';
                        selectedOtNum = null;
                        actualizarBotonesOT();
                        return;
                    }
                    list.innerHTML = '';
                    ots.forEach(ot => {
                        const estadoClass = {
                            'NUEVO':      'st-nuevo',
                            'PENDIENTE':  'st-pendiente',
                            'COTIZACION': 'st-cotizado',
                            'EN PROGRESO':'st-pendiente'
                        }[ot.Estado] || 'st-cotizado';

                        const card = document.createElement('div');
                        card.className = 'ot-activa-card';
                        if (selectedOtNum === ot.OT_Num) {
                            card.classList.add('selected');
                        }
                        card.innerHTML = `
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-size:12px;font-weight:700;color:var(--text-primary);">${escapeHtml(ot.OT_Num)}</span>
                                <span class="status ${estadoClass}">${escapeHtml(ot.Estado)}</span>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                                ${escapeHtml(ot.Fecha)}
                                ${ot.Trabajo_Realizado ? ' · ' + escapeHtml(ot.Trabajo_Realizado.substring(0,50)) + (ot.Trabajo_Realizado.length > 50 ? '…' : '') : ''}
                            </div>`;
                        
                        // Agregar listener de click para selección
                        card.addEventListener('click', () => {
                            if (card.classList.contains('selected')) {
                                card.classList.remove('selected');
                                selectedOtNum = null;
                            } else {
                                document.querySelectorAll('.ot-activa-card').forEach(c => c.classList.remove('selected'));
                                card.classList.add('selected');
                                selectedOtNum = ot.OT_Num;
                            }
                            actualizarBotonesOT();
                        });

                        list.appendChild(card);
                    });
                    
                    // Si ya no existe la orden que estaba seleccionada, limpiar selección
                    if (selectedOtNum && !ots.some(o => o.OT_Num === selectedOtNum)) {
                        selectedOtNum = null;
                    }
                    actualizarBotonesOT();
                })
                .catch(() => {
                    section.style.display = 'none';
                    sep.style.display = 'none';
                    selectedOtNum = null;
                    actualizarBotonesOT();
                });
        }

        // Actualizar OTs cuando cambia el vehículo seleccionado
        document.addEventListener('change', e => {
            if (e.target && e.target.name === 'clave_vehiculo_radio') {
                const clave = e.target.value;
                selectedOtNum = null;
                actualizarBotonesOT();
                if (clave) cargarOTActivas(clave);
            }
        });

        // ── Acciones de los Botones ────────────────────────────────────
        
        // Agregar Servicio (Nueva orden / Limpiar modo edición)
        document.getElementById('btn-agregar-servicio').addEventListener('click', () => {
            cancelarEdicion();
            document.getElementById('km-entrada').focus();
        });

        // Cotizar (cambiar estado a COTIZACION)
        document.getElementById('btn-cotizar').addEventListener('click', () => {
            document.getElementById('estado-ot').value = 'COTIZACION';
            document.getElementById('monto-ot').focus();
        });

        // Editar OT
        btnEditar.addEventListener('click', () => {
            if (!selectedOtNum) return;
            
            fetch('php/get_ot_detalles.php?ot_num=' + encodeURIComponent(selectedOtNum))
                .then(r => r.json())
                .then(ot => {
                    if (ot.error) {
                        alert('Error: ' + ot.error);
                        return;
                    }
                    
                    // Rellenar campos del formulario
                    document.getElementById('km-entrada').value = ot.Odometer_In || '';
                    document.getElementById('descripcion-trabajo').value = ot.Trabajo_Realizado || '';
                    document.getElementById('horas-mano-obra').value = ot.Horas_Facturadas || '';
                    document.getElementById('costo-hora').value = ot.Costo_MO_Hr || '';
                    document.getElementById('total-refacciones').value = ot.Refacciones || '';
                    document.getElementById('mecanico').value = ot.MecanicoNombre || '';
                    document.getElementById('estado-ot').value = ot.Estado || 'NUEVO';
                    document.getElementById('monto-ot').value = ot.Total_Cobrado || '';
                    
                    // Recalcular montos sugeridos y mano de obra
                    recalcular();
                    
                    // Cambiar acción a actualizar
                    document.getElementById('ot-action-hidden').value = 'editar_ot';
                    document.getElementById('ot-num-hidden').value = ot.OT_Num;
                    
                    // Actualizar UI del formulario
                    document.getElementById('ot-form-title').textContent = 'Editar Orden de Trabajo: ' + ot.OT_Num;
                    document.getElementById('btn-guardar-ot').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Actualizar Orden de Trabajo';
                    btnCancelarEdicion.style.display = 'block';
                    
                    // Scroll suave
                    document.querySelector('.form-page').scrollIntoView({ behavior: 'smooth' });
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de red al intentar obtener los detalles de la orden.');
                });
        });

        // Cancelar Edición
        function cancelarEdicion() {
            // Generar nuevo ot_num aleatorio original
            const now  = new Date();
            const pad  = n => String(n).padStart(2, '0');
            const otNum = 'OT-' + now.getFullYear()
                        + pad(now.getMonth() + 1)
                        + pad(now.getDate())
                        + '-' + Math.floor(Math.random() * 9000 + 1000);
            
            document.getElementById('ot-action-hidden').value = 'nueva_ot';
            document.getElementById('ot-num-hidden').value = otNum;
            
            // Limpiar campos
            document.getElementById('km-entrada').value = '';
            document.getElementById('descripcion-trabajo').value = '';
            document.getElementById('horas-mano-obra').value = '';
            document.getElementById('costo-hora').value = '';
            document.getElementById('total-refacciones').value = '';
            document.getElementById('mecanico').value = '';
            document.getElementById('estado-ot').value = 'NUEVO';
            document.getElementById('monto-ot').value = '';
            
            recalcular();
            
            // Revertir UI
            document.getElementById('ot-form-title').textContent = 'Nueva Orden de Trabajo';
            document.getElementById('btn-guardar-ot').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Orden de Trabajo';
            btnCancelarEdicion.style.display = 'none';
        }
        
        btnCancelarEdicion.addEventListener('click', cancelarEdicion);

        // Marcar Terminada
        btnTerminar.addEventListener('click', () => {
            if (!selectedOtNum) return;
            
            if (!confirm(`¿Estás seguro de que deseas marcar la orden ${selectedOtNum} como terminada?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('ot_num', selectedOtNum);
            formData.append('estado', 'TERMINADO');
            
            fetch('php/actualizar_estado_ot.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(`La orden ${selectedOtNum} ha sido marcada como terminada.`);
                    
                    // Si estábamos editando esta orden, cancelar edición
                    if (document.getElementById('ot-num-hidden').value === selectedOtNum) {
                        cancelarEdicion();
                    }
                    
                    // Limpiar selección
                    selectedOtNum = null;
                    actualizarBotonesOT();
                    
                    // Recargar lista de OTs activas del vehículo
                    const checkedRadio = document.querySelector('input[name="clave_vehiculo_radio"]:checked');
                    if (checkedRadio) {
                        cargarOTActivas(checkedRadio.value);
                    }
                } else {
                    alert('Error: ' + (res.error || 'No se pudo actualizar el estado de la orden.'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de red al actualizar el estado de la orden.');
            });
        });

        // Imprimir Recibo / Generar PDF
        btnPdf.addEventListener('click', () => {
            if (!selectedOtNum) return;
            window.open('imprimir_recibo.php?tipo=recibo&ot_num=' + encodeURIComponent(selectedOtNum), '_blank');
        });

        // Imprimir Factura (con IVA +16%)
        btnFacturar.addEventListener('click', () => {
            if (!selectedOtNum) return;
            window.open('imprimir_recibo.php?tipo=factura&ot_num=' + encodeURIComponent(selectedOtNum), '_blank');
        });

        // ── Validación antes de enviar ─────────────────────────────────
        document.getElementById('ot-form').addEventListener('submit', e => {
            if (!claveClienteHidden.value) {
                e.preventDefault();
                searchInput.focus();
                searchInput.style.border = '2px solid var(--st-cancelado)';
                alert('Por favor selecciona un cliente antes de guardar la orden.');
                return;
            }
            if (!claveVehiculoHidden.value) {
                e.preventDefault();
                alert('Por favor selecciona un vehículo antes de guardar la orden.');
                return;
            }
        });

        // ── Escape HTML ────────────────────────────────────────────────
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

    }); // DOMContentLoaded
    </script>

</body>
</html>
