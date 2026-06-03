<?php
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();

require __DIR__ . '/php/funciones.php';

$mensaje = '';
$mensaje_tipo = '';
$modo_modal = 'agregar';
$item_editar = null;

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if ($id !== '' && borrar_inventario_refaccion($id)) {
        header('Location: mostrar_Inventario.php?msg=eliminado');
        exit;
    }
    $mensaje = 'No se pudo eliminar el artículo.';
    $mensaje_tipo = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_inventario'])) {
    $ok = agregar_inventario_refaccion(
        $_POST['codigo'] ?? '',
        $_POST['nombre'] ?? '',
        $_POST['cantidad'] ?? 0,
        $_POST['unidad'] ?? 'pz'
    );
    if ($ok) {
        header('Location: mostrar_Inventario.php?ok=1');
        exit;
    }
    $mensaje = 'No se pudo agregar. El nombre es obligatorio y la cantidad debe ser un número válido.';
    $mensaje_tipo = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_inventario'])) {
    $id = $_POST['id'] ?? '';
    $ok = actualizar_inventario_refaccion(
        $id,
        $_POST['codigo'] ?? '',
        $_POST['nombre'] ?? '',
        $_POST['cantidad'] ?? 0,
        $_POST['unidad'] ?? 'pz'
    );
    if ($ok) {
        header('Location: mostrar_Inventario.php?msg=editado');
        exit;
    }
    $mensaje = 'No se pudo actualizar. Verifica los datos.';
    $mensaje_tipo = 'error';
    $modo_modal = 'editar';
    if ($id !== '') {
        $item_editar = [
            'id' => $id,
            'codigo' => $_POST['codigo'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'cantidad' => $_POST['cantidad'] ?? 0,
            'unidad' => $_POST['unidad'] ?? 'pz',
        ];
    }
}

if (isset($_GET['ok'])) {
    $mensaje = 'Artículo agregado al inventario.';
    $mensaje_tipo = 'ok';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $mensaje = 'Artículo eliminado del inventario.';
    $mensaje_tipo = 'ok';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'editado') {
    $mensaje = 'Artículo actualizado correctamente.';
    $mensaje_tipo = 'ok';
}

$consulta = obtener_inventario_refacciones();

$modal_abierto = $mensaje_tipo === 'error' && ($modo_modal === 'editar' || isset($_POST['agregar_inventario']));

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Salvatori - Stock de Refacciones</title>

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
                <img src="assets/img/logo-salvatori.png" alt="Salvatori">
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
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['usuario_nombre'] ?? 'Administrador'); ?>&background=217346&color=fff" alt="">
            </div>

        </div>

    </header>

    <?php
    $sidebar_activo             = 'inventario_stock';
    $sidebar_inventario_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>

    <main class="inventario-main">

        <div class="inventario-toolbar">
            <h2><i class="fa-solid fa-boxes-stacked"></i> Stock de refacciones</h2>
            <button type="button" class="btn-inventario btn-inventario--primary" id="btn-agregar-inventario">
                <i class="fa-solid fa-plus"></i> Agregar
            </button>
            <button type="button" class="btn-inventario" id="btn-editar-inventario" disabled>Editar</button>
            <button type="button" class="btn-inventario" id="btn-eliminar-inventario" disabled>Eliminar</button>
            <a href="entradas_salidas.php" class="btn-inventario">
                <i class="fa-solid fa-right-left"></i> Entradas/Salidas
            </a>
        </div>

        <p class="inventario-ayuda">Selecciona una fila de la tabla para editarla o eliminarla.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="inventario-alerta inventario-alerta--<?php echo $mensaje_tipo === 'error' ? 'error' : 'ok'; ?>">
                <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <table class="tabla-salvatori" id="tabla-inventario">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($consulta && mysqli_num_rows($consulta) > 0): ?>
                    <?php while ($fila = mysqli_fetch_assoc($consulta)): ?>
                        <tr class="fila-inventario"
                            data-id="<?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-codigo="<?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-nombre="<?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-cantidad="<?php echo (int) $fila['cantidad']; ?>"
                            data-unidad="<?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td><?php echo htmlspecialchars($fila['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int) $fila['cantidad']; ?></td>
                            <td><?php echo htmlspecialchars($fila['unidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($fila['creado_en'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="inventario-tabla-vacio">
                            No hay artículos en inventario. Usa <strong>Agregar</strong> para registrar la primera refacción.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>

    <div class="modal-inventario<?php echo $modal_abierto ? ' is-open' : ''; ?>" id="modal-inventario" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-inventario">
        <div class="modal-inventario__panel">
            <div class="modal-inventario__head">
                <h3 id="titulo-modal-inventario">Agregar al inventario</h3>
                <button type="button" class="modal-inventario__cerrar" id="modal-inventario-cerrar" aria-label="Cerrar">&times;</button>
            </div>
            <form method="post" action="mostrar_Inventario.php" id="form-inventario">
                <input type="hidden" name="id" id="inv-id" value="<?php echo htmlspecialchars((string) ($item_editar['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="agregar_inventario" id="campo-agregar" value="1">
                <input type="hidden" name="editar_inventario" id="campo-editar" value="" disabled>
                <div class="campo">
                    <label for="inv-codigo">Código (opcional)</label>
                    <input type="text" id="inv-codigo" name="codigo" maxlength="64" placeholder="Ej. REF-001" autocomplete="off"
                        value="<?php echo htmlspecialchars($item_editar['codigo'] ?? $_POST['codigo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="campo">
                    <label for="inv-nombre">Nombre de la pieza <span class="campo-requerido">*</span></label>
                    <input type="text" id="inv-nombre" name="nombre" maxlength="200" required placeholder="Ej. Filtro de aceite"
                        value="<?php echo htmlspecialchars($item_editar['nombre'] ?? $_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="campo">
                    <label for="inv-cantidad">Cantidad</label>
                    <input type="number" id="inv-cantidad" name="cantidad" min="0" step="1" required
                        value="<?php echo htmlspecialchars((string) ($item_editar['cantidad'] ?? $_POST['cantidad'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="campo">
                    <label for="inv-unidad">Unidad</label>
                    <input type="text" id="inv-unidad" name="unidad" maxlength="32" placeholder="pz, kg, lt…"
                        value="<?php echo htmlspecialchars($item_editar['unidad'] ?? $_POST['unidad'] ?? 'pz', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="modal-inventario__acciones">
                    <button type="button" class="btn-inventario" id="modal-inventario-cancelar">Cancelar</button>
                    <button type="submit" class="btn-inventario btn-inventario--primary" id="btn-guardar-inventario">Guardar en inventario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('modal-inventario');
            var form = document.getElementById('form-inventario');
            var titulo = document.getElementById('titulo-modal-inventario');
            var btnGuardar = document.getElementById('btn-guardar-inventario');
            var campoAgregar = document.getElementById('campo-agregar');
            var campoEditar = document.getElementById('campo-editar');
            var inputId = document.getElementById('inv-id');
            var abrirAgregar = document.getElementById('btn-agregar-inventario');
            var btnEditar = document.getElementById('btn-editar-inventario');
            var btnEliminar = document.getElementById('btn-eliminar-inventario');
            var cerrarBtn = document.getElementById('modal-inventario-cerrar');
            var cancelar = document.getElementById('modal-inventario-cancelar');
            var filaSeleccionada = null;

            function abrirModal() {
                modal.classList.add('is-open');
                var input = document.getElementById('inv-nombre');
                if (input) { input.focus(); }
            }

            function cerrarModal() {
                modal.classList.remove('is-open');
            }

            function modoAgregar() {
                titulo.textContent = 'Agregar al inventario';
                btnGuardar.textContent = 'Guardar en inventario';
                campoAgregar.disabled = false;
                campoAgregar.value = '1';
                campoEditar.disabled = true;
                campoEditar.value = '';
                inputId.value = '';
                form.reset();
                document.getElementById('inv-unidad').value = 'pz';
                document.getElementById('inv-cantidad').value = '0';
            }

            function modoEditar(fila) {
                titulo.textContent = 'Editar refacción';
                btnGuardar.textContent = 'Guardar cambios';
                campoAgregar.disabled = true;
                campoAgregar.value = '';
                campoEditar.disabled = false;
                campoEditar.value = '1';
                inputId.value = fila.dataset.id;
                document.getElementById('inv-codigo').value = fila.dataset.codigo || '';
                document.getElementById('inv-nombre').value = fila.dataset.nombre || '';
                document.getElementById('inv-cantidad').value = fila.dataset.cantidad || '0';
                document.getElementById('inv-unidad').value = fila.dataset.unidad || 'pz';
            }

            function seleccionarFila(fila) {
                if (filaSeleccionada) {
                    filaSeleccionada.classList.remove('fila-inventario--seleccionada');
                }
                filaSeleccionada = fila;
                if (fila) {
                    fila.classList.add('fila-inventario--seleccionada');
                    btnEditar.disabled = false;
                    btnEliminar.disabled = false;
                } else {
                    btnEditar.disabled = true;
                    btnEliminar.disabled = true;
                }
            }

            document.querySelectorAll('.fila-inventario').forEach(function (fila) {
                fila.addEventListener('click', function () {
                    seleccionarFila(fila);
                });
            });

            if (abrirAgregar) {
                abrirAgregar.addEventListener('click', function () {
                    modoAgregar();
                    abrirModal();
                });
            }

            if (btnEditar) {
                btnEditar.addEventListener('click', function () {
                    if (!filaSeleccionada) {
                        alert('Selecciona un artículo de la tabla.');
                        return;
                    }
                    modoEditar(filaSeleccionada);
                    abrirModal();
                });
            }

            if (btnEliminar) {
                btnEliminar.addEventListener('click', function () {
                    if (!filaSeleccionada) {
                        alert('Selecciona un artículo de la tabla.');
                        return;
                    }
                    var nombre = filaSeleccionada.dataset.nombre || 'este artículo';
                    if (confirm('¿Eliminar "' + nombre + '" del inventario? Se registrará como baja.')) {
                        window.location.href = 'mostrar_Inventario.php?eliminar=' + encodeURIComponent(filaSeleccionada.dataset.id);
                    }
                });
            }

            if (cerrarBtn) cerrarBtn.addEventListener('click', cerrarModal);
            if (cancelar) cancelar.addEventListener('click', cerrarModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) cerrarModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') cerrarModal();
            });

            <?php if ($modo_modal === 'editar' && $item_editar): ?>
            titulo.textContent = 'Editar refacción';
            btnGuardar.textContent = 'Guardar cambios';
            campoAgregar.disabled = true;
            campoEditar.disabled = false;
            campoEditar.value = '1';
            <?php endif; ?>
        })();
    </script>

</body>

</html>
