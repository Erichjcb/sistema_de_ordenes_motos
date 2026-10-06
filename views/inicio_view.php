<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Sistema de Repuestos</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; background-color: #f4f7f6; }
        .btn-rol { display: inline-block; margin: 20px; padding: 20px 40px; font-size: 20px; text-decoration: none; border-radius: 8px; color: white; font-weight: bold; }
        .btn-cliente { background-color: #007bff; }
        .btn-proveedor { background-color: #28a745; }
        .btn-rol:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <h2>🏍️ Bienvenido al Sistema de Órdenes</h2>
    <p>Selecciona tu perfil de acceso para continuar:</p>
    <a href="index.php?rol=cliente" class="btn-rol btn-cliente">Soy Cliente (Customer)</a>
    <a href="index.php?rol=proveedor" class="btn-rol btn-proveedor">Soy Proveedor (Supplier)</a>
</body>
</html>