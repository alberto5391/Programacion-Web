<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evidencias de campañas - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand">Evidencias de campañas</span>
            <?php
            $moduloRegreso = ($_SESSION['rol'] ?? '') === 'Tecnico'
                ? 'tecnico_dashboard'
                : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'dashboard');
            ?>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso) ?>">Volver al menú</a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Evidencias fotográficas</h1>
            <p class="text-muted mb-0">Cada fotografía queda asociada a una campaña y a su tipo de actividad, sin fusionar sus registros.</p>
        </header>

        <?= $mensaje ?>

        <?php if (appCan('evidencias', 'registrar')): ?>
        <section class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Registrar evidencia</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modulo" value="evidencias">
                    <input type="hidden" name="accion" value="guardar_evidencia">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_campana" class="form-label">Campaña</label>
                            <select class="form-select" id="id_campana" name="id_campana" required>
                                <option value="">Selecciona una campaña</option>
                                <?php foreach ($campanas as $campana): ?>
                                    <?php $idCampana = (int)$campana['IdCampana']; ?>
                                    <option value="<?= $idCampana ?>" <?= $idCampanaSeleccionada === $idCampana ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($campana['CodigoCampana'] . ' - ' . $campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_actividad" class="form-label">Tipo de actividad</label>
                            <select class="form-select" id="tipo_actividad" name="tipo_actividad" required>
                                <option value="">Selecciona la actividad</option>
                                <option>Inspección</option>
                                <option>Aplicación</option>
                                <option>Seguimiento</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="foto" class="form-label">Fotografía (JPEG, PNG o WebP; máximo 5 MB)</label>
                            <input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit" <?= count($campanas) === 0 ? 'disabled' : '' ?>>Guardar evidencia</button>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($evidenciaEditar && appCan('evidencias', 'modificar')): ?>
            <section class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Modificar evidencia</div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>index.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="modulo" value="evidencias">
                        <input type="hidden" name="accion" value="actualizar_evidencia">
                        <input type="hidden" name="id_evidencia" value="<?= (int)$evidenciaEditar['IdEvidencia'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="id_campana_editar" class="form-label">Campaña</label>
                            <select class="form-select" id="id_campana_editar" name="id_campana" required>
                                <?php foreach ($campanas as $campana): ?>
                                    <option value="<?= (int)$campana['IdCampana'] ?>" <?= (int)$evidenciaEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($campana['CodigoCampana'] . ' - ' . $campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label for="tipo_actividad_editar" class="form-label">Tipo de actividad</label>
                        <select class="form-select" id="tipo_actividad_editar" name="tipo_actividad" required>
                            <?php foreach (['Inspección', 'Aplicación', 'Seguimiento'] as $tipoActividad): ?>
                                <option value="<?= htmlspecialchars($tipoActividad, ENT_QUOTES, 'UTF-8') ?>" <?= $evidenciaEditar['TipoActividad'] === $tipoActividad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipoActividad, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php $rutaActual = BASE_URL . ltrim($evidenciaEditar['RutaArchivo'], '/\\'); ?>
                        <div class="mt-3 mb-2">
                            <label class="form-label" for="foto_editar">Reemplazar fotografía (opcional; JPEG, PNG o WebP; máximo 5 MB)</label>
                            <input class="form-control" type="file" id="foto_editar" name="foto" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="mb-3">
                            <span class="form-label d-block">Fotografía actual</span>
                            <img src="<?= htmlspecialchars($rutaActual, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía actual de la evidencia" style="max-width: 180px; max-height: 130px;">
                        </div>
                        <button class="btn btn-primary mt-3" type="submit">Guardar cambios</button>
                        <a class="btn btn-outline-secondary mt-3" href="<?= BASE_URL ?>index.php?modulo=evidencias&id_campana=<?= (int)$evidenciaEditar['IdCampana'] ?>">Cancelar</a>
                    </form>
                </div>
            </section>
        <?php endif; ?>

            <section class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Todas las evidencias agregadas</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Campaña</th>
                                    <th>Fotografía</th>
                                    <th>Actividad</th>
                                    <th>Fecha de registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultadoEvidencias && $resultadoEvidencias->num_rows > 0): ?>
                                    <?php while ($evidencia = $resultadoEvidencias->fetch_assoc()): ?>
                                        <?php $ruta = BASE_URL . ltrim($evidencia['RutaFoto'], '/\\'); ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($evidencia['CodigoCampana'], ENT_QUOTES, 'UTF-8') ?>
                                                -
                                                <?= htmlspecialchars($evidencia['NombreCampana'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td>
                                                <a href="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                                    <img src="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>" alt="Evidencia de campaña" style="max-width: 120px; max-height: 90px;">
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($evidencia['TipoActividad'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($evidencia['FechaRegistro'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php if (appCan('evidencias', 'modificar')): ?>
                                                    <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>index.php?modulo=evidencias&accion=editar&id=<?= (int)$evidencia['IdEvidencia'] ?>">Editar</a>
                                                <?php endif; ?>
                                                <?php if (appCan('evidencias', 'eliminar')): ?>
                                                    <form action="<?= BASE_URL ?>index.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta evidencia fotográfica?');">
                                                        <input type="hidden" name="modulo" value="evidencias">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id" value="<?= (int)$evidencia['IdEvidencia'] ?>">
                                                        <input type="hidden" name="id_campana" value="<?= (int)$evidencia['IdCampana'] ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Aún no hay evidencias registradas.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
