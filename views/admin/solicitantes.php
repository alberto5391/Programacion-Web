<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitantes y orígenes - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <?php
            $moduloRegreso = ($_SESSION['rol'] ?? '') === 'Administrador'
                ? 'dashboard'
                : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard');
            ?>
            <a class="navbar-brand" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso) ?>">Solicitantes y orígenes</a>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=campanas">Ir a campañas</a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Catálogo de solicitantes y orígenes</h1>
            <p class="text-muted mb-0">Registra las fuentes de solicitud y asígnalas a una o varias campañas desde el formulario de campaña.</p>
        </header>

        <?= $mensaje ?>

        <?php if (appCan('solicitantes', $solicitanteEditar ? 'modificar' : 'registrar')): ?>
        <section class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><?= $solicitanteEditar ? 'Modificar solicitante u origen' : 'Registrar solicitante u origen' ?></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="POST">
                    <input type="hidden" name="modulo" value="solicitantes">
                    <input type="hidden" name="accion" value="guardar_solicitante">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($solicitanteEditar): ?>
                        <input type="hidden" name="id_solicitante" value="<?= (int)$solicitanteEditar['IdSolicitante'] ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tipo_origen" class="form-label">Tipo de origen</label>
                            <select class="form-select" id="tipo_origen" name="tipo_origen" required>
                                <option value="">Selecciona un tipo</option>
                                <?php foreach (['Reporte ciudadano', 'Cliente privado', 'Programa de salud público', 'Institución', 'Solicitud interna', 'Otro'] as $tipo): ?>
                                    <option value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>" <?= $solicitanteEditar && $solicitanteEditar['TipoOrigen'] === $tipo ? 'selected' : '' ?>><?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre_solicitante" class="form-label">Nombre o razón social</label>
                            <input class="form-control" type="text" id="nombre_solicitante" name="nombre_solicitante" maxlength="150" value="<?= $solicitanteEditar ? htmlspecialchars($solicitanteEditar['NombreSolicitante'], ENT_QUOTES, 'UTF-8') : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_solicitante" class="form-label">Teléfono de contacto</label>
                            <input class="form-control" type="tel" id="telefono_solicitante" name="telefono_solicitante" maxlength="20" value="<?= $solicitanteEditar ? htmlspecialchars($solicitanteEditar['TelefonoSolicitante'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="estado_solicitud" class="form-label">Estado</label>
                            <select class="form-select" id="estado_solicitud" name="estado_solicitud" required>
                                <option value="Activo" <?= !$solicitanteEditar || $solicitanteEditar['EstadoSolicitud'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= $solicitanteEditar && $solicitanteEditar['EstadoSolicitud'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_solicitud" class="form-label">Descripción de la solicitud</label>
                            <textarea class="form-control" id="descripcion_solicitud" name="descripcion_solicitud" rows="3" required><?= $solicitanteEditar ? htmlspecialchars($solicitanteEditar['DescripcionSolicitud'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit"><?= $solicitanteEditar ? 'Guardar cambios' : 'Guardar solicitante' ?></button>
                    <?php if ($solicitanteEditar): ?>
                        <a class="btn btn-outline-secondary mt-3" href="<?= BASE_URL ?>index.php?modulo=solicitantes">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <section class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Solicitantes registrados</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Solicitante</th>
                                <th>Contacto</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultadoSolicitantes && $resultadoSolicitantes->num_rows > 0): ?>
                                <?php while ($solicitante = $resultadoSolicitantes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= (int)$solicitante['IdSolicitante'] ?></td>
                                        <td><?= htmlspecialchars($solicitante['TipoOrigen']) ?></td>
                                        <td><?= htmlspecialchars($solicitante['Nombre']) ?></td>
                                        <td><?= htmlspecialchars($solicitante['Contacto'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($solicitante['Descripcion']) ?></td>
                                        <td><?= htmlspecialchars($solicitante['Estado']) ?></td>
                                        <td>
                                            <?php if (appCan('solicitantes', 'modificar')): ?>
                                                <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>index.php?modulo=solicitantes&accion=editar&id=<?= (int)$solicitante['IdSolicitante'] ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (appCan('solicitantes', 'eliminar')): ?>
                                                <form method="POST" action="<?= BASE_URL ?>index.php" class="d-inline" onsubmit="return confirm('¿Eliminar este solicitante? También se quitarán sus asociaciones con campañas.');">
                                                    <input type="hidden" name="modulo" value="solicitantes">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?= (int)$solicitante['IdSolicitante'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted">Aún no hay solicitantes registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
