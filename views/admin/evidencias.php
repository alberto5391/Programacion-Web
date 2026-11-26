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
                                <?php if ($resultadoCampanas): ?>
                                    <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                                        <?php
                                        $idCampana = (int)$campana['IdCampana'];
                                        $etiquetaCampana = htmlspecialchars($campana['CodigoCampana'] . ' - ' . $campana['NombreCampana']);
                                        ?>
                                        <option value="<?= $idCampana ?>" <?= $idCampanaSeleccionada === $idCampana ? 'selected' : '' ?>>
                                            <?= $etiquetaCampana ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
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
                    <button class="btn btn-primary mt-3" type="submit" <?= !$resultadoCampanas || $resultadoCampanas->num_rows === 0 ? 'disabled' : '' ?>>Guardar evidencia</button>
                </form>
            </div>
        </section>

        <?php if ($idCampanaSeleccionada !== null): ?>
            <section class="card shadow-sm">
                <div class="card-header bg-white fw-semibold">Evidencias registradas en esta campaña</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Fotografía</th>
                                    <th>Actividad</th>
                                    <th>Fecha de registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultadoEvidencias && $resultadoEvidencias->num_rows > 0): ?>
                                    <?php while ($evidencia = $resultadoEvidencias->fetch_assoc()): ?>
                                        <?php $ruta = BASE_URL . ltrim($evidencia['RutaFoto'], '/\\'); ?>
                                        <tr>
                                            <td>
                                                <a href="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                                    <img src="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>" alt="Evidencia de campaña" style="max-width: 120px; max-height: 90px;">
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($evidencia['TipoActividad']) ?></td>
                                            <td><?= htmlspecialchars($evidencia['FechaRegistro']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted">Esta campaña aún no tiene evidencias.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
