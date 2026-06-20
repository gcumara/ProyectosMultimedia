<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 100px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">Iniciar Sesión</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form action="index.php?action=login_post" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" name="username" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" class="btn primary">Entrar</button>
            </form>
            <p style="margin-top: 15px; font-size: 0.9em; text-align: center;">
                Admin por defecto: <strong>admin</strong> / <strong>admin</strong>
            </p>
        </div>
    </div>
</body>
</html>