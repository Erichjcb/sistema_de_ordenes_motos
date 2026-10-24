<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - Sistema de Órdenes</title>
    <link rel="stylesheet" href="assets/css/tienda.css">
</head>
<body>
    <main class="page-shell auth-shell">
    <div class="auth-box">
        <a class="brand-lockup" href="index.php?pagina=catalogo"><span class="brand-mark">🏍</span><span>Ruta Moto<br><small>Repuestos & accesorios</small></span></a>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alerta <?= htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'error', ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <?php if ($authView === 'registro'): ?>
            <h2>Crear cuenta de Customer</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="auth_action" value="registro">
                <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" required minlength="3" maxlength="50" autocomplete="username">
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" name="ciudad" required maxlength="100" autocomplete="address-level2">
                <label for="distrito">Distrito</label>
                <input id="distrito" name="distrito" required maxlength="100" autocomplete="address-level3">
                <label for="contrasena">Contraseña (mínimo 8 caracteres)</label>
                <input id="contrasena" type="password" name="contrasena" required minlength="8" autocomplete="new-password">
                <label for="confirmacion">Confirmar contraseña</label>
                <input id="confirmacion" type="password" name="confirmacion" required minlength="8" autocomplete="new-password">
                <button type="submit">Registrarme</button>
            </form>
            <div class="auth-links"><a href="index.php?acceso=login">Ya tengo una cuenta</a><a href="index.php?pagina=catalogo">Volver al catálogo</a></div>
        <?php elseif ($authView === 'login'): ?>
            <h2>Iniciar sesión</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="auth_action" value="login">
                <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" required autocomplete="username">
                <label for="contrasena">Contraseña</label>
                <input id="contrasena" type="password" name="contrasena" required autocomplete="current-password">
                <button type="submit">Entrar</button>
            </form>
            <div class="auth-links"><a href="index.php?acceso=registro">Crear cuenta de Customer</a><a href="index.php?pagina=catalogo">Volver al catálogo</a></div>
        <?php else: ?>
            <p>Explora el catálogo sin iniciar sesión. Necesitarás una cuenta Customer al finalizar la compra.</p>
            <div class="auth-links"><a class="boton" href="index.php?acceso=login">Iniciar sesión</a><a href="index.php?acceso=registro">Registrarme como Customer</a><a href="index.php?pagina=catalogo">Ver catálogo</a></div>
        <?php endif; ?>
    </div>
    </main>
</body>
</html>
