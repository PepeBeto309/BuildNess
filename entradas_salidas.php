<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();

require __DIR__ . '/php/funciones.php';

$entradas = obtener_movimientos_inventario('entrada');
$salidas = obtener_movimientos_inventario('salida');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salvatori - Entradas y Salidas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <img src="assets/img/logo-salvatori.png" alt="Salvatori">
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
                <i class="fa-solid fa-triangle-exclamation"><span class="notification-dot"></span></i>
                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>
            </div>
            <div class="user-profile-circle">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff" alt="">
            </div>
        </div>
    </header>

    <?php
    $sidebar_activo             = 'inventario_entradas';
    $sidebar_inventario_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main class="inventario-main">
        <div class="inventario-toolbar">
            <h2><i class="fa-solid fa-right-left"></i> Entradas y salidas</h2>
            <div style="display:flex; gap:8px;">
                <button type="button" class="btn-inventario btn-inventario--primary" id="btn-abrir-baja-parcial">
                    <i class="fa-solid fa-minus"></i> Baja Parcial / Uso
                </button>
                <a href="mostrar_Inventario.php" class="btn-inventario">
                    <i class="fa-solid fa-boxes-stacked"></i> Volver al stock
                </a>
            </div>
        </div>

        <?php
        $mensaje = '';
        $mensaje_tipo = '';
        if (isset($_GET['msg']) && $_GET['msg'] === 'baja_ok') {
            $mensaje = 'Baja parcial / uso de refacción registrado correctamente.';
            $mensaje_tipo = 'ok';
        } elseif (isset($_GET['error'])) {
            $mensaje_tipo = 'error';
            if ($_GET['error'] === 'cantidad_insuficiente') {
                $disp = isset($_GET['disponible']) ? (int)$_GET['disponible'] : 0;
                $mensaje = "La cantidad solicitada supera el stock disponible ($disp pz).";
            } elseif ($_GET['error'] === 'ot_requerida') {
                $mensaje = 'Debes ingresar el número de la Orden de Trabajo.';
            } else {
                $mensaje = 'Error al registrar la baja parcial. Intente de nuevo.';
            }
        }
        ?>
        <?php if ($mensaje !== ''): ?>
            <div class="inventario-alerta inventario-alerta--<?php echo $mensaje_tipo === 'error' ? 'error' : 'ok'; ?>" style="margin-bottom:20px;">
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <section class="movimientos-seccion">
            <h3 class="movimientos-titulo movimientos-titulo--entrada">
                <i class="fa-solid fa-arrow-down"></i> Altas (entradas)
            </h3>
            <table class="tabla-salvatori">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($entradas && mysqli_num_rows($entradas) > 0): ?>
                        <?php while ($fila = mysqli_fetch_assoc($entradas)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['registrado_en'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $fila['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nota'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="inventario-tabla-vacio">No hay altas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="movimientos-seccion">
            <h3 class="movimientos-titulo movimientos-titulo--salida">
                <i class="fa-solid fa-arrow-up"></i> Bajas (salidas)
            </h3>
            <table class="tabla-salvatori">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($salidas && mysqli_num_rows($salidas) > 0): ?>
                        <?php while ($fila = mysqli_fetch_assoc($salidas)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['registrado_en'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $fila['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nota'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="inventario-tabla-vacio">No hay bajas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- MODAL BAJA PARCIAL -->
    <div class="modal-inventario" id="modal-baja-parcial" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-baja">
        <div class="modal-inventario__panel">
            <div class="modal-inventario__head">
                <h3 id="titulo-modal-baja">Registrar Uso / Baja Parcial</h3>
                <button type="button" class="modal-inventario__cerrar" id="modal-baja-cerrar" aria-label="Cerrar">&times;</button>
            </div>
            <form method="post" action="php/registrar_baja_parcial.php" id="form-baja-parcial">
                <div class="campo">
                    <label for="baja-refaccion">Seleccionar refacción <span class="campo-requerido">*</span></label>
                    <select id="baja-refaccion" name="codigo_refaccion" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--bg-surface); color:var(--text-primary); font-family:inherit;">
                        <option value="">-- Selecciona una pieza --</option>
                        <?php
                        $disponibles = obtener_refacciones_disponibles();
                        if ($disponibles && mysqli_num_rows($disponibles) > 0) {
                            while ($ref = mysqli_fetch_assoc($disponibles)) {
                                $disp_text = htmlspecialchars("[{$ref['codigo']}] {$ref['nombre']} ({$ref['cantidad']} {$ref['unidad']})");
                                echo "<option value=\"" . htmlspecialchars($ref['codigo']) . "\" data-max=\"" . $ref['cantidad'] . "\">" . $disp_text . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="baja-cantidad">Cantidad a retirar <span class="campo-requerido">*</span></label>
                    <input type="number" id="baja-cantidad" name="cantidad" min="1" required placeholder="Cantidad a retirar">
                </div>
                <div class="campo">
                    <label>Motivo de la baja</label>
                    <div style="display:flex; gap:16px; margin-top:6px;">
                        <label style="display:flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer;">
                            <input type="radio" name="tipo_baja" value="ot" checked style="accent-color:var(--primary);"> Uso en Orden de Trabajo
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-weight:normal; cursor:pointer;">
                            <input type="radio" name="tipo_baja" value="general" style="accent-color:var(--primary);"> Baja general de stock
                        </label>
                    </div>
                </div>
                <div class="campo" id="grupo-baja-ot">
                    <label for="baja-ot-num">Número de la Orden de Trabajo <span class="campo-requerido">*</span></label>
                    <input type="text" id="baja-ot-num" name="ot_num" placeholder="Ej. OT-20260603-1234">
                </div>
                <div class="modal-inventario__acciones">
                    <button type="button" class="btn-inventario" id="modal-baja-cancelar">Cancelar</button>
                    <button type="submit" class="btn-inventario btn-inventario--primary">Registrar Salida</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalBaja = document.getElementById('modal-baja-parcial');
        var btnAbrir  = document.getElementById('btn-abrir-baja-parcial');
        var btnCerrar = document.getElementById('modal-baja-cerrar');
        var btnCancel = document.getElementById('modal-baja-cancelar');
        var formBaja  = document.getElementById('form-baja-parcial');
        var selectRef = document.getElementById('baja-refaccion');
        var inputCant = document.getElementById('baja-cantidad');
        var radios    = document.getElementsByName('tipo_baja');
        var grupoOt   = document.getElementById('grupo-baja-ot');
        var inputOt   = document.getElementById('baja-ot-num');

        // Abre el modal
        if (btnAbrir) {
            btnAbrir.addEventListener('click', function() {
                modalBaja.classList.add('is-open');
                formBaja.reset();
                grupoOt.style.display = 'block';
                inputOt.required = true;
            });
        }

        // Cierra el modal
        function cerrarModalBaja() {
            modalBaja.classList.remove('is-open');
        }

        if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalBaja);
        if (btnCancel) btnCancel.addEventListener('click', cerrarModalBaja);

        // Cierra si hace clic fuera
        modalBaja.addEventListener('click', function(e) {
            if (e.target === modalBaja) cerrarModalBaja();
        });

        // Control de visibilidad del campo OT
        radios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'ot') {
                    grupoOt.style.display = 'block';
                    inputOt.required = true;
                } else {
                    grupoOt.style.display = 'none';
                    inputOt.required = false;
                    inputOt.value = '';
                }
            });
        });

        // Validar cantidad disponible
        selectRef.addEventListener('change', function() {
            var option = this.options[this.selectedIndex];
            if (option && option.dataset.max) {
                var maxVal = parseInt(option.dataset.max);
                inputCant.max = maxVal;
                inputCant.placeholder = "Disponible: " + maxVal;
            } else {
                inputCant.removeAttribute('max');
                inputCant.placeholder = "Cantidad a retirar";
            }
        });

        formBaja.addEventListener('submit', function(e) {
            var option = selectRef.options[selectRef.selectedIndex];
            if (option && option.dataset.max) {
                var maxVal = parseInt(option.dataset.max);
                var reqVal = parseInt(inputCant.value);
                if (reqVal > maxVal) {
                    e.preventDefault();
                    alert("Error: La cantidad solicitada (" + reqVal + ") supera la disponible en stock (" + maxVal + ").");
                }
            }
        });
    });
    </script>

</body>

</html>
