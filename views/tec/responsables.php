<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/tecnico.css">
    <title>Responsables de Campaña</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Responsables de campaña</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=home" class="btn btn-outline-light btn-sm">Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm">Notificaciones</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h2 class="mb-4">Asignación de técnicos a campañas</h2>
        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (appCan('responsables', $asignacionEditar ? 'modificar' : 'registrar')): ?>
            <form method="POST" action="index.php" class="mb-5">
                <input type="hidden" name="modulo" value="responsables">
                <input type="hidden" name="accion" value="<?= $asignacionEditar ? 'modificar_responsable' : 'guardar_responsable' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($asignacionEditar): ?>
                    <input type="hidden" name="id_campana_original" value="<?= (int)$asignacionEditar['IdCampana'] ?>">
                    <input type="hidden" name="id_usuario_original" value="<?= (int)$asignacionEditar['IdUsuario'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="id_campana">Campaña</label>
                    <select class="form-select" id="id_campana" name="id_campana" required>
                        <option value="">Selecciona una campaña</option>
                        <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                            <option value="<?= (int)$campana['IdCampana'] ?>" <?= $asignacionEditar && (int)$asignacionEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'] . ' (' . $campana['NombreZona'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (($_SESSION['rol'] ?? '') === 'Tecnico'): ?>
                    <p class="form-text">Tu usuario se asignará como responsable técnico.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label" for="id_usuario">Técnico responsable</label>
                        <select class="form-select" id="id_usuario" name="id_usuario" required>
                            <option value="">Selecciona un técnico</option>
                            <?php while ($tecnico = $resultadoTecnicos->fetch_assoc()): ?>
                                <option value="<?= (int)$tecnico['IdUsuario'] ?>" <?= $asignacionEditar && (int)$asignacionEditar['IdUsuario'] === (int)$tecnico['IdUsuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['NumeroEmpleado'] . ' — ' . $tecnico['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button class="btn btn-primary" type="submit"><?= $asignacionEditar ? 'Guardar cambios' : 'Asignar técnico' ?></button>
                <?php if ($asignacionEditar): ?>
                    <a class="btn btn-outline-secondary" href="index.php?modulo=responsables">Cancelar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <h3 class="mb-3">Personal asignado</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr><th>Campaña</th><th>Zona</th><th>No. empleado</th><th>Responsable</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($resultadoAsignaciones->num_rows > 0): ?>
                        <?php while ($asignacion = $resultadoAsignaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($asignacion['CodigoCampana'] . ' — ' . $asignacion['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($asignacion['NombreZona'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($asignacion['NumeroEmpleado'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($asignacion['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (appCan('responsables', 'modificar')): ?>
                                        <a class="btn btn-sm btn-primary" href="index.php?modulo=responsables&accion=editar&id_campana=<?= (int)$asignacion['IdCampana'] ?>&id_usuario=<?= (int)$asignacion['IdUsuario'] ?>">Editar</a>
                                    <?php endif; ?>
                                    <?php if (appCan('responsables', 'eliminar')): ?>
                                        <form method="POST" action="index.php" class="d-inline" onsubmit="return confirm('¿Retirar este responsable de la campaña?');">
                                            <input type="hidden" name="modulo" value="responsables">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_campana" value="<?= (int)$asignacion['IdCampana'] ?>">
                                            <input type="hidden" name="id_usuario" value="<?= (int)$asignacion['IdUsuario'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No hay responsables asignados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php?modulo=<?= ($_SESSION['rol'] ?? '') === 'Administrador' ? 'dashboard' : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard') ?>" class="btn btn-outline-secondary mb-4">Regresar al menú</a>
    </main>
</body>
</html>
