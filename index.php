<?php
require_once __DIR__ . '/php/auth.php';

// Si ya está autenticado, redirigir al dashboard
if (esta_autenticado()) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';
$submitted_email = '';
$active_tab = 'login';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once __DIR__ . '/php/databaseS.php';

    if ($_POST['action'] === 'login') {
        $active_tab = 'login';
        $submitted_email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($submitted_email === '' || $password === '') {
            $error_message = 'Por favor, rellene todos los campos.';
        } else {
            // Buscar el usuario en la base de datos (usando SELECT * para evitar error de columna desconocida)
            $stmt = mysqli_prepare($db, "SELECT * FROM Usuarios WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $submitted_email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                if ($user && (!isset($user['activo']) || $user['activo'])) {
                    // Validar contraseña (soporta texto plano y hash de contraseña para compatibilidad)
                    if ($password === $user['password'] || password_verify($password, $user['password'])) {
                        // Guardar datos en la sesión (detectar id_usuario o id de forma dinámica)
                        $id_col = isset($user['id_usuario']) ? 'id_usuario' : (isset($user['id']) ? 'id' : null);
                        if ($id_col) {
                            $_SESSION['usuario_id'] = $user[$id_col];
                        } else {
                            $_SESSION['usuario_id'] = 1; // fallback
                        }
                        $_SESSION['usuario_nombre'] = $user['nombre'] ?? 'Administrador';
                        $_SESSION['usuario_email'] = $user['email'];
                        $_SESSION['usuario_rol'] = $user['rol'] ?? 'usuario';
                        
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        $error_message = 'El correo o la contraseña son incorrectos.';
                    }
                } else {
                    $error_message = 'El correo o la contraseña son incorrectos.';
                }
            } else {
                $error_message = 'Error en el sistema. Inténtalo más tarde.';
            }
        }
    } elseif ($_POST['action'] === 'register') {
        $active_tab = 'register';
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nombre === '' || $email === '' || $password === '') {
            $error_message = 'Por favor, llene todos los campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'El correo electrónico no es válido.';
        } else {
            // Verificar si el correo ya existe
            $stmt = mysqli_prepare($db, "SELECT * FROM Usuarios WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $exists = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                if ($exists) {
                    $error_message = 'El correo electrónico ya está registrado.';
                } else {
                    // Crear hash de contraseña seguro
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar en Usuarios.
                    $stmt_ins = mysqli_prepare($db, "INSERT INTO Usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, 'usuario', 1)");
                    if ($stmt_ins) {
                        mysqli_stmt_bind_param($stmt_ins, "sss", $nombre, $email, $hashed_password);
                        $ok = mysqli_stmt_execute($stmt_ins);
                        $new_id = mysqli_insert_id($db);
                        mysqli_stmt_close($stmt_ins);

                        if ($ok) {
                            // Auto-login tras el registro exitoso
                            $_SESSION['usuario_id'] = $new_id;
                            $_SESSION['usuario_nombre'] = $nombre;
                            $_SESSION['usuario_email'] = $email;
                            $_SESSION['usuario_rol'] = 'usuario';

                            header('Location: dashboard.php');
                            exit;
                        } else {
                            $error_message = 'No se pudo crear el usuario. Inténtalo de nuevo.';
                        }
                    } else {
                        $error_message = 'Error al registrar el usuario en la base de datos.';
                    }
                }
            } else {
                $error_message = 'Error en el sistema. Inténtalo más tarde.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>BuildNess - ERP+CRM</title>
    <script src="js/theme.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#68dba9",
                        "error": "#ba1a1a",
                        "on-tertiary-container": "#64d7a5",
                        "inverse-primary": "#b3c5ff",
                        "on-secondary-fixed": "#0d1c2e",
                        "secondary-fixed": "#d5e3fc",
                        "inverse-surface": "#2c3134",
                        "outline-variant": "#c3c6d6",
                        "secondary-fixed-dim": "#b9c7df",
                        "surface-container-high": "#e4e9ed",
                        "on-primary": "#ffffff",
                        "on-surface": "#171c1f",
                        "primary-fixed": "#dbe1ff",
                        "tertiary-container": "#005c3f",
                        "tertiary": "#00422c",
                        "surface-container-low": "#f0f4f8",
                        "surface-tint": "#2156ca",
                        "on-primary-fixed-variant": "#003ea6",
                        "surface": "#f6fafe",
                        "secondary-container": "#d5e3fc",
                        "on-surface-variant": "#434653",
                        "primary-fixed-dim": "#b3c5ff",
                        "surface-container-highest": "#dfe3e7",
                        "surface-dim": "#d6dade",
                        "on-error": "#ffffff",
                        "surface-bright": "#f6fafe",
                        "on-secondary-fixed-variant": "#3a485b",
                        "error-container": "#ffdad6",
                        "on-secondary": "#ffffff",
                        "surface-container": "#eaeef2",
                        "on-tertiary": "#ffffff",
                        "background": "#f6fafe",
                        "surface-variant": "#dfe3e7",
                        "outline": "#737685",
                        "primary": "#00328a",
                        "on-error-container": "#93000a",
                        "surface-container-lowest": "#ffffff",
                        "inverse-on-surface": "#edf1f5",
                        "on-tertiary-fixed-variant": "#005137",
                        "on-secondary-container": "#57657a",
                        "on-tertiary-fixed": "#002114",
                        "on-background": "#171c1f",
                        "secondary": "#515f74",
                        "tertiary-fixed": "#85f8c4",
                        "primary-container": "#0047bb",
                        "on-primary-container": "#afc1ff",
                        "on-primary-fixed": "#00174a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Manrope", "sans-serif"],
                        "body": ["Inter", "sans-serif"],
                        "label": ["Inter", "sans-serif"]
                    }
                }
            }
        }
    </script>
    <style>
        .glass-overlay {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #00328a, #0047bb);
            border-radius: 0.375rem;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-tertiary {
            background-color: #00422c;
            border-radius: 0.375rem;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .ambient-shadow {
            box-shadow: 0 8px 32px rgba(23, 28, 31, 0.06);
        }
        
        /* Slider styling */
        .pricing-slider-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            padding: 24px 0;
        }
        .pricing-slider-track {
            display: flex;
            transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
            will-change: transform;
        }
        .pricing-slide {
            flex: 0 0 100%;
            width: 100%;
            padding: 0 12px;
            box-sizing: border-box;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        /* Custom Dot Animations */
        .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background-color: #cbd5e1;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .dark .slider-dot {
            background-color: #475569;
        }
        .slider-dot.active {
            width: 24px;
            background-color: #00328a;
        }
        .dark .slider-dot.active {
            background-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body antialiased">
    <!-- TopNavBar -->
    <nav class="fixed top-0 w-full z-50 bg-slate-50/80 dark:bg-slate-950/80 backdrop-blur-xl shadow-sm dark:shadow-none bg-slate-100/50 dark:bg-slate-900/50">
        <div class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto">
            <div class="text-2xl font-black font-['Manrope'] text-blue-900 dark:text-blue-50 tracking-tighter">
                BuildNess
            </div>
            <div class="hidden md:flex gap-8">
                <a class="font-['Manrope'] font-bold tracking-tight text-sm text-slate-600 dark:text-slate-400 hover:text-blue-800 transition-colors hover:bg-slate-100/50 dark:hover:bg-slate-800/50 rounded-lg py-1 px-3" href="#funcionalidades">Funcionalidades</a>
                <a class="font-['Manrope'] font-bold tracking-tight text-sm text-slate-600 dark:text-slate-400 hover:text-blue-800 transition-colors hover:bg-slate-100/50 dark:hover:bg-slate-800/50 rounded-lg py-1 px-3" href="#precios">Precios</a>
                <a class="font-['Manrope'] font-bold tracking-tight text-sm text-slate-600 dark:text-slate-400 hover:text-blue-800 transition-colors hover:bg-slate-100/50 dark:hover:bg-slate-800/50 rounded-lg py-1 px-3" href="#nosotros">Nosotros</a>
            </div>
            <div class="flex items-center gap-4">
                <button id="theme-toggle" class="p-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 rounded-lg transition-colors flex items-center justify-center" aria-label="Cambiar tema" style="width: 36px; height: 36px;">
                </button>
                <button id="open-login-btn-nav" class="btn-primary px-6 py-2 font-label font-semibold text-sm hidden md:block">
                    <div>Iniciar Sesión</div>
                </button>
                <button id="open-login-btn-mobile" class="md:hidden text-primary">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-6 max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-16">
        <div class="flex-1 space-y-8">
            <h1 class="font-headline text-5xl md:text-6xl font-bold tracking-tighter text-primary leading-tight">
                Digitaliza tu negocio de servicios en minutos. <span class="text-on-surface">Adiós al papel y al caos operativo.</span>
            </h1>
            <p class="font-body text-lg text-secondary max-w-xl">
                Deja atrás el caos de los cuadernos y las hojas de cálculo. BuildNess organiza tus citas, finanzas and clientes en una plataforma diseñada para talleres, clínicas y estudios en México.
            </p>
            <div class="flex gap-4">
                <button id="open-login-btn-hero" class="btn-primary px-8 py-3 font-label font-semibold">
                    Comenzar ahora
                </button>
                <button id="open-login-btn-demo" class="px-8 py-3 font-label font-semibold text-on-surface-variant hover:bg-surface-container-high rounded-md transition-colors">
                    Ver demo
                </button>
            </div>
        </div>
        <div class="flex-1 w-full relative">
            <div class="absolute inset-0 bg-secondary-fixed opacity-50 rounded-xl blur-3xl transform -rotate-6"></div>
            <img alt="Dashboard illustration" class="relative z-10 w-full h-auto rounded-xl ambient-shadow border border-outline-variant/20" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXM_RQ_BHLjxqgQil-rAb4zDxJ7ugLf3mglELyZjAeNq5jXgXg1A7WrFkorM261U-brG0YXJYdrt028qWezxRQitLWa7RYs2jGauGBj2xyRMszQm9QzQb9uXSDlF2uAdAkEaaRTNfAyOSK2qefGg062p0fJBDeipQr3-k54MjcZf5gKhKzOv4Y0YuZLHuevf6agTI3hfPK0jiXLa7Fj9_knQb_PeHYtFpX28RDY7FjeU0uS1c0aDxEOrHnIUjGAJYFcHOaSjXwMnA">
        </div>
    </section>

    <!-- Problems Section -->
    <section class="py-24 bg-surface-container-low">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="font-headline text-3xl md:text-4xl font-bold text-primary mb-4">¿Te suena familiar?</h2>
                <p class="font-body text-secondary max-w-2xl mx-auto">Los problemas de siempre tienen solución.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-surface-container-lowest p-8 rounded-xl ambient-shadow">
                    <span class="material-symbols-outlined text-4xl text-error mb-4">description</span>
                    <h3 class="font-headline text-xl font-bold mb-3">Caos en papel</h3>
                    <p class="font-body text-secondary text-sm">¿Cansado de perder tiempo en papel? Registros perdidos y horas buscando información que debería estar a un clic.</p>
                </div>
                <div class="bg-surface-container-lowest p-8 rounded-xl ambient-shadow">
                    <span class="material-symbols-outlined text-4xl text-error mb-4">payments</span>
                    <h3 class="font-headline text-xl font-bold mb-3">Ceguera operativa</h3>
                    <p class="font-body text-secondary text-sm">¿No sabes cuánto ganas realmente? Flujo de caja confuso y dificultad para separar gastos personales de los del negocio.</p>
                </div>
                <div class="bg-surface-container-lowest p-8 rounded-xl ambient-shadow">
                    <span class="material-symbols-outlined text-4xl text-error mb-4">event_busy</span>
                    <h3 class="font-headline text-xl font-bold mb-3">Falta de control de citas</h3>
                    <p class="font-body text-secondary text-sm">Pérdida de ingresos por inasistencias y mala comunicación de recordatorios.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solutions Bento Grid -->
    <section class="py-24 px-6 max-w-7xl mx-auto" id="funcionalidades">
        <h2 class="font-headline text-3xl md:text-4xl font-bold text-primary mb-12 text-center">La plataforma todo en uno</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Bento Item 1 (Spans 2 cols on lg) -->
            <div class="lg:col-span-2 bg-surface-container-low rounded-xl p-8 flex flex-col md:flex-row gap-8 items-center border border-outline-variant/20 hover:bg-surface-container-high transition-colors">
                <div class="flex-1 space-y-4">
                    <div class="w-12 h-12 bg-primary-fixed rounded-full flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-primary">rocket_launch</span>
                    </div>
                    <h3 class="font-headline text-2xl font-bold">On-boarding en menos de 2 horas</h3>
                    <p class="font-body text-secondary">Empieza a usar BuildNess sin complicaciones técnicas. Te guiamos paso a paso para que digitalices tu operación rápidamente.</p>
                </div>
                <div class="flex-1 w-full bg-surface-container-lowest rounded-lg p-4 ambient-shadow">
                    <img alt="Calendar interface" class="w-full h-48 object-cover rounded" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJvWg2RSxFzpP4kWAQsc-UPPGWJVmlZV52bXg6G5brY-BsvtIiqxhURiWdtTTtH_biecJnYg5t2waIWeeBnu_MI4z_BhYUbiL-eivKretepa_Yf1e4sz7TjkAO3OTbduoclcjhvaysS17LHo9xaqZI7qXa77CwmTzRoMtziO_iecmlX0AMFnE_Z6e2vZRUzru7bNXdDywpLHAhCpTpK4Qz6QvnlD3k3qpeRIONWrPbQaePExthP_N_4syuU9XKyfahFCF71DT275c">
                </div>
            </div>
            <!-- Bento Item 2 -->
            <div class="bg-surface-container-low rounded-xl p-8 border border-outline-variant/20 hover:bg-surface-container-high transition-colors flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 bg-secondary-fixed rounded-full flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-on-secondary-fixed">payments</span>
                    </div>
                    <h3 class="font-headline text-2xl font-bold mb-4">Accesibilidad total</h3>
                    <p class="font-body text-secondary">Planes diseñados para la realidad del emprendedor mexicano, desde $299 MXN al mes.</p>
                </div>
            </div>
            <!-- Bento Item 3 -->
            <div class="bg-surface-container-low rounded-xl p-8 border border-outline-variant/20 hover:bg-surface-container-high transition-colors">
                <div class="w-12 h-12 bg-tertiary-fixed rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-on-tertiary-fixed">smart_toy</span>
                </div>
                <h3 class="font-headline text-2xl font-bold mb-4">Módulo de IA</h3>
                <p class="font-body text-secondary">Análisis predictivo de demanda y automatización de WhatsApp por solo $199/mes adicionales.</p>
            </div>
            <!-- Bento Item 4 (Spans 2 cols on lg) -->
            <div class="lg:col-span-2 bg-surface-container-low rounded-xl p-8 border border-outline-variant/20 hover:bg-surface-container-high transition-colors flex flex-col justify-between">
                <div class="w-12 h-12 bg-primary-fixed-dim rounded-full flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-primary">support_agent</span>
                </div>
                <div>
                    <h3 class="font-headline text-2xl font-bold mb-4">Soporte Humano 24/7 en español</h3>
                    <p class="font-body text-secondary mb-6">No hablas con robots. Nuestro equipo en México está listo para ayudarte a configurar tu cuenta y resolver dudas reales a cualquier hora.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof Section -->
    <section class="py-16 bg-surface-container-lowest">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="font-body text-lg text-secondary mb-8">Con la confianza de negocios mexicanos reales</p>
            <div class="flex flex-wrap justify-center gap-12 opacity-60 grayscale hover:grayscale-0 transition-all duration-300">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-3xl">build</span>
                    <span class="font-headline font-bold text-xl">Taller El Amigo</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-3xl">dentistry</span>
                    <span class="font-headline font-bold text-xl">Clínica Dental Juárez</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-3xl">content_cut</span>
                    <span class="font-headline font-bold text-xl">Estética Bella</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-24 bg-surface-container-low" id="precios">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="font-headline text-3xl md:text-4xl font-bold text-primary mb-4">Inversión clara, sin sorpresas</h2>
                <p class="font-body text-secondary max-w-2xl mx-auto">Planes diseñados para adaptarse a la etapa de tu negocio.</p>
            </div>
            <!-- Slider Wrapper -->
            <div class="relative max-w-6xl mx-auto px-4">
                <!-- Slider Container -->
                <div class="pricing-slider-container">
                    <!-- Slider Track -->
                    <div id="pricing-slider-track" class="pricing-slider-track select-none" style="cursor: grab;">

                        <!-- Slide 1: Suscripción -->
                        <div class="pricing-slide flex flex-col">
                            <div class="w-full max-w-lg mx-auto flex flex-col h-full" style="background:#fff;border-radius:1.5rem;box-shadow:0 8px 40px rgba(0,50,138,.10);border:1.5px solid #e5eaf5;overflow:hidden;">
                                <!-- Card Header -->
                                <div style="padding:2rem 2rem 1.25rem;background:linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 100%);border-bottom:1px solid #dbe3fa;">
                                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                                        <div style="width:2.75rem;height:2.75rem;border-radius:.875rem;background:linear-gradient(135deg,#00328a,#2156ca);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(0,50,138,.25);">
                                            <span class="material-symbols-outlined" style="color:#fff;font-size:1.25rem;font-variation-settings:'FILL' 1;">calendar_month</span>
                                        </div>
                                        <div>
                                            <span style="display:inline-block;font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#2156ca;background:#dbe1ff;border-radius:999px;padding:.2rem .75rem;margin-bottom:.2rem;">Plan Flexible</span>
                                            <h3 class="font-headline" style="font-size:1.25rem;font-weight:800;color:#00328a;margin:0;">Suscripción</h3>
                                        </div>
                                    </div>
                                    <div style="display:flex;align-items:baseline;gap:.4rem;">
                                        <span class="font-headline" style="font-size:3rem;font-weight:900;color:#00328a;line-height:1;">$299</span>
                                        <span class="font-body" style="font-size:.9rem;color:#515f74;">/mes</span>
                                    </div>
                                    <p class="font-body" style="font-size:.8rem;color:#515f74;margin-top:.5rem;">¡2 meses gratis en plan anual! Sin permanencia forzada.</p>
                                </div>
                                <!-- Card Body -->
                                <div style="padding:1.5rem 2rem 2rem;flex:1;display:flex;flex-direction:column;">
                                    <ul style="list-style:none;padding:0;margin:0 0 1.5rem;display:flex;flex-direction:column;gap:.875rem;flex:1;">
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;"><strong>ERP Solo</strong> — $299/mes</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;"><strong>ERP + CRM</strong> — $499/mes</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Soporte <strong>24/7</strong> en español</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Actualizaciones automáticas incluidas</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Cancela cuando quieras</span>
                                        </li>
                                    </ul>
                                    <button class="btn-pricing-trigger" style="width:100%;padding:.875rem;border-radius:.75rem;font-weight:700;font-size:.9rem;border:2px solid #00328a;color:#00328a;background:transparent;cursor:pointer;transition:all .2s ease;letter-spacing:.01em;" onmouseover="this.style.background='#00328a';this.style.color='#fff';" onmouseout="this.style.background='transparent';this.style.color='#00328a';">
                                        Comenzar con Suscripción →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2: Licencia Vitalicia -->
                        <div class="pricing-slide flex flex-col">
                            <div class="w-full max-w-lg mx-auto flex flex-col h-full" style="background:linear-gradient(145deg,#00328a 0%,#0047bb 60%,#1a5cd9 100%);border-radius:1.5rem;box-shadow:0 20px 60px rgba(0,50,138,.35),0 0 0 1px rgba(255,255,255,.08);overflow:hidden;position:relative;">
                                <!-- Orbs decorativos -->
                                <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.06);top:-60px;right:-60px;pointer-events:none;"></div>
                                <div style="position:absolute;width:120px;height:120px;border-radius:50%;background:rgba(133,248,196,.08);bottom:80px;left:-40px;pointer-events:none;"></div>
                                <!-- Badge popular -->
                                <div style="position:absolute;top:1.25rem;right:1.25rem;background:linear-gradient(135deg,#00422c,#005c3f);color:#85f8c4;font-size:.65rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:.35rem .9rem;border-radius:999px;display:flex;align-items:center;gap:.3rem;">
                                    <span class="material-symbols-outlined" style="font-size:.8rem;font-variation-settings:'FILL' 1;">star</span>
                                    Más Popular
                                </div>
                                <!-- Card Header -->
                                <div style="padding:2rem 2rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.12);">
                                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                                        <div style="width:2.75rem;height:2.75rem;border-radius:.875rem;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);">
                                            <span class="material-symbols-outlined" style="color:#85f8c4;font-size:1.25rem;font-variation-settings:'FILL' 1;">workspace_premium</span>
                                        </div>
                                        <div>
                                            <span style="display:inline-block;font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#85f8c4;background:rgba(133,248,196,.15);border:1px solid rgba(133,248,196,.3);border-radius:999px;padding:.2rem .75rem;margin-bottom:.2rem;">Pago Único</span>
                                            <h3 class="font-headline" style="font-size:1.25rem;font-weight:800;color:#fff;margin:0;">Licencia Vitalicia</h3>
                                        </div>
                                    </div>
                                    <div style="display:flex;align-items:baseline;gap:.5rem;flex-wrap:wrap;">
                                        <span class="font-headline" style="font-size:2.75rem;font-weight:900;color:#fff;line-height:1;">Desde $4,999</span>
                                    </div>
                                    <p class="font-body" style="font-size:.8rem;color:rgba(255,255,255,.7);margin-top:.5rem;">Un solo pago — acceso de por vida, sin mensualidades.</p>
                                </div>
                                <!-- Card Body -->
                                <div style="padding:1.5rem 2rem 2rem;flex:1;display:flex;flex-direction:column;">
                                    <ul style="list-style:none;padding:0;margin:0 0 1.5rem;display:flex;flex-direction:column;gap:.875rem;flex:1;">
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:rgba(133,248,196,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#85f8c4;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:rgba(255,255,255,.9);"><strong>ERP Solo</strong> — $4,999 pago único</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:rgba(133,248,196,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#85f8c4;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:rgba(255,255,255,.9);"><strong>ERP + CRM</strong> — $7,999 pago único</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:rgba(133,248,196,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#85f8c4;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:rgba(255,255,255,.9);">Usuarios y citas <strong>ilimitadas</strong></span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:rgba(133,248,196,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#85f8c4;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:rgba(255,255,255,.9);">Soporte prioritario de por vida</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:rgba(133,248,196,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#85f8c4;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:rgba(255,255,255,.9);">Sin mensualidades ni renovaciones</span>
                                        </li>
                                    </ul>
                                    <button class="btn-pricing-trigger" style="width:100%;padding:.875rem;border-radius:.75rem;font-weight:700;font-size:.9rem;background:linear-gradient(135deg,#00422c,#005c3f);color:#85f8c4;border:none;cursor:pointer;transition:all .2s ease;letter-spacing:.01em;box-shadow:0 4px 16px rgba(0,66,44,.4);" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(0,66,44,.5)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(0,66,44,.4)';">
                                        Obtener Licencia Vitalicia →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Add-on IA -->
                        <div class="pricing-slide flex flex-col">
                            <div class="w-full max-w-lg mx-auto flex flex-col h-full" style="background:#fff;border-radius:1.5rem;box-shadow:0 8px 40px rgba(0,50,138,.10);border:1.5px solid #e5eaf5;overflow:hidden;">
                                <!-- Card Header -->
                                <div style="padding:2rem 2rem 1.25rem;background:linear-gradient(135deg,#f0fdf9 0%,#dcfce7 100%);border-bottom:1px solid #bbf7d0;">
                                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
                                        <div style="width:2.75rem;height:2.75rem;border-radius:.875rem;background:linear-gradient(135deg,#00422c,#005c3f);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(0,66,44,.3);">
                                            <span class="material-symbols-outlined" style="color:#85f8c4;font-size:1.25rem;font-variation-settings:'FILL' 1;">smart_toy</span>
                                        </div>
                                        <div>
                                            <span style="display:inline-block;font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#005c3f;background:#bbf7d0;border-radius:999px;padding:.2rem .75rem;margin-bottom:.2rem;">Potencia IA</span>
                                            <h3 class="font-headline" style="font-size:1.25rem;font-weight:800;color:#00422c;margin:0;">Add-on Inteligencia Artificial</h3>
                                        </div>
                                    </div>
                                    <div style="display:flex;align-items:baseline;gap:.4rem;">
                                        <span class="font-headline" style="font-size:3rem;font-weight:900;color:#00422c;line-height:1;">$199</span>
                                        <span class="font-body" style="font-size:.9rem;color:#515f74;">/mes</span>
                                    </div>
                                    <p class="font-body" style="font-size:.8rem;color:#515f74;margin-top:.5rem;">Compatible con cualquier plan. Actívalo o desactívalo cuando quieras.</p>
                                </div>
                                <!-- Card Body -->
                                <div style="padding:1.5rem 2rem 2rem;flex:1;display:flex;flex-direction:column;">
                                    <ul style="list-style:none;padding:0;margin:0 0 1.5rem;display:flex;flex-direction:column;gap:.875rem;flex:1;">
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;"><strong>Análisis predictivo</strong> de demanda</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Automatización de <strong>WhatsApp</strong></span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Insights de negocio en <strong>tiempo real</strong></span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Recomendaciones de inventario con IA</span>
                                        </li>
                                        <li style="display:flex;align-items:flex-start;gap:.75rem;">
                                            <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">
                                                <span class="material-symbols-outlined" style="font-size:.875rem;color:#16a34a;font-variation-settings:'FILL' 1;">check</span>
                                            </span>
                                            <span class="font-body" style="font-size:.875rem;color:#171c1f;">Reportes ejecutivos automáticos</span>
                                        </li>
                                    </ul>
                                    <button class="btn-pricing-trigger" style="width:100%;padding:.875rem;border-radius:.75rem;font-weight:700;font-size:.9rem;background:linear-gradient(135deg,#00422c,#005c3f);color:#fff;border:none;cursor:pointer;transition:all .2s ease;letter-spacing:.01em;box-shadow:0 4px 16px rgba(0,66,44,.25);" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(0,66,44,.35)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(0,66,44,.25)';">
                                        Activar Módulo IA →
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Navigation Arrows -->
                <button id="slider-prev" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 md:-translate-x-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110 z-20 focus:outline-none hover:bg-slate-50 dark:hover:bg-slate-800" aria-label="Anterior">
                    <span class="material-symbols-outlined font-bold text-2xl">chevron_left</span>
                </button>
                <button id="slider-next" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 md:translate-x-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110 z-20 focus:outline-none hover:bg-slate-50 dark:hover:bg-slate-800" aria-label="Siguiente">
                    <span class="material-symbols-outlined font-bold text-2xl">chevron_right</span>
                </button>

                <!-- Indicator Dots -->
                <div id="slider-dots" class="flex justify-center items-center gap-3 mt-6"></div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-24 px-6 max-w-5xl mx-auto text-center">
        <h2 class="font-headline text-4xl font-bold text-on-surface mb-8">¿Listo para llevar tu negocio al siguiente nivel?</h2>
        <p class="font-body text-secondary mb-10 max-w-2xl mx-auto">Únete a cientos de emprendedores que ya digitalizaron su operación.</p>
        <button id="open-register-btn-cta" class="btn-primary px-10 py-4 font-label font-bold text-lg shadow-lg">
            Registrarse ahora
        </button>
    </section>

    <!-- Footer -->
    <footer class="w-full py-12 px-6 mt-24 bg-slate-100 dark:bg-slate-900">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="font-['Manrope'] font-extrabold text-blue-900 dark:text-blue-50 text-xl">
                BuildNess
            </div>
            <div class="flex gap-6">
                <a class="font-['Inter'] text-xs uppercase tracking-widest font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors" href="#">Privacidad</a>
                <a class="font-['Inter'] text-xs uppercase tracking-widest font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors" href="#">Términos</a>
                <a class="font-['Inter'] text-xs uppercase tracking-widest font-medium text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors" href="#">Contacto</a>
            </div>
            <div class="font-body text-sm text-secondary text-center md:text-right">
                © 2024 BuildNess. Un proyecto respaldado por el TecNM.
            </div>
        </div>
    </footer>

    <!-- Login & Register Modal -->
    <div id="login-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-8 shadow-2xl border border-slate-200/50 dark:border-slate-800/50 transform transition-all relative">
            <button id="close-login-btn" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="text-center mb-6">
                <div class="text-2xl font-black font-['Manrope'] text-blue-900 dark:text-blue-50 tracking-tighter mb-2">BuildNess</div>
                <h3 id="modal-title" class="text-xl font-bold text-slate-800 dark:text-slate-100">Iniciar Sesión</h3>
                <p id="modal-subtitle" class="text-sm text-slate-500 dark:text-slate-400 mt-1">Ingresa tus credenciales de administrador</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-400 rounded-lg p-3 text-sm mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form action="index.php" method="POST" id="login-form" class="space-y-4 <?php echo $active_tab === 'register' ? 'hidden' : ''; ?>">
                <input type="hidden" name="action" value="login">
                <div>
                    <label for="login-email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Correo Electrónico</label>
                    <input type="email" name="email" id="login-email" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all text-sm" placeholder="admin@taller.com" value="<?php echo htmlspecialchars($submitted_email); ?>">
                </div>
                <div>
                    <label for="login-password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Contraseña</label>
                    <input type="password" name="password" id="login-password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all text-sm" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full py-3 bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-lg shadow-md transition-colors text-sm mt-2">
                    Ingresar al Sistema
                </button>
                <div class="text-center mt-4">
                    <p class="text-xs text-slate-500">¿No tienes una cuenta? <a href="#" id="toggle-to-register" class="text-blue-600 hover:underline font-semibold">Regístrate aquí</a></p>
                </div>
            </form>

            <!-- REGISTER FORM -->
            <form action="index.php" method="POST" id="register-form" class="space-y-4 <?php echo $active_tab === 'register' ? '' : 'hidden'; ?>">
                <input type="hidden" name="action" value="register">
                <div>
                    <label for="register-nombre" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" id="register-nombre" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all text-sm" placeholder="Ej. Juan Pérez">
                </div>
                <div>
                    <label for="register-email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Correo Electrónico</label>
                    <input type="email" name="email" id="register-email" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all text-sm" placeholder="usuario@taller.com" autocomplete="email">
                    <!-- Indicador de disponibilidad de correo (DOM en tiempo real) -->
                    <div id="register-email-feedback" class="flex items-center gap-1.5 mt-1.5 text-xs font-medium min-h-[18px] transition-all duration-300" aria-live="polite"></div>
                </div>
                <div>
                    <label for="register-password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Contraseña</label>
                    <input type="password" name="password" id="register-password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all text-sm" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full py-3 bg-emerald-700 hover:bg-emerald-600 text-white font-semibold rounded-lg shadow-md transition-colors text-sm mt-2">
                    Crear Cuenta
                </button>
                <div class="text-center mt-4">
                    <p class="text-xs text-slate-500">¿Ya tienes una cuenta? <a href="#" id="toggle-to-login" class="text-blue-600 hover:underline font-semibold">Inicia sesión aquí</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Script for Modal Interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // ==========================================
            // VALIDACIÓN DE CORREO EN TIEMPO REAL (DOM)
            // ==========================================
            const registerEmailInput  = document.getElementById('register-email');
            const emailFeedback       = document.getElementById('register-email-feedback');
            const registerSubmitBtn   = document.querySelector('#register-form button[type="submit"]');

            // Estados visuales del indicador
            const feedbackStates = {
                checking: {
                    icon : 'hourglass_top',
                    color: 'text-slate-400',
                    text : 'Verificando...',
                },
                available: {
                    icon : 'check_circle',
                    color: 'text-emerald-600',
                    text : 'Correo disponible.',
                },
                taken: {
                    icon : 'cancel',
                    color: 'text-red-500',
                    text : 'Este correo ya está registrado.',
                },
                invalid: {
                    icon : 'warning',
                    color: 'text-amber-500',
                    text : 'Formato de correo no válido.',
                },
                empty: { icon: '', color: '', text: '' },
            };

            /**
             * Actualiza el nodo DOM del feedback según el estado dado.
             * @param {'checking'|'available'|'taken'|'invalid'|'empty'} state
             * @param {string} [customText] - Texto alternativo opcional.
             */
            function setFeedback(state, customText) {
                const cfg = feedbackStates[state] ?? feedbackStates.empty;
                const label = customText ?? cfg.text;

                if (!label) {
                    emailFeedback.innerHTML = '';
                    return;
                }

                emailFeedback.innerHTML =
                    `<span class="material-symbols-outlined text-sm leading-none ${cfg.color}">${cfg.icon}</span>` +
                    `<span class="${cfg.color}">${label}</span>`;

                // Actualizar borde del input
                registerEmailInput.classList.remove(
                    'border-emerald-400', 'border-red-400', 'border-amber-400', 'border-slate-200'
                );
                if (state === 'available')  registerEmailInput.classList.add('border-emerald-400');
                if (state === 'taken')      registerEmailInput.classList.add('border-red-400');
                if (state === 'invalid')    registerEmailInput.classList.add('border-amber-400');
                if (state === 'empty' || state === 'checking') registerEmailInput.classList.add('border-slate-200');
            }

            // Habilitar/deshabilitar el botón de submit según disponibilidad
            let emailIsAvailable = false;

            function updateSubmitState(available) {
                emailIsAvailable = available;
                if (registerSubmitBtn) {
                    registerSubmitBtn.disabled = !available;
                    registerSubmitBtn.style.opacity = available ? '1' : '0.5';
                    registerSubmitBtn.style.cursor  = available ? 'pointer' : 'not-allowed';
                }
            }

            // Debounce: esperar 550 ms tras la última tecla antes de consultar
            let debounceTimer = null;
            const DEBOUNCE_MS = 550;

            registerEmailInput.addEventListener('input', () => {
                const email = registerEmailInput.value.trim();

                clearTimeout(debounceTimer);
                updateSubmitState(false);

                if (email === '') {
                    setFeedback('empty');
                    return;
                }

                // Validación de formato local antes de ir al servidor
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    setFeedback('invalid');
                    return;
                }

                setFeedback('checking');

                debounceTimer = setTimeout(() => {
                    // Petición AJAX al endpoint PHP
                    const formData = new FormData();
                    formData.append('email', email);

                    fetch('php/check_email.php', {
                        method : 'POST',
                        body   : formData,
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error de red');
                        return res.json();
                    })
                    .then(data => {
                        // Solo aplicar si el input no cambió mientras esperábamos
                        if (registerEmailInput.value.trim() !== email) return;

                        if (data.available === true) {
                            setFeedback('available', data.message);
                            updateSubmitState(true);
                        } else if (data.available === false) {
                            const isTaken = data.message && data.message.toLowerCase().includes('registrado');
                            setFeedback(isTaken ? 'taken' : 'invalid', data.message);
                            updateSubmitState(false);
                        }
                    })
                    .catch(() => {
                        // Si hay error de red, no bloquear el envío pero mostrar aviso
                        setFeedback('empty');
                        updateSubmitState(true);
                    });
                }, DEBOUNCE_MS);
            });

            // Restablecer estado al limpiar el campo manualmente
            registerEmailInput.addEventListener('blur', () => {
                if (registerEmailInput.value.trim() === '') {
                    setFeedback('empty');
                    updateSubmitState(false);
                }
            });
            // ==========================================
            // FIN VALIDACIÓN DE CORREO EN TIEMPO REAL
            // ==========================================

            const modal = document.getElementById('login-modal');
            const closeBtn = document.getElementById('close-login-btn');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const modalTitle = document.getElementById('modal-title');
            const modalSubtitle = document.getElementById('modal-subtitle');
            
            const toggleToRegister = document.getElementById('toggle-to-register');
            const toggleToLogin = document.getElementById('toggle-to-login');

            // Collect all triggers for the login modal
            const triggers = [
                document.getElementById('open-login-btn-nav'),
                document.getElementById('open-login-btn-mobile'),
                document.getElementById('open-login-btn-hero'),
            ];

            // Also attach to pricing plans & demo
            document.querySelectorAll('.btn-pricing-trigger').forEach(btn => triggers.push(btn));
            const demoBtn = document.getElementById('open-login-btn-demo');
            if (demoBtn) triggers.push(demoBtn);


            const openModal = () => {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (registerForm.classList.contains('hidden')) {
                    document.getElementById('login-email').focus();
                } else {
                    document.getElementById('register-nombre').focus();
                }
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const showLogin = () => {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                modalTitle.textContent = "Iniciar Sesión";
                modalSubtitle.textContent = "Ingresa tus credenciales de administrador";
                document.getElementById('login-email').focus();
            };

            const showRegister = () => {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                modalTitle.textContent = "Registrarse";
                modalSubtitle.textContent = "Crea tu cuenta de usuario";
                document.getElementById('register-nombre').focus();
            };

            toggleToRegister.addEventListener('click', (e) => {
                e.preventDefault();
                showRegister();
            });

            toggleToLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLogin();
            });

            // Botón CTA final → abre modal directamente en el tab de REGISTRO
            const registerCtaBtn = document.getElementById('open-register-btn-cta');
            if (registerCtaBtn) {
                registerCtaBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal();
                    showRegister();
                });
            }

            triggers.forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        openModal();
                    });
                }
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Automatically open the modal if there's a login/register error
            <?php if (!empty($error_message)): ?>
                openModal();
                <?php if ($active_tab === 'register'): ?>
                    showRegister();
                <?php else: ?>
                    showLogin();
                <?php endif; ?>
            <?php endif; ?>

            // ==========================================
            // PRICING SLIDER LOGIC
            // ==========================================
            const sliderTrack = document.getElementById('pricing-slider-track');
            const slides = document.querySelectorAll('.pricing-slide');
            const btnPrev = document.getElementById('slider-prev');
            const btnNext = document.getElementById('slider-next');
            const dotsContainer = document.getElementById('slider-dots');
            
            let currentSlide = 0;
            let startX = 0;
            let currentX = 0;
            let isDragging = false;
            
            function getSlidesToShow() {
                return 1;
            }
            
            function updateSlider() {
                if (!sliderTrack || slides.length === 0) return;
                
                const slidesToShow = getSlidesToShow();
                const totalSlides = slides.length;
                const maxIndex = Math.max(0, totalSlides - slidesToShow);
                
                if (currentSlide > maxIndex) {
                    currentSlide = maxIndex;
                }
                
                const slidePercent = 100 / slidesToShow;
                sliderTrack.style.transform = `translateX(-${currentSlide * slidePercent}%)`;
                
                if (maxIndex === 0) {
                    if (btnPrev) btnPrev.style.display = 'none';
                    if (btnNext) btnNext.style.display = 'none';
                    if (dotsContainer) dotsContainer.style.display = 'none';
                } else {
                    if (btnPrev) {
                        btnPrev.style.display = 'flex';
                        btnPrev.disabled = (currentSlide === 0);
                        btnPrev.style.opacity = (currentSlide === 0) ? '0.4' : '1';
                        btnPrev.style.cursor = (currentSlide === 0) ? 'not-allowed' : 'pointer';
                    }
                    if (btnNext) {
                        btnNext.style.display = 'flex';
                        btnNext.disabled = (currentSlide === maxIndex);
                        btnNext.style.opacity = (currentSlide === maxIndex) ? '0.4' : '1';
                        btnNext.style.cursor = (currentSlide === maxIndex) ? 'not-allowed' : 'pointer';
                    }
                    if (dotsContainer) {
                        dotsContainer.style.display = 'flex';
                        const dots = dotsContainer.querySelectorAll('.slider-dot');
                        dots.forEach((dot, idx) => {
                            if (idx === currentSlide) {
                                dot.classList.add('active');
                            } else {
                                dot.classList.remove('active');
                            }
                            dot.style.display = (idx > maxIndex) ? 'none' : 'inline-block';
                        });
                    }
                }
            }
            
            function buildDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                const slidesToShow = getSlidesToShow();
                const totalSlides = slides.length;
                const maxIndex = Math.max(0, totalSlides - slidesToShow);
                
                for (let i = 0; i <= maxIndex; i++) {
                    const dot = document.createElement('button');
                    dot.className = 'slider-dot focus:outline-none';
                    if (i === currentSlide) dot.classList.add('active');
                    dot.setAttribute('aria-label', `Ir al plan ${i + 1}`);
                    dot.addEventListener('click', (e) => {
                        e.preventDefault();
                        currentSlide = i;
                        updateSlider();
                    });
                    dotsContainer.appendChild(dot);
                }
            }
            
            if (sliderTrack && slides.length > 0) {
                if (btnPrev) {
                    btnPrev.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (currentSlide > 0) {
                            currentSlide--;
                            updateSlider();
                        }
                    });
                }
                
                if (btnNext) {
                    btnNext.addEventListener('click', (e) => {
                        e.preventDefault();
                        const slidesToShow = getSlidesToShow();
                        const maxIndex = slides.length - slidesToShow;
                        if (currentSlide < maxIndex) {
                            currentSlide++;
                            updateSlider();
                        }
                    });
                }
                
                // Swipe Support (Touch Events)
                sliderTrack.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    currentX = startX;
                    isDragging = true;
                    sliderTrack.style.transition = 'none';
                }, { passive: true });
                
                sliderTrack.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    currentX = e.touches[0].clientX;
                    const diffX = currentX - startX;
                    
                    const slidesToShow = getSlidesToShow();
                    const slidePercent = 100 / slidesToShow;
                    const containerWidth = sliderTrack.parentElement.clientWidth;
                    const currentOffsetPercent = -(currentSlide * slidePercent);
                    const dragPercent = (diffX / containerWidth) * 100;
                    
                    sliderTrack.style.transform = `translateX(${currentOffsetPercent + dragPercent}%)`;
                }, { passive: true });
                
                sliderTrack.addEventListener('touchend', () => {
                    if (!isDragging) return;
                    isDragging = false;
                    sliderTrack.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1)';
                    
                    const diffX = currentX - startX;
                    const threshold = 50;
                    
                    const slidesToShow = getSlidesToShow();
                    const maxIndex = slides.length - slidesToShow;
                    
                    if (Math.abs(diffX) > threshold) {
                        if (diffX > 0 && currentSlide > 0) {
                            currentSlide--;
                        } else if (diffX < 0 && currentSlide < maxIndex) {
                            currentSlide++;
                        }
                    }
                    updateSlider();
                });
                
                // Drag Support (Mouse Events)
                sliderTrack.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    startX = e.clientX;
                    currentX = startX;
                    isDragging = true;
                    sliderTrack.style.transition = 'none';
                    sliderTrack.style.cursor = 'grabbing';
                });
                
                window.addEventListener('mousemove', (e) => {
                    if (!isDragging) return;
                    currentX = e.clientX;
                    const diffX = currentX - startX;
                    
                    const slidesToShow = getSlidesToShow();
                    const slidePercent = 100 / slidesToShow;
                    const containerWidth = sliderTrack.parentElement.clientWidth;
                    const currentOffsetPercent = -(currentSlide * slidePercent);
                    const dragPercent = (diffX / containerWidth) * 100;
                    
                    sliderTrack.style.transform = `translateX(${currentOffsetPercent + dragPercent}%)`;
                });
                
                window.addEventListener('mouseup', () => {
                    if (!isDragging) return;
                    isDragging = false;
                    sliderTrack.style.transition = 'transform 0.5s cubic-bezier(0.25, 1, 0.5, 1)';
                    sliderTrack.style.cursor = 'grab';
                    
                    const diffX = currentX - startX;
                    const threshold = 50;
                    
                    const slidesToShow = getSlidesToShow();
                    const maxIndex = slides.length - slidesToShow;
                    
                    if (Math.abs(diffX) > threshold) {
                        if (diffX > 0 && currentSlide > 0) {
                            currentSlide--;
                        } else if (diffX < 0 && currentSlide < maxIndex) {
                            currentSlide++;
                        }
                    }
                    updateSlider();
                });
                
                // Window Resize
                window.addEventListener('resize', () => {
                    buildDots();
                    updateSlider();
                });
                
                // Initialize
                buildDots();
                updateSlider();
            }
        });
    </script>
</body>
</html>
