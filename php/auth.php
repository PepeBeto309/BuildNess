<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Comprueba si el usuario tiene una sesión activa.
 * 
 * @return bool True si el usuario está logueado, false de lo contrario.
 */
function esta_autenticado(): bool {
    return isset($_SESSION['usuario_id']);
}

/**
 * Protege páginas internas. Si no está autenticado, redirige al landing page con un aviso.
 */
function requerir_autenticacion(): void {
    if (!esta_autenticado()) {
        header('Location: index.php?error=no_session');
        exit;
    }
}
?>
