<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Workflow - Panel Estudiante</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <h2>Panel ESTUDIANTE</h2>
        <div>
            <span style="margin-right: 15px;">Hola, <?= htmlspecialchars($currentUser['nombre']) ?></span>
            <a href="index.php?action=logout" class="btn danger">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <?php 
            $tramiteActivo = false;
            $materiasInscritasTexto = 'Ninguna';

            foreach ($procesos as $p) {
                // El trámite se considera activo si está en curso o si es una notificación de rechazo pendiente de cerrar
                if (!in_array($p['estado'], ['FIN', 'RECHAZO_ARCHIVADO'])) {
                    $tramiteActivo = true;
                }
                if ($p['estado'] === 'FIN') {
                    $materiasInscritasTexto = $p['materias']; 
                }
            }
            $materiasInscritasArray = ($materiasInscritasTexto !== 'Ninguna') ? explode(', ', $materiasInscritasTexto) : [];
        ?>

        <?php if (!$tramiteActivo): ?>
            <div class="card form-card">
                <h3>Generar Nueva Solicitud</h3>
                <form action="index.php?action=iniciar_solicitud" method="POST" style="flex-direction: column; gap: 12px; align-items: flex-start; width: 100%;">
                    <label for="tipo"><strong>Seleccione la acción que desea realizar:</strong></label>
                    <select name="tipo" id="tipo" style="padding: 10px; border-radius: 5px; width: 100%; max-width: 400px; border: 1px solid #ccc;" required>
                        <option value="INSCRIPCION">Solicitar Inscripción Ordinaria</option>
                        <option value="RETIRO_ADICION">Solicitar Retiro o Adición de Materias</option>
                    </select>
                    <button type="submit" class="btn primary">Iniciar Trámite</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card warning" style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Aviso:</strong> Tienes un trámite en curso o una notificación pendiente. Resuélvelo antes de abrir otro.
            </div>
        <?php endif; ?>

        <h3>Mi Trámite Activo</h3>
        <?php foreach ($procesos as $p): ?>
            <?php if (in_array($p['estado'], ['FIN', 'RECHAZO_ARCHIVADO'])) continue; ?>
            
            <div class="card process-card">
                <?php if (!in_array($p['estado'], ['INSCRIPCION_RECHAZADA', 'RETIRO_ADICION_RECHAZADO'])): ?>
                    <form action="index.php?action=cancelar_solicitud" method="POST" style="float: right;">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn danger" onclick="return confirm('¿Deseas cancelar este trámite? Se eliminará de forma permanente.')">Cancelar Trámite</button>
                    </form>
                <?php endif; ?>

                <p><strong>ID Trámite:</strong> <?= $p['id'] ?> | <strong>Tipo:</strong> <?= ($p['tipo'] ?? 'INSCRIPCION') === 'RETIRO_ADICION' ? 'Retiro / Adición' : 'Inscripción' ?></p>
                <p><span class="badge"><?= $p['estado'] ?></span></p>

                <?php if ($p['estado'] === 'INSCRIPCION_RECHAZADA'): ?>
                    <div style="margin-top: 15px; background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">
                        <p><strong>¡Tu solicitud de Inscripción ha sido Rechazada!</strong></p>
                        <p><strong>Motivo del rechazo:</strong> <?= htmlspecialchars($p['motivo_rechazo'] ?? 'No especificado') ?></p>
                        <form action="index.php?action=cerrar_rechazo_inscripcion" method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn danger">Entendido / Limpiar Panel</button>
                        </form>
                    </div>

                <?php elseif ($p['estado'] === 'RETIRO_ADICION_RECHAZADO'): ?>
                    <div style="margin-top: 15px; background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;">
                        <p><strong>¡Tu solicitud de Retiro o Adición ha sido Rechazada!</strong></p>
                        <p><strong>Motivo del rechazo:</strong> <?= htmlspecialchars($p['motivo_rechazo'] ?? 'No especificado') ?></p>
                        <form action="index.php?action=cerrar_rechazo_retiro" method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn danger">Entendido / Limpiar Panel</button>
                        </form>
                    </div>

                <?php else: ?>
                    <?php if (($p['tipo'] ?? 'INSCRIPCION') === 'RETIRO_ADICION'): ?>
                        <?php if ($p['estado'] === 'RETIRO_ADICION_SOLICITADO'): ?>
                            <p style="color: #666; margin-top: 10px;">Tu solicitud de Retiro/Adición fue enviada a Kardex. Esperando su revisión técnica...</p>
                        <?php elseif ($p['estado'] === 'RETIRO_ADICION_ACEPTADO'): ?>
                            <form action="index.php?action=guardar_retiro_adicion" method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <p style="margin-bottom: 10px; color: #28a745;"><strong>¡Solicitud Aceptada! Modifica tus materias actuales:</strong></p>
                                <div style="margin-bottom: 15px; background: #fdfdfd; padding: 10px; border: 1px solid #eee; border-radius: 5px;">
                                    <?php foreach ($materias_disponibles as $mat): 
                                        $isEnrolled = in_array($mat['nombre'], $materiasInscritasArray);
                                    ?>
                                        <label style="display: block; padding: 6px 5px; cursor: pointer;">
                                            <input type="checkbox" name="materias[]" value="<?= htmlspecialchars($mat['nombre']) ?>" <?= $isEnrolled ? 'checked' : '' ?>> 
                                            <strong><?= htmlspecialchars($mat['codigo']) ?></strong> - <?= htmlspecialchars($mat['nombre']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn success">Guardar y Consolidar Cambios</button>
                            </form>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if ($p['estado'] === 'SOLICITUD_ENVIADA'): ?>
                            <p style="color: #666; margin-top: 10px;">Tu solicitud de inscripción ha sido recibida por Kardex y está bajo evaluación técnica.</p>
                        <?php elseif ($p['estado'] === 'PAGO_SOLICITADO'): ?>
                            <form action="index.php?action=realizar_pago" method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn warning">Realizar Pago</button>
                            </form>
                        <?php elseif ($p['estado'] === 'MATERIAS_DISPONIBLES_ENVIADAS'): ?>
                            <form action="index.php?action=mandar_inscripcion" method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <p style="margin-bottom: 10px;"><strong>Selecciona tus materias:</strong></p>
                                <div style="margin-bottom: 15px;">
                                    <?php foreach ($materias_disponibles as $mat): ?>
                                        <label style="display: block; padding: 5px;">
                                            <input type="checkbox" name="materias[]" value="<?= htmlspecialchars($mat['nombre']) ?>"> 
                                            <?= htmlspecialchars($mat['codigo']) ?> - <?= htmlspecialchars($mat['nombre']) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn primary">Enviar Inscripción</button>
                            </form>
                        <?php elseif ($p['estado'] === 'INSCRIPCION_ENVIADA'): ?>
                            <div style="margin-top: 15px;">
                                <p><strong>Materias en revisión:</strong> <?= htmlspecialchars($p['materias']) ?></p>
                                <form action="index.php?action=modificar_inscripcion" method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn warning">Modificar Selección</button>
                                </form>
                            </div>
                        <?php elseif ($p['estado'] === 'COMPROBANTE_ENVIADO'): ?>
                            <div style="margin-top: 15px;">
                                <p><strong>Materias Aprobadas:</strong> <?= htmlspecialchars($p['materias']) ?></p>
                                <form action="index.php?action=finalizar" method="POST" style="margin-top: 10px;">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn success">Finalizar y Archivar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <hr style="margin: 30px 0;">
        
        <h3>Mis Materias Inscritas Actuales</h3>
        <div class="card" style="background: #edf7ed; border-left: 5px solid #28a745;">
            <?php if (empty($materiasInscritasArray)): ?>
                <p style="color: #444;">No registras ninguna materia inscrita formalmente en este momento.</p>
            <?php else: ?>
                <ul style="margin-left: 20px; line-height: 1.6;">
                    <?php foreach ($materiasInscritasArray as $m): ?>
                        <li><strong><?= htmlspecialchars($m) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>