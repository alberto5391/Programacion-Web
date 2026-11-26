<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Estado de campañas - PLAGUE</title>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0"><i class="bi bi-kanban me-2"></i>Estado de campañas</span>
            <a href="<?= BASE_URL ?>index.php?modulo=graficas" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Volver a gráficas
            </a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3 mb-1">Gestión de estados</h1>
            <p class="text-muted mb-0">Actualiza el avance de las campañas y consulta el historial de cambios.</p>
        </header>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($tipoMensaje, ENT_QUOTES, 'UTF-8') ?>" role="alert">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="row g-3 mb-4" aria-label="Resumen de campañas">
            <?php
            $coloresEstado = [
                'Programada' => 'text-secondary',
                'En Curso' => 'text-primary',
                'Finalizada' => 'text-success',
                'Cancelada' => 'text-danger',
            ];
            ?>
            <?php foreach ($estadosPermitidos as $estado): ?>
                <div class="col-sm-6 col-xl-3">
                    <article class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted"><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="fs-3 fw-bold <?= $coloresEstado[$estado] ?>"><?= number_format($conteosEstado[$estado]) ?></div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="estado_campana">
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label" for="municipio">Municipio</label>
                        <select class="form-select" id="municipio" name="municipio">
                            <option value="">Todos los municipios</option>
                            <?php foreach ($municipios as $opcionMunicipio): ?>
                                <option value="<?= htmlspecialchars($opcionMunicipio, ENT_QUOTES, 'UTF-8') ?>" <?= $municipio === $opcionMunicipio ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcionMunicipio, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estadosPermitidos as $estado): ?>
                                <option value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>" <?= $estadoFiltro === $estado ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 col-xl-4">
                        <label class="form-label" for="buscar">Campaña</label>
                        <input class="form-control" type="search" id="buscar" name="buscar" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>" placeholder="Código o nombre">
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                    </div>
                </form>
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=estado_campana">Quitar filtros</a>
                <p class="small text-muted mt-2 mb-0">
                    Los contadores respetan municipio y búsqueda; la tabla también aplica el filtro de estado.
                </p>
            </div>
        </section>

        <div class="alert alert-light border" role="note">
            Transiciones permitidas: Programada → En Curso o Cancelada; En Curso → Finalizada o Cancelada.
            Fecha fin es aproximada y no cambia al actualizar el estado.
        </div>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Campañas</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover bg-white align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Campaña</th>
                                <th scope="col">Zona</th>
                                <th scope="col">Periodo estimado</th>
                                <th scope="col">Estado actual</th>
                                <th scope="col">Actualizar</th>
                                <th scope="col">Historial</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($campanas): ?>
                                <?php foreach ($campanas as $campana): ?>
                                    <?php
                                    $idCampanaActual = (int) $campana['IdCampana'];
                                    $estadoActual = $campana['Estado'];
                                    $estadosSiguientes = $transicionesPermitidas[$estadoActual] ?? [];
                                    $claseEstado = [
                                        'Programada' => 'secondary',
                                        'En Curso' => 'primary',
                                        'Finalizada' => 'success',
                                        'Cancelada' => 'danger',
                                    ][$estadoActual] ?? 'secondary';
                                    $historialCampana = $historialPorCampana[$idCampanaActual] ?? [];
                                    ?>
                                    <tr>
                                        <th scope="row">
                                            <div><?= htmlspecialchars($campana['CodigoCampana'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </th>
                                        <td>
                                            <div><?= htmlspecialchars($campana['NombreZona'] . ' (' . $campana['CodigoZona'] . ')', ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($campana['Municipio'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($campana['FechaInicio'], ENT_QUOTES, 'UTF-8') ?>
                                            —
                                            <?= htmlspecialchars($campana['FechaFin'] ?? 'Sin definir', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-<?= $claseEstado ?>"><?= htmlspecialchars($estadoActual, ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td>
                                            <?php if (appCan('estado_campana', 'modificar') && $estadosSiguientes): ?>
                                                <form action="<?= BASE_URL ?>index.php" method="POST" class="d-flex gap-2">
                                                    <input type="hidden" name="modulo" value="estado_campana">
                                                    <input type="hidden" name="accion" value="actualizar_estado_campana">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="id_campana" value="<?= $idCampanaActual ?>">
                                                    <input type="hidden" name="municipio" value="<?= htmlspecialchars($municipio, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="estado" value="<?= htmlspecialchars($estadoFiltro, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="buscar" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
                                                    <select class="form-select form-select-sm" name="estado_campana" aria-label="Nuevo estado para <?= htmlspecialchars($campana['CodigoCampana'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                        <option value="">Nuevo estado</option>
                                                        <?php foreach ($estadosSiguientes as $estadoSiguiente): ?>
                                                            <option value="<?= htmlspecialchars($estadoSiguiente, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($estadoSiguiente, ENT_QUOTES, 'UTF-8') ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-sm btn-outline-primary" type="submit" aria-label="Actualizar <?= htmlspecialchars($campana['CodigoCampana'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php elseif (!$estadosSiguientes): ?>
                                                <span class="small text-muted">Estado final</span>
                                            <?php else: ?>
                                                <span class="small text-muted">Sin permiso</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <details>
                                                <summary>Ver (<?= count($historialCampana) ?>)</summary>
                                                <?php if ($historialCampana): ?>
                                                    <ul class="small mt-2 mb-0 ps-3">
                                                        <?php foreach ($historialCampana as $cambio): ?>
                                                            <li class="mb-2">
                                                                <?= htmlspecialchars($cambio['EstadoAnterior'] . ' → ' . $cambio['EstadoNuevo'], ENT_QUOTES, 'UTF-8') ?><br>
                                                                <span class="text-muted">
                                                                    <?= htmlspecialchars($cambio['FechaCambio'], ENT_QUOTES, 'UTF-8') ?>
                                                                    · <?= htmlspecialchars($cambio['Usuario'], ENT_QUOTES, 'UTF-8') ?>
                                                                </span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="small text-muted mt-2 mb-0">Sin cambios registrados.</p>
                                                <?php endif; ?>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay campañas que coincidan con los filtros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <a href="<?= BASE_URL ?>index.php?modulo=<?= ($_SESSION['rol'] ?? '') === 'Administrador' ? 'dashboard' : (($_SESSION['rol'] ?? '') === 'Supervisor' ? 'super_dashboard' : 'tecnico_dashboard') ?>" class="btn btn-outline-secondary mb-4">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </main>
</body>
</html>
