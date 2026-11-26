<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas por zona - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/supervisor.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f7fa; }
        .panel, .stat-card { border: 0; box-shadow: 0 2px 10px rgba(24, 39, 75, .07); }
        .stat-value { font-size: 1.7rem; font-weight: 700; }
        .chart-wrap { height: 440px; position: relative; }
        @media (max-width: 575px) {
            .chart-wrap { height: 360px; }
            .stat-value { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <?php
    $rolActual = $_SESSION['rol'] ?? '';
    $moduloRegreso = $rolActual === 'Supervisor'
        ? 'super_dashboard'
        : ($rolActual === 'Tecnico' ? 'tecnico_dashboard' : 'dashboard');
    ?>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0"><i class="bi bi-geo-alt me-2"></i>Estadísticas por zona</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=graficas">
                <i class="bi bi-arrow-left"></i> Volver a gráficas
            </a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Actividad por zona</h1>
            <p class="text-muted mb-0">Comparativo de identificaciones, acciones e incidencias en zonas activas.</p>
        </header>

        <?php foreach ($mensajes as $mensaje): ?>
            <div class="alert alert-warning" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="graficas_zonas">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="desde">Desde</label>
                        <input class="form-control" type="date" id="desde" name="desde" value="<?= htmlspecialchars($fechaInicio, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="hasta">Hasta</label>
                        <input class="form-control" type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($fechaFin, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-8 col-lg-3">
                        <label class="form-label" for="municipio">Municipio</label>
                        <select class="form-select" id="municipio" name="municipio">
                            <option value="">Todos los municipios</option>
                            <?php foreach ($municipios as $opcionMunicipio): ?>
                                <option value="<?= htmlspecialchars($opcionMunicipio['Municipio'], ENT_QUOTES, 'UTF-8') ?>" <?= $municipio === $opcionMunicipio['Municipio'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcionMunicipio['Municipio'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-8 col-lg-2">
                        <label class="form-label" for="metrica">Comparar por</label>
                        <select class="form-select" id="metrica" name="metrica">
                            <?php foreach ($metricas as $claveMetrica => $opcionMetrica): ?>
                                <option value="<?= htmlspecialchars($claveMetrica, ENT_QUOTES, 'UTF-8') ?>" <?= $metrica === $claveMetrica ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcionMetrica['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 col-lg-1">
                        <button class="btn btn-primary w-100" type="submit">Aplicar</button>
                    </div>
                </form>
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=graficas_zonas">Restablecer filtros</a>
                <p class="small text-muted mt-3 mb-0">
                    Identificaciones y acciones se filtran por fecha de acción; las incidencias, por fecha de registro.
                </p>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Resumen por zona">
            <div class="col-sm-6 col-xl-3">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Identificaciones</div>
                        <div class="stat-value"><?= number_format($totales['TotalIdentificaciones']) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Acciones</div>
                        <div class="stat-value"><?= number_format($totales['TotalAcciones']) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Incidencias</div>
                        <div class="stat-value"><?= number_format($totales['TotalIncidencias']) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-3">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Zonas con actividad</div>
                        <div class="stat-value"><?= number_format($zonasConActividad) ?></div>
                        <div class="small text-muted">de <?= number_format(count($zonas)) ?> zonas activas</div>
                    </div>
                </article>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">
                Top 10 zonas: <?= htmlspecialchars($etiquetaMetrica, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="card-body">
                <?php if ($zonasDestacadas): ?>
                    <div class="chart-wrap">
                        <canvas id="graficaZonas" role="img" aria-label="Comparativo de zonas por métrica"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay registros para esta métrica en el periodo y municipio seleccionados.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Detalle de zonas activas</div>
            <div class="card-body">
                <?php if ($zonas): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Zona</th>
                                    <th scope="col">Municipio</th>
                                    <th scope="col">Identificaciones</th>
                                    <th scope="col">Acciones</th>
                                    <th scope="col">Incidencias</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($zonas as $zona): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($zona['NombreZona'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= htmlspecialchars($zona['Municipio'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= number_format((int) $zona['TotalIdentificaciones']) ?></td>
                                        <td><?= number_format((int) $zona['TotalAcciones']) ?></td>
                                        <td><?= number_format((int) $zona['TotalIncidencias']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay zonas activas para mostrar.</p>
                <?php endif; ?>
            </div>
        </section>

        <a class="btn btn-outline-secondary mb-4" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </main>

    <?php if ($zonasDestacadas): ?>
        <script>
            new Chart(document.getElementById('graficaZonas'), {
                type: 'bar',
                data: {
                    labels: <?= $labelsJson ?>,
                    datasets: [{
                        label: <?= json_encode($etiquetaMetrica, $opcionesJson) ?>,
                        data: <?= $datosJson ?>,
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: {
                                display: true,
                                text: <?= json_encode($etiquetaMetrica, $opcionesJson) ?>
                            }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
