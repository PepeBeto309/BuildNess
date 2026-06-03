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
    <style>
        /* ── Buscador de cliente ───────────────────────────────────── */
        .cliente-search-wrapper {
            position: relative;
        }

        .cliente-search-wrapper input[type="text"] {
            width: 100%;
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
    </style>
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
                <img src="assets/img/logo-salvatori.png">
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
    $sidebar_activo           = 'clientes_nuevo';
    $sidebar_clientes_abierto = true;
    require __DIR__ . '/php/sidebar.php';
    ?>


    <main id="content">

        <div class="form-page">

            <div class="form-title" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:8px;"></i>
                    <span id="form-title-text">Nuevo Cliente</span>
                </div>
                <button type="button" id="btn-limpiar-seleccion" class="btn" style="display:none; width:auto; padding:5px 12px; font-size:12px; margin-left:10px;">
                    <i class="fa-solid fa-xmark"></i> Cancelar Edición
                </button>
            </div>

            <form name="formClientes" id="formClientes" method="post" action="php/clientes.php" novalidate>
                <input type="hidden" name="clave_cliente" id="clave-cliente-hidden" value="">

                <!-- Buscador de cliente existente -->
                <div class="input-group" style="margin-bottom:20px; border-bottom:1px dashed var(--border); padding-bottom:16px;">
                    <label for="cliente-search" style="font-weight:600; color:var(--primary);">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Cliente Existente (para editar datos o registrar vehículos)
                    </label>
                    <div class="cliente-search-wrapper" style="margin-top:6px;">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input
                            type="text"
                            id="cliente-search"
                            placeholder="Buscar por Nombre, Teléfono o Correo..."
                            autocomplete="off"
                        >
                        <div class="search-spinner" id="cliente-spinner"></div>
                        <div class="cliente-dropdown" id="cliente-dropdown"></div>
                    </div>
                </div>

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

                <div class="form-actions" style="margin-top:22px;">
                    <button type="submit" class="btn btn-primary" id="btn-guardar-cliente" name="Guardar" value="Guardar">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cliente y Vehículo
                    </button>
                    <a href="mostrar_clientes.php" class="btn">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                </div>

            </form>

        </div>

    </main>

    <script src="js/validacion_cliente.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('cliente-search');
        const dropdown    = document.getElementById('cliente-dropdown');
        const spinner     = document.getElementById('cliente-spinner');
        
        const hiddenClave = document.getElementById('clave-cliente-hidden');
        const formTitle   = document.getElementById('form-title-text');
        const btnGuardar  = document.getElementById('btn-guardar-cliente');
        const btnLimpiar  = document.getElementById('btn-limpiar-seleccion');
        
        const inputNombres = document.getElementById('nombres');
        const inputApPat   = document.getElementById('apPat');
        const inputApMat   = document.getElementById('apMat');
        const inputTel     = document.getElementById('tel');
        const inputEmail   = document.getElementById('email');

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
            hiddenClave.value = cli.Clave_Cliente;
            
            const nameParts = splitFullName(cli.Nombre);
            inputNombres.value = nameParts.nombres;
            inputApPat.value   = nameParts.apPat;
            inputApMat.value   = nameParts.apMat;
            inputTel.value     = cli.Telefono || '';
            inputEmail.value   = cli.Email || '';
            
            formTitle.textContent = 'Editar Cliente / Registrar Vehículo';
            btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Cambios';
            btnLimpiar.style.display = 'inline-flex';
            
            searchInput.value = '';
            dropdown.classList.remove('is-open');
        }

        btnLimpiar.addEventListener('click', () => {
            hiddenClave.value = '';
            formTitle.textContent = 'Nuevo Cliente';
            btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar Cliente y Vehículo';
            btnLimpiar.style.display = 'none';
            
            inputNombres.value = '';
            inputApPat.value   = '';
            inputApMat.value   = '';
            inputTel.value     = '';
            inputEmail.value   = '';
        });

        document.addEventListener('click', e => {
            if (!e.target.closest('.cliente-search-wrapper')) {
                dropdown.classList.remove('is-open');
            }
        });

        function splitFullName(fullName) {
            const parts = fullName.trim().split(/\s+/);
            let nombres = '';
            let apPat = '';
            let apMat = '';
            
            if (parts.length === 1) {
                nombres = parts[0];
            } else if (parts.length === 2) {
                nombres = parts[0];
                apPat = parts[1];
            } else if (parts.length === 3) {
                nombres = parts[0];
                apPat = parts[1];
                apMat = parts[2];
            } else if (parts.length >= 4) {
                apMat = parts[parts.length - 1];
                apPat = parts[parts.length - 2];
                nombres = parts.slice(0, parts.length - 2).join(' ');
            }
            return { nombres, apPat, apMat };
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    });
    </script>
</body>
</html>
