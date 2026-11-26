<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Estado de campañas</title>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Estado de campañas</a>
            <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm">Notificaciones</a>
        </div>
    </nav>

    <main class="container">
        <h1 class="h3 mb-4">Actualización del estado de una campaña (RF27)</h1>
        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (appCan('estado_campana', 'modificar')): ?>
            <form action="index.php" method="POST" class="card card-body mb-4">
                <input type="hidden" name="modulo" value="estado_campana">
                <input type="hidden" name="accion" value="actualizar_estado_campana">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label" for="id_campana">Campaña</label>
                    <select class="form-select" id="id_campana" name="id_campana" required>
                        <option value="">Selecciona una campaña</option>
                        <?php foreach ($campanas as $campana): ?>
                            <option value="<?= (int)$campana['IdCampana'] ?>">
                                <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="estado_campana">Nuevo estado</label>
                    <select class="form-select" id="estado_campana" name="estado_campana" required>
                        <option value="">Selecciona un estado</option>
                        <?php foreach (['Programada', 'En Curso', 'Finalizada', 'Cancelada'] as $estado): ?>
                            <option value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar estado</button>
            </form>
        <?php endif; ?>

        <h2 class="h4">Campañas registradas</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr><th>Campaña</th><th>Zona</th><th>Periodo</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php if ($campanas): ?>
                        <?php foreach ($campanas as $campana): ?>
                            <tr>
                                <td><?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($campana['NombreZona'] . ' (' . $campana['CodigoZona'] . ')', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($campana['FechaInicio'] . ' — ' . ($campana['FechaFin'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($campana['Estado'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No hay campañas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php?modulo=<?= ($_SESSION['rol'] ?? '') === 'Administrador' ? 'dashboard' : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard') ?>" class="btn btn-outline-secondary mb-4">Regresar al menú</a>
    </main>
</body>
</html>
