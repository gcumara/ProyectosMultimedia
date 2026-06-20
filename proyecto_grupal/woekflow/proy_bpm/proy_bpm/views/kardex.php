<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Workflow - Panel Kardex</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar dark">
        <h2>Panel KARDEX</h2>
        <div>
            <span style="margin-right: 15px; color: white;">Hola, <?= htmlspecialchars($currentUser['nombre']) ?></span>
            <a href="index.php?action=logout" class="btn danger">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h3>Bandeja de Trámites Activos</h3>
        <?php 
        $hayActivos = false;
        foreach ($procesos as $p): 
            // Omitir estados archivados de la bandeja activa
            if (in_array($p['estado'], ['FIN', 'INSCRIPCION_RECHAZADA', 'RETIRO_ADICION_RECHAZADO', 'RECHAZO_ARCHIVADO'])) continue;
            $hayActivos = true;
            $tipoLabel = ($p['tipo'] ?? 'INSCRIPCION') === 'RETIRO_ADICION' ? 'Retiro / Adición' : 'Inscripción Ordinaria';
        ?>
            <div class="card process-card">
                <p><strong>ID Trámite:</strong> <?= $p['id'] ?> | <strong>Estudiante:</strong> <?= htmlspecialchars($p['estudiante']) ?></p>
                <p><strong>Tipo de Solicitud:</strong> <span style="color:#007bff; font-weight:bold;"><?= $tipoLabel ?></span></p>
                <?php if (!empty($p['materias'])): ?>
                    <p><strong>Materias Propuestas:</strong> <?= htmlspecialchars($p['materias']) ?></p>
                <?php endif; ?>
                <p><span class="badge dark-badge"><?= $p['estado'] ?></span></p>

                <div class="actions" style="margin-top: 15px; padding: 10px; background: #fcfcfc; border-radius: 4px;">
                    <?php if ($p['estado'] === 'RETIRO_ADICION_SOLICITADO'): ?>
                        <p style="margin-bottom: 8px;"><strong>Procesar Solicitud de Retiro / Adición:</strong></p>
                        <form action="index.php?action=kardex_aceptar_retiro" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn success">Aceptar Solicitud</button>
                        </form>
                        
                        <form action="index.php?action=kardex_rechazar_retiro" method="POST" style="display:inline-block; margin-left: 10px;" onsubmit="return solicitarMotivo(this);">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="motivo" id="motivo_<?= $p['id'] ?>" value="">
                            <button type="submit" class="btn danger">Rechazar Solicitud</button>
                        </form>

                    <?php elseif ($p['estado'] === 'SOLICITUD_ENVIADA'): ?>
                        <p style="margin-bottom: 8px;"><strong>Procesar Solicitud de Inscripción Inicial:</strong></p>
                        <form action="index.php?action=solicitar_pago" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn primary">Validar y Solicitar Pago</button>
                        </form>
                        
                        <form action="index.php?action=kardex_rechazar_inscripcion" method="POST" style="display:inline-block; margin-left: 10px;" onsubmit="return solicitarMotivo(this);">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="motivo" id="motivo_<?= $p['id'] ?>" value="">
                            <button type="submit" class="btn danger">Rechazar Solicitud</button>
                        </form>

                    <?php elseif ($p['estado'] === 'PAGO_REALIZADO'): ?>
                        <p>¿El depósito bancario es correcto?</p>
                        <form action="index.php?action=verificar_pago_ok" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn success">Sí (Enviar Materias)</button>
                        </form>
                        <form action="index.php?action=verificar_pago_error" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn danger">No (Rebotar Pago)</button>
                        </form>
                    <?php elseif ($p['estado'] === 'INSCRIPCION_ENVIADA'): ?>
                        <p>¿Aprobar carga de materias seleccionadas?</p>
                        <form action="index.php?action=aprobar_carga_ok" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn success">Sí (Emitir Comprobante)</button>
                        </form>
                        <form action="index.php?action=aprobar_carga_error" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn danger">No (Rechazar Carga)</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$hayActivos): ?>
            <p style="color: #777;">No tienes solicitudes pendientes de atención.</p>
        <?php endif; ?>


        <hr style="margin: 40px 0; border: 0; border-top: 2px dashed #ccc;">
        
        <h3>Historial de Trámites Concluidos y Rechazados</h3>
        <?php 
        $hayConcluidos = false;
        foreach ($procesos as $p): 
            if (!in_array($p['estado'], ['FIN', 'INSCRIPCION_RECHAZADA', 'RETIRO_ADICION_RECHAZADO', 'RECHAZO_ARCHIVADO'])) continue;
            $hayConcluidos = true;
            $tipoLabel = ($p['tipo'] ?? 'INSCRIPCION') === 'RETIRO_ADICION' ? 'Retiro / Adición' : 'Inscripción Ordinaria';
        ?>
            <div class="card" style="opacity: 0.85; background-color: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; margin-bottom: 10px;">
                <p style="font-size: 0.9em; color:#555;">
                    <strong>ID:</strong> <?= $p['id'] ?> | 
                    <strong>Estudiante:</strong> <?= htmlspecialchars($p['estudiante']) ?> | 
                    <strong>Tipo:</strong> <?= $tipoLabel ?> |
                    <strong>Fecha:</strong> <?= $p['fecha_creacion'] ?> 
                </p>
                <?php if (!empty($p['materias'])): ?>
                    <p style="font-size: 0.9em; margin: 4px 0;"><strong>Materias:</strong> <?= htmlspecialchars($p['materias']) ?></p>
                <?php endif; ?>
                <?php if (!empty($p['motivo_rechazo'])): ?>
                    <p style="font-size: 0.9em; color: #c0392b;"><strong>Motivo del Rechazo:</strong> <?= htmlspecialchars($p['motivo_rechazo']) ?></p>
                <?php endif; ?>
                <p style="margin-top: 5px;"><span class="badge" style="background-color: #6c757d; color: white; font-size: 0.8em;"><?= $p['estado'] ?></span></p>
            </div>
        <?php endforeach; ?>
        <?php if (!$hayConcluidos): ?>
            <p style="color: #999; font-style: italic;">No existen registros en el histórico todavía.</p>
        <?php endif; ?>

    </div>

    <script>
    function solicitarMotivo(formElement) {
        var motivo = prompt("Por favor, introduzca el motivo del rechazo de la solicitud:");
        if (motivo === null) {
            return false; // Cancela el envío si hace clic en cancelar
        }
        if (motivo.trim() === "") {
            alert("Debe especificar un motivo para poder rechazar.");
            return false;
        }
        // Asignar el valor al campo oculto correspondiente dentro de este formulario
        formElement.querySelector('input[name="motivo"]').value = motivo;
        return true;
    }
    </script>
</body>
</html>