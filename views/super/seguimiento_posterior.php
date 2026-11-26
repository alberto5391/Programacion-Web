<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/supervisor.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento</title>
</head>
<body>

    <!-- Barra Superior de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-check me-2"></i>Registro de seguimiento</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=home" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Registro de seguimiento posterior (RF20)</h2>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (appCan('seguimiento_posterior', $seguimientoEditar ? 'modificar' : 'registrar')): ?>
            <form method="POST" action="index.php" class="mb-5">
                <input type="hidden" name="modulo" value="seguimiento_posterior">
                <input type="hidden" name="accion" value="guardar_seguimiento">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($seguimientoEditar): ?>
                    <input type="hidden" name="id_accion" value="<?= (int)$seguimientoEditar['IdAccion'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_campana" class="form-label">Campaña:</label>
                    <select class="form-select" id="id_campana" name="id_campana" required>
                        <option value="">Selecciona una campaña</option>
                        <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                            <option value="<?= (int)$campana['IdCampana'] ?>" <?= $seguimientoEditar && (int)$seguimientoEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'] . ' (' . $campana['NombreZona'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (($_SESSION['rol'] ?? '') === 'Tecnico'): ?>
                    <p class="form-text">El seguimiento se registrará con tu perfil técnico.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <label for="id_tecnico" class="form-label">Técnico responsable:</label>
                        <select class="form-select" id="id_tecnico" name="id_tecnico" required>
                            <option value="">Selecciona un técnico</option>
                            <?php while ($tecnico = $resultadoTecnicos->fetch_assoc()): ?>
                                <option value="<?= (int)$tecnico['IdTecnico'] ?>" <?= $seguimientoEditar && (int)$seguimientoEditar['IdTecnico'] === (int)$tecnico['IdTecnico'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['NumeroEmpleado'] . ' — ' . $tecnico['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="fecha_seguimiento" class="form-label">Fecha y hora:</label>
                    <input type="datetime-local" class="form-control" id="fecha_seguimiento" name="fecha_seguimiento"
                           value="<?= $seguimientoEditar ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($seguimientoEditar['FechaHora'])), ENT_QUOTES, 'UTF-8') : '' ?>" required>
                </div>

                <div class="mb-3">
                    <label for="presencia_plaga" class="form-label">Presencia de la plaga:</label>
                    <select class="form-select" id="presencia_plaga" name="presencia_plaga" required>
                        <option value="">Selecciona una opción</option>
                        <?php foreach (['Ausente', 'Presente', 'Reincidente'] as $presencia): ?>
                            <option value="<?= $presencia ?>" <?= $seguimientoEditar && $seguimientoEditar['Presencia'] === $presencia ? 'selected' : '' ?>>
                                <?= $presencia ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="resultados_seg" class="form-label">Resultados obtenidos:</label>
                    <textarea class="form-control" id="resultados_seg" name="resultados_seg" required><?= $seguimientoEditar ? htmlspecialchars($seguimientoEditar['Resultado'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="incidencias" class="form-label">Incidencias detectadas:</label>
                    <textarea class="form-control" id="incidencias" name="incidencias"><?= $seguimientoEditar ? htmlspecialchars($seguimientoEditar['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><?= $seguimientoEditar ? 'Guardar cambios' : 'Guardar seguimiento' ?></button>
                <?php if ($seguimientoEditar): ?>
                    <a href="index.php?modulo=seguimiento_posterior" class="btn btn-outline-secondary">Cancelar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <h3 class="mb-3">Seguimientos registrados</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Campaña</th>
                        <th>Zona</th>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th>Resultados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultadoSeguimientos->num_rows > 0): ?>
                        <?php while ($seguimiento = $resultadoSeguimientos->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($seguimiento['CodigoCampana'] . ' — ' . $seguimiento['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($seguimiento['NombreZona'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($seguimiento['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($seguimiento['NumeroEmpleado'] . ' — ' . $seguimiento['Tecnico'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= nl2br(htmlspecialchars($seguimiento['DetallesResultados'] . ($seguimiento['Observaciones'] ? "\nIncidencias: " . $seguimiento['Observaciones'] : ''), ENT_QUOTES, 'UTF-8')) ?></td>
                                <td>
                                    <?php if (appCan('seguimiento_posterior', 'modificar')): ?>
                                        <a class="btn btn-sm btn-primary" href="index.php?modulo=seguimiento_posterior&accion=editar&id=<?= (int)$seguimiento['IdAccion'] ?>">Editar</a>
                                    <?php endif; ?>
                                    <?php if (appCan('seguimiento_posterior', 'eliminar')): ?>
                                        <form method="POST" action="index.php" class="d-inline" onsubmit="return confirm('¿Eliminar este seguimiento?');">
                                            <input type="hidden" name="modulo" value="seguimiento_posterior">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?= (int)$seguimiento['IdAccion'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">Todavía no hay seguimientos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>