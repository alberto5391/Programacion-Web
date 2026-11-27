<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/tecnico.css">
    <title>Aplicación de Tratamientos</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Aplicación de Tratamientos</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=home" class="btn btn-outline-light btn-sm">Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm">Notificaciones</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h2 class="mb-4">Aplicaciones registradas</h2>
        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (appCan('tratamientos', $aplicacionEditar ? 'modificar' : 'registrar')): ?>
            <form method="POST" action="index.php" class="mb-5" enctype="multipart/form-data">
                <input type="hidden" name="modulo" value="tratamientos">
                <input type="hidden" name="accion" value="guardar_aplicacion">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($aplicacionEditar): ?>
                    <input type="hidden" name="id_accion" value="<?= (int)$aplicacionEditar['IdAccion'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="id_campana">Campaña</label>
                    <select class="form-select" id="id_campana" name="id_campana" required>
                        <option value="">Selecciona una campaña</option>
                        <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                            <option value="<?= (int)$campana['IdCampana'] ?>" <?= $aplicacionEditar && (int)$aplicacionEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'] . ' (' . $campana['NombreZona'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if (($_SESSION['rol'] ?? '') === 'Tecnico'): ?>
                    <p class="form-text">La aplicación se asociará automáticamente con tu perfil técnico.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label" for="id_tecnico">Técnico responsable</label>
                        <select class="form-select" id="id_tecnico" name="id_tecnico" required>
                            <option value="">Selecciona un técnico</option>
                            <?php while ($tecnico = $resultadoTecnicos->fetch_assoc()): ?>
                                <option value="<?= (int)$tecnico['IdTecnico'] ?>" <?= $aplicacionEditar && (int)$aplicacionEditar['IdTecnico'] === (int)$tecnico['IdTecnico'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tecnico['NumeroEmpleado'] . ' — ' . $tecnico['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="fecha_hora">Fecha y hora</label>
                    <input class="form-control" type="datetime-local" id="fecha_hora" name="fecha_hora"
                           value="<?= $aplicacionEditar ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($aplicacionEditar['FechaHora'])), ENT_QUOTES, 'UTF-8') : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="id_producto">Producto utilizado</label>
                    <select class="form-select" id="id_producto" name="id_producto" required>
                        <option value="">Selecciona un producto</option>
                        <?php while ($producto = $resultadoProductos->fetch_assoc()): ?>
                            <option value="<?= (int)$producto['IdProducto'] ?>" <?= $aplicacionEditar && (int)$aplicacionEditar['IdProducto'] === (int)$producto['IdProducto'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($producto['NombreComercial'] . ' (' . $producto['UnidadMedida'] . ')', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="cantidad">Cantidad aplicada</label>
                    <input class="form-control" type="number" id="cantidad" name="cantidad" min="0.01" step="0.01" required
                           value="<?= $aplicacionEditar ? htmlspecialchars((string)$aplicacionEditar['CantidadAplicada'], ENT_QUOTES, 'UTF-8') : '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones"><?= $aplicacionEditar ? htmlspecialchars($aplicacionEditar['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="foto">Fotografía del tratamiento: producto aplicado o área tratada (opcional; JPEG, PNG o WebP; máximo 5 MB)</label>
                    <?php if ($aplicacionEditar && !empty($aplicacionEditar['RutaFoto'])): ?>
                        <?php $rutaFotoActual = BASE_URL . ltrim($aplicacionEditar['RutaFoto'], '/\\'); ?>
                        <div class="form-text mb-2">
                            Fotografía actual:<br>
                            <a href="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                <img src="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía actual del tratamiento" class="img-thumbnail my-1" style="max-width: 160px; max-height: 120px;">
                            </a><br>
                            Si eliges otro archivo, reemplazará a esta fotografía.
                        </div>
                    <?php endif; ?>
                    <input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                </div>
                <button class="btn btn-primary" type="submit"><?= $aplicacionEditar ? 'Guardar cambios' : 'Registrar aplicación' ?></button>
                <?php if ($aplicacionEditar): ?>
                    <a class="btn btn-outline-secondary" href="index.php?modulo=tratamientos">Cancelar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr><th>Campaña</th><th>Zona</th><th>Fecha</th><th>Técnico</th><th>Producto</th><th>Cantidad</th><th>Observaciones</th><th>Fotografía</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($resultadoAplicaciones->num_rows > 0): ?>
                        <?php while ($aplicacion = $resultadoAplicaciones->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($aplicacion['CodigoCampana'] . ' — ' . $aplicacion['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($aplicacion['NombreZona'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($aplicacion['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($aplicacion['NumeroEmpleado'] . ' — ' . $aplicacion['Tecnico'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($aplicacion['Producto'] ?? 'Sin producto', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($aplicacion['CantidadAplicada'] ?? ''), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($aplicacion['UnidadMedida'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($aplicacion['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($aplicacion['RutaFoto'])): ?>
                                        <?php $rutaFoto = BASE_URL . ltrim($aplicacion['RutaFoto'], '/\\'); ?>
                                        <a href="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                            <img src="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía del tratamiento" class="img-thumbnail" style="max-width: 120px; max-height: 90px;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin fotografía</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (appCan('tratamientos', 'modificar')): ?>
                                        <a class="btn btn-sm btn-primary" href="index.php?modulo=tratamientos&accion=editar&id=<?= (int)$aplicacion['IdAccion'] ?>">Editar</a>
                                    <?php endif; ?>
                                    <?php if (appCan('tratamientos', 'eliminar')): ?>
                                        <form method="POST" action="index.php" class="d-inline" onsubmit="return confirm('¿Eliminar esta aplicación?');">
                                            <input type="hidden" name="modulo" value="tratamientos">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?= (int)$aplicacion['IdAccion'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9">No hay aplicaciones registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php?modulo=<?= ($_SESSION['rol'] ?? '') === 'Administrador' ? 'dashboard' : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard') ?>" class="btn btn-outline-secondary mb-4">Regresar al menú</a>
    </main>
</body>
</html>
