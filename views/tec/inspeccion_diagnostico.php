<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/tecnico.css">
    <title>Inspección y Diagnóstico</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Inspección y Diagnóstico</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=home" class="btn btn-outline-light btn-sm">Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm">Notificaciones</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h2 class="mb-4">Inspecciones y diagnósticos en campo</h2>
        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (appCan('inspeccion_diagnostico', $accionEditar ? 'modificar' : 'registrar')): ?>
            <?php
            $tipoActual = $accionEditar['TipoAccion'] ?? 'Inspección';
            $detalleActual = $accionEditar['DetallesResultados'] ?? '';
            $ubicacionActual = $tipoActual === 'Inspección'
                ? (strpos($detalleActual, 'Ubicación: ') === 0 ? substr($detalleActual, 11) : $detalleActual)
                : '';
            $caracteristicasActuales = $tipoActual === 'Diagnóstico'
                ? (preg_replace('/^Nivel de infestación: [^\r\n]*\R?/', '', $detalleActual) ?? $detalleActual)
                : '';
            ?>
            <form method="POST" action="index.php" class="mb-5" enctype="multipart/form-data">
                <input type="hidden" name="modulo" value="inspeccion_diagnostico">
                <input type="hidden" name="accion" value="guardar_accion">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($accionEditar): ?>
                    <input type="hidden" name="id_accion" value="<?= (int)$accionEditar['IdAccion'] ?>">
                    <input type="hidden" name="tipo_accion" value="<?= htmlspecialchars($tipoActual, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="tipo_accion">Tipo de registro</label>
                    <select class="form-select" id="tipo_accion" name="tipo_accion" required <?= $accionEditar ? 'disabled' : '' ?>>
                        <?php foreach (['Inspección', 'Diagnóstico'] as $tipo): ?>
                            <option value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>" <?= $tipoActual === $tipo ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="id_campana">Campaña</label>
                    <select class="form-select" id="id_campana" name="id_campana" required>
                        <option value="">Selecciona una campaña</option>
                        <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                            <option value="<?= (int)$campana['IdCampana'] ?>" <?= $accionEditar && (int)$accionEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'] . ' (' . $campana['NombreZona'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (($_SESSION['rol'] ?? '') === 'Tecnico'): ?>
                    <p class="form-text">El registro se asociará automáticamente con tu perfil técnico.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label" for="id_tecnico">Técnico responsable</label>
                        <select class="form-select" id="id_tecnico" name="id_tecnico" required>
                            <option value="">Selecciona un técnico</option>
                            <?php while ($tecnico = $resultadoTecnicos->fetch_assoc()): ?>
                                <option value="<?= (int)$tecnico['IdTecnico'] ?>" <?= $accionEditar && (int)$accionEditar['IdTecnico'] === (int)$tecnico['IdTecnico'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['NumeroEmpleado'] . ' — ' . $tecnico['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="fecha_hora">Fecha y hora</label>
                    <input class="form-control" type="datetime-local" id="fecha_hora" name="fecha_hora"
                           value="<?= $accionEditar ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($accionEditar['FechaHora'])), ENT_QUOTES, 'UTF-8') : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="ubicacion">Ubicación observada (para inspección)</label>
                    <input class="form-control" type="text" id="ubicacion" name="ubicacion" value="<?= htmlspecialchars($ubicacionActual, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="id_plaga">Plaga identificada (para diagnóstico)</label>
                    <select class="form-select" id="id_plaga" name="id_plaga">
                        <option value="">Selecciona una plaga</option>
                        <?php while ($plaga = $resultadoPlagas->fetch_assoc()): ?>
                            <option value="<?= (int)$plaga['IdPlaga'] ?>" <?= $accionEditar && isset($accionEditar['IdPlaga']) && (int)$accionEditar['IdPlaga'] === (int)$plaga['IdPlaga'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plaga['NombreComun'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nivel_infestacion">Nivel de infestación</label>
                    <select class="form-select" id="nivel_infestacion" name="nivel_infestacion">
                        <option value="">Selecciona un nivel</option>
                        <?php foreach (['Bajo', 'Medio', 'Alto', 'Crítico'] as $nivel): ?>
                            <option value="<?= $nivel ?>" <?= $accionEditar && ($accionEditar['NivelInfestacion'] ?? '') === $nivel ? 'selected' : '' ?>><?= $nivel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="caracteristicas">Características relevantes (para diagnóstico)</label>
                    <textarea class="form-control" id="caracteristicas" name="caracteristicas"><?= htmlspecialchars($caracteristicasActuales, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones"><?= $accionEditar ? htmlspecialchars($accionEditar['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="foto">Fotografía de la inspección o plaga (opcional; JPEG, PNG o WebP; máximo 5 MB)</label>
                    <?php if ($accionEditar && !empty($accionEditar['RutaFoto'])): ?>
                        <?php $rutaFotoActual = BASE_URL . ltrim($accionEditar['RutaFoto'], '/\\'); ?>
                        <div class="form-text mb-2">
                            Fotografía actual:<br>
                            <a href="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                <img src="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía actual del registro" class="img-thumbnail my-1" style="max-width: 160px; max-height: 120px;">
                            </a><br>
                            Si eliges otro archivo, reemplazará a esta fotografía.
                        </div>
                    <?php endif; ?>
                    <input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                </div>
                <button class="btn btn-primary" type="submit"><?= $accionEditar ? 'Guardar cambios' : 'Registrar' ?></button>
                <?php if ($accionEditar): ?>
                    <a class="btn btn-outline-secondary" href="index.php?modulo=inspeccion_diagnostico">Cancelar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <h3 class="mb-3">Registros existentes</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr><th>Tipo</th><th>Campaña</th><th>Zona</th><th>Fecha</th><th>Técnico</th><th>Detalle</th><th>Fotografía</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($resultadoAcciones->num_rows > 0): ?>
                        <?php while ($accionFila = $resultadoAcciones->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($accionFila['TipoAccion'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($accionFila['CodigoCampana'] . ' — ' . $accionFila['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($accionFila['NombreZona'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($accionFila['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($accionFila['NumeroEmpleado'] . ' — ' . $accionFila['Tecnico'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($accionFila['DetallesResultados'] . ($accionFila['Plaga'] ? "\nPlaga: " . $accionFila['Plaga'] . ' — ' . $accionFila['NivelInfestacion'] : '') . ($accionFila['Observaciones'] ? "\n" . $accionFila['Observaciones'] : ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($accionFila['RutaFoto'])): ?>
                                        <?php $rutaFoto = BASE_URL . ltrim($accionFila['RutaFoto'], '/\\'); ?>
                                        <a href="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                            <img src="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía del registro" class="img-thumbnail" style="max-width: 120px; max-height: 90px;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin fotografía</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (appCan('inspeccion_diagnostico', 'modificar')): ?>
                                        <a class="btn btn-sm btn-primary" href="index.php?modulo=inspeccion_diagnostico&accion=editar&id=<?= (int)$accionFila['IdAccion'] ?>">Editar</a>
                                    <?php endif; ?>
                                    <?php if (appCan('inspeccion_diagnostico', 'eliminar')): ?>
                                        <form method="POST" action="index.php" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                            <input type="hidden" name="modulo" value="inspeccion_diagnostico">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="tipo_accion" value="<?= htmlspecialchars($accionFila['TipoAccion'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int)$accionFila['IdAccion'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">No hay inspecciones ni diagnósticos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php?modulo=<?= ($_SESSION['rol'] ?? '') === 'Administrador' ? 'dashboard' : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard') ?>" class="btn btn-outline-secondary mb-4">Regresar al menú</a>
    </main>
</body>
</html>
