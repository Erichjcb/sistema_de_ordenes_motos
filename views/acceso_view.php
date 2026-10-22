<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - Sistema de Órdenes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px auto; max-width: 440px; background-color: #f4f7f6; color: #333; }
        .caja { background: white; padding: 24px; border: 1px solid #ddd; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; box-sizing: border-box; }
        button, .enlace { display: inline-block; padding: 10px 16px; border: 0; border-radius: 4px; background: #007bff; color: white; text-decoration: none; cursor: pointer; }
        .alerta { padding: 12px; margin-bottom: 16px; border-radius: 4px; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="caja">
        <h2>🏍️ Sistema de Órdenes</h2>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alerta <?= htmlspecialchars($_SESSION['tipo_mensaje'] ?? 'error', ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <?php if ($authView === 'registro'): ?>
            <h3>Crear cuenta de Customer</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="auth_action" value="registro">
                <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" required minlength="3" maxlength="50" autocomplete="username">
                <label for="contrasena">Contraseña (mínimo 8 caracteres)</label>
                <input id="contrasena" type="password" name="contrasena" required minlength="8" autocomplete="new-password">
                <label for="confirmacion">Confirmar contraseña</label>
                <input id="confirmacion" type="password" name="confirmacion" required minlength="8" autocomplete="new-password">
                <button type="submit">Registrarme</button>
            </form>
            <p><a href="index.php?acceso=login">Ya tengo una cuenta</a></p>
        <?php elseif ($authView === 'login'): ?>
            <h3>Iniciar sesión</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="auth_action" value="login">
                <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" required autocomplete="username">
                <label for="contrasena">Contraseña</label>
                <input id="contrasena" type="password" name="contrasena" required autocomplete="current-password">
                <button type="submit">Entrar</button>
            </form>
            <p><a href="index.php?acceso=registro">Crear cuenta de Customer</a></p>
        <?php else: ?>
            <p>Inicia sesión para administrar o consultar tus órdenes.</p>
            <p><a class="enlace" href="index.php?acceso=login">Iniciar sesión</a></p>
            <p><a href="index.php?acceso=registro">Registrarme como Customer</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
