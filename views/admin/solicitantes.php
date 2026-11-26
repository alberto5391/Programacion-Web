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
            <a class="navbar-brand" href="<?= BASE_URL ?>index.php?modulo=dashboard">Solicitantes y orígenes</a>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=campanas">Ir a campañas</a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Catálogo de solicitantes y orígenes</h1>
            <p class="text-muted mb-0">Registra las fuentes de solicitud y asígnalas a una o varias campañas desde el formulario de campaña.</p>
        </header>

        <?= $mensaje ?>

        <section class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Registrar solicitante u origen</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="POST">
                    <input type="hidden" name="modulo" value="solicitantes">
                    <input type="hidden" name="accion" value="registrar_solicitante">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tipo_origen" class="form-label">Tipo de origen</label>
                            <select class="form-select" id="tipo_origen" name="tipo_origen" required>
                                <option value="">Selecciona un tipo</option>
                                <option>Reporte ciudadano</option>
                                <option>Cliente privado</option>
                                <option>Programa de salud público</option>
                                <option>Institución</option>
                                <option>Solicitud interna</option>
                                <option>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre_solicitante" class="form-label">Nombre o razón social</label>
                            <input class="form-control" type="text" id="nombre_solicitante" name="nombre_solicitante" maxlength="150" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_solicitante" class="form-label">Teléfono de contacto</label>
                            <input class="form-control" type="tel" id="telefono_solicitante" name="telefono_solicitante" maxlength="20" required>
                        </div>
                        <div class="col-md-6">
                            <label for="estado_solicitud" class="form-label">Estado</label>
                            <select class="form-select" id="estado_solicitud" name="estado_solicitud" required>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="descripcion_solicitud" class="form-label">Descripción de la solicitud</label>
                            <textarea class="form-control" id="descripcion_solicitud" name="descripcion_solicitud" rows="3" required></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" type="submit">Guardar solicitante</button>
                </form>
            </div>
        </section>

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
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Aún no hay solicitantes registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
