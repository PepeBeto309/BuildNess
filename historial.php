<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();
require_once __DIR__ . '/php/databaseS.php';

// Recuperar todas las OT, excluyendo las del sistema
$sql = "SELECT
            o.OT_Num, o.Fecha, o.Estado, o.Total_Cobrado, o.Trabajo_Realizado,
            c.Nombre AS Cliente,
            v.Marca, v.Modelo, v.Anio, v.Placa
        FROM OT o
        INNER JOIN Clientes c  ON o.Clave_Cliente  = c.Clave_Cliente
        INNER JOIN Vehiculos v ON o.Clave_Vehiculo = v.Clave_Vehiculo
        WHERE o.OT_Num NOT IN ('OT-SYSTEM','OT-BAJA')
        ORDER BY o.Fecha DESC, o.OT_Num DESC";

$result = mysqli_query($db, $sql);
$ordenes = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ordenes[] = $row;
    }
}

$success = isset($_GET['success']) && $_GET['success'] === 'ot_guardada';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salvatori - Historial de Órdenes</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="js/theme.js"></script>

    <style>
        /* ── Historial toolbar ─────────────────────────────────────────── */
        .historial-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .historial-toolbar h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .historial-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 360px;
        }

        .historial-search-wrapper .search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 13px;
            pointer-events: none;
        }

        #historial-buscar {
            width: 100%;
            font-family: inherit;
            padding: 9px 12px 9px 34px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            background: var(--bg-surface);
            color: var(--text-primary);
            transition: border-color var(--dur-fast) var(--ease-out),
                        box-shadow var(--dur-fast) var(--ease-out);
        }

        #historial-buscar:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,127,75,.15);
        }

        /* ── Badge de éxito al guardar ─────────────────────────────────── */
        .historial-success-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--bg-completado);
            border: 1px solid rgba(22,163,74,.3);
            border-radius: var(--radius-sm);
            color: var(--st-completado);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            animation: fadeIn .4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Tabla del historial ────────────────────────────────────────── */
        .historial-wrap {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .historial-count {
            padding: 12px 20px;
            font-size: 12px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            background: var(--bg-base);
        }

        #historial-table { width: 100%; border-collapse: collapse; }

        #historial-table th {
            background: var(--bg-base);
            padding: 13px 16px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        #historial-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: var(--text-secondary);
            vertical-align: middle;
            transition: background var(--dur-fast) var(--ease-out);
        }

        #historial-table tr:last-child td { border-bottom: none; }
        #historial-table tbody tr:hover td { background: var(--bg-hover); }

        .historial-empty {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            font-size: 14px;
            font-style: italic;
        }

        .historial-empty i {
            display: block;
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--border-strong);
        }

        .ot-num-link {
            font-weight: 700;
            color: var(--primary);
            font-size: 12.5px;
        }

        .vehiculo-info { line-height: 1.3; }
        .vehiculo-info .marca { font-weight: 600; color: var(--text-primary); }
        .vehiculo-info .placa {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .monto-cell {
            font-weight: 700;
            color: var(--text-primary);
        }

        .row-hidden { display: none; }
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
    $sidebar_activo          = 'ordenes_historial';
    $sidebar_ordenes_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main id="content">

        <?php if ($success): ?>
        <div class="historial-success-banner" id="success-banner">
            <i class="fa-solid fa-circle-check"></i>
            Orden de trabajo guardada exitosamente.
            <button type="button" onclick="document.getElementById('success-banner').remove();"
                style="background:none;border:none;cursor:pointer;color:inherit;margin-left:auto;font-size:14px;"
                aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <?php endif; ?>

        <div class="historial-toolbar">
            <h2>
                <i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);"></i>
                Historial de Órdenes de Trabajo
            </h2>

            <div class="historial-search-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="historial-buscar"
                       placeholder="Buscar por OT, cliente, placa…"
                       autocomplete="off"
                       aria-label="Buscar orden de trabajo">
            </div>

            <a href="nueva_orden.php" class="btn btn-primary">
                <i class="fa-solid fa-file-circle-plus"></i> Nueva Orden
            </a>
        </div>

        <div class="historial-wrap">
            <div class="historial-count" id="historial-count">
                <?php echo count($ordenes); ?> <?php echo count($ordenes) === 1 ? 'orden' : 'órdenes'; ?> en total
            </div>

            <table id="historial-table">
                <thead>
                    <tr>
                        <th>Nº Orden</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody id="historial-tbody">
                <?php if (empty($ordenes)): ?>
                    <tr id="empty-row">
                        <td colspan="7">
                            <div class="historial-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                No hay órdenes de trabajo registradas aún.<br>
                                <a href="nueva_orden.php" style="color:var(--primary);text-decoration:underline;font-style:normal;">
                                    Crear la primera orden
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ordenes as $ot): ?>
                    <?php
                        $estadoMap = [
                            'NUEVO'      => 'st-nuevo',
                            'PENDIENTE'  => 'st-pendiente',
                            'COTIZACION' => 'st-cotizado',
                            'TERMINADO'  => 'st-terminado',
                            'CANCELADO'  => 'st-cancelado',
                        ];
                        $badgeClass = $estadoMap[$ot['Estado']] ?? 'st-cotizado';
                        $vehiculo = trim(($ot['Marca'] ?? '') . ' ' . ($ot['Modelo'] ?? ''));
                        $anio     = $ot['Anio'] ? ' ' . $ot['Anio'] : '';
                        $total    = $ot['Total_Cobrado'] ? '$' . number_format((float)$ot['Total_Cobrado'], 2) : '—';
                        $desc     = $ot['Trabajo_Realizado']
                                  ? htmlspecialchars(mb_substr($ot['Trabajo_Realizado'], 0, 60))
                                    . (mb_strlen($ot['Trabajo_Realizado']) > 60 ? '…' : '')
                                  : '<span style="color:var(--text-muted);font-style:italic;">—</span>';
                    ?>
                    <tr class="historial-row"
                        data-search="<?php echo htmlspecialchars(
                            strtolower(
                                $ot['OT_Num'] . ' ' .
                                $ot['Cliente'] . ' ' .
                                $vehiculo . ' ' .
                                ($ot['Placa'] ?? '') . ' ' .
                                $ot['Estado']
                            )
                        ); ?>">
                        <td>
                            <span class="ot-num-link">
                                <?php echo htmlspecialchars($ot['OT_Num']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($ot['Fecha']); ?></td>
                        <td style="font-weight:500;color:var(--text-primary);">
                            <?php echo htmlspecialchars($ot['Cliente']); ?>
                        </td>
                        <td>
                            <div class="vehiculo-info">
                                <div class="marca"><?php echo htmlspecialchars($vehiculo . $anio); ?></div>
                                <?php if ($ot['Placa']): ?>
                                <div class="placa"><?php echo htmlspecialchars($ot['Placa']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><span class="status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($ot['Estado']); ?></span></td>
                        <td class="monto-cell"><?php echo $total; ?></td>
                        <td><?php echo $desc; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <script>
    // ── Búsqueda en tiempo real ─────────────────────────────────────────
    document.getElementById('historial-buscar').addEventListener('input', function () {
        const q     = this.value.toLowerCase().trim();
        const rows  = document.querySelectorAll('#historial-tbody .historial-row');
        let visible = 0;

        rows.forEach(row => {
            const match = q === '' || row.dataset.search.includes(q);
            row.classList.toggle('row-hidden', !match);
            if (match) visible++;
        });

        const countEl = document.getElementById('historial-count');
        const total   = rows.length;
        if (q === '') {
            countEl.textContent = total + ' ' + (total === 1 ? 'orden' : 'órdenes') + ' en total';
        } else {
            countEl.textContent = visible + ' de ' + total + ' ' + (total === 1 ? 'orden' : 'órdenes');
        }
    });
    </script>

</body>
</html>
