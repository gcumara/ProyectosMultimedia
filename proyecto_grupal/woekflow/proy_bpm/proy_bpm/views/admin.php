<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <nav class="navbar dark">
        <h2>Panel ADMINISTRADOR</h2>
        <div>
            <span style="margin-right: 15px;">Hola, <?= htmlspecialchars($currentUser['nombre']) ?></span>
            <a href="index.php?action=logout" class="btn danger">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h3>Registrar Nuevo Usuario</h3>
        <div class="card form-card">
            <form action="index.php?action=admin_create_user" method="POST">
                <input type="text" name="nombre" placeholder="Nombre Real" required>
                <input type="text" name="username" placeholder="Usuario (Login)" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <select name="role" style="padding: 10px; border-radius: 5px;">
                    <option value="estudiante">Estudiante</option>
                    <option value="kardex">Kardex</option>
                </select>
                <button type="submit" class="btn primary">Crear Usuario</button>
            </form>
        </div>

        <h3>Lista de Usuarios</h3>
        <?php foreach ($usuarios as $u): ?>
            <div class="card process-card" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['username']) ?>)</p>
                    <p><span class="badge"><?= strtoupper($u['role']) ?></span></p>
                </div>
                <?php if ($u['username'] !== 'admin'): ?>
                    <a href="index.php?action=admin_delete_user&id=<?= $u['id'] ?>" class="btn danger" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <hr style="margin: 30px 0;">

        <h3>Registrar Nueva Materia</h3>
        <div class="card form-card">
            <form action="index.php?action=admin_create_materia" method="POST">
                <input type="text" name="codigo" placeholder="Código (Ej: SIS-101)" required>
                <input type="text" name="nombre" placeholder="Nombre de la Materia" required>
                <input type="text" name="prerequisito" placeholder="Prerrequisito (o 'Ninguno')">
                <button type="submit" class="btn primary">Crear Materia</button>
            </form>
        </div>

        <h3>Lista de Materias</h3>
        <?php foreach ($materias as $m): ?>
            <div class="card process-card" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p><strong><?= htmlspecialchars($m['codigo']) ?></strong> - <?= htmlspecialchars($m['nombre']) ?></p>
                    <p style="font-size: 0.9em; color: #666;">Prerrequisito: <?= htmlspecialchars($m['prerequisito']) ?></p>
                </div>
                <a href="index.php?action=admin_delete_materia&id=<?= $m['id_materia'] ?>" class="btn danger" onclick="return confirm('¿Eliminar materia?')">Eliminar</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>