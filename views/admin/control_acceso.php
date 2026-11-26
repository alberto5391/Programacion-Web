<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Control de acceso a información - PLAGUE</title>

    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand, .card-header { font-weight: bold; }
        .encabezado { margin-top: 2rem; margin-bottom: 1.5rem; }
        .permission-status { white-space: nowrap; }
        footer { margin-top: 3rem; padding: 1.25rem; background-color: #343a40; color: white; text-align: center; }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php?modulo=dashboard">
            <i class="bi bi-shield-check mr-2"></i> Control de acceso
        </a>

        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>index.php?modulo=dashboard" class="btn btn-outline-light btn-sm mr-2">
                <i class="bi bi-arrow-left"></i> Inicio
            </a>
            <a href="<?= BASE_URL ?>index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                <i class="bi bi-bell"></i> Notificaciones
            </a>
        </div>
    </div>
</nav>

    <div class="container">
        <div class="encabezado">
            <h1>Control de acceso</h1>
            <p class="text-muted mb-3">Consulta de permisos efectivos por rol.</p>
            <hr>
            <div class="alert alert-info mb-0" role="status">
                <i class="bi bi-info-circle mr-1"></i>
                Los permisos son reglas fijas aplicadas por el sistema. Esta pantalla es informativa y no los modifica.
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">Permisos generales en módulos operativos</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Rol</th>
                                <th>Consultar</th>
                                <th>Registrar</th>
                                <th>Modificar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rolePermissions as $role => $allowedActions): ?>
                                <tr>
                                    <th scope="row"><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></th>
                                    <?php foreach (['consultar', 'registrar', 'modificar', 'eliminar'] as $action): ?>
                                        <?php $allowed = in_array($action, $allowedActions, true); ?>
                                        <td class="permission-status">
                                            <?php if ($allowed): ?>
                                                <span class="badge badge-success">
                                                    <i class="bi bi-check-lg" aria-hidden="true"></i> Permitido
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">
                                                    <i class="bi bi-dash-lg" aria-hidden="true"></i> No permitido
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">Excepciones y alcance</div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2"><strong>Usuarios y Control de acceso:</strong> exclusivos del Administrador.</li>
                    <li class="mb-2"><strong>Bitácora, paneles, estadísticas, gráficas, información histórica, reportes y zonas prioritarias:</strong> solo permiten consulta.</li>
                    <li><strong>Notificaciones:</strong> permanecen fuera de esta política de permisos.</li>
                </ul>
            </div>
        </div>

        <a href="<?= BASE_URL ?>index.php?modulo=dashboard" class="btn btn-outline-secondary mb-4">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </div>

    <footer>
        <p class="mb-0">PLAGUE — Control de acceso</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>