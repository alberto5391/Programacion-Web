<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plagas identificadas - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/supervisor.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f7fa; }
        .panel, .stat-card { border: 0; box-shadow: 0 2px 10px rgba(24, 39, 75, .07); }
        .stat-value { font-size: 1.7rem; font-weight: 700; }
        .chart-wrap { height: 380px; position: relative; }
        @media (max-width: 575px) {
            .chart-wrap { height: 320px; }
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
            <span class="navbar-brand mb-0"><i class="bi bi-bug me-2"></i>Plagas identificadas</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=graficas">
                <i class="bi bi-arrow-left"></i> Volver a gráficas
            </a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Análisis de plagas identificadas</h1>
            <p class="text-muted mb-0">Conteo de registros de identificación, no de ejemplares individuales.</p>
        </header>

        <?php foreach ($mensajes as $mensaje): ?>
            <div class="alert alert-warning" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="graficas_plagas">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="desde">Desde</label>
                        <input class="form-control" type="date" id="desde" name="desde" value="<?= htmlspecialchars($fechaInicio, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="hasta">Hasta</label>
                        <input class="form-control" type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($fechaFin, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-8 col-lg-4">
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
                    <div class="col-sm-4 col-lg-2">
                        <button class="btn btn-primary w-100" type="submit">Aplicar</button>
                    </div>
                </form>
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=graficas_plagas">Restablecer filtros</a>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Resumen de plagas">
            <div class="col-md-4">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Identificaciones</div>
                        <div class="stat-value"><?= number_format($totalIdentificaciones) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Especies identificadas</div>
                        <div class="stat-value"><?= number_format($totalEspecies) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-md-4">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Infestación alta o crítica</div>
                        <div class="stat-value text-danger"><?= number_format($altasOCriticas) ?></div>
                    </div>
                </article>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Identificaciones por mes</div>
            <div class="card-body">
                <?php if ($totalIdentificaciones > 0): ?>
                    <div class="chart-wrap">
                        <canvas id="graficaPeriodo" role="img" aria-label="Identificaciones de plagas por mes"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay identificaciones en el periodo y municipio seleccionados.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Plagas más identificadas</div>
            <div class="card-body">
                <?php if ($plagasFrecuentes): ?>
                    <div class="chart-wrap mb-4">
                        <canvas id="graficaRanking" role="img" aria-label="Ranking de plagas identificadas"></canvas>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Plaga</th>
                                    <th scope="col">Identificaciones</th>
                                    <th scope="col">Porcentaje del total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($plagasFrecuentes as $plaga): ?>
                                    <?php
                                    $identificaciones = (int) $plaga['Identificaciones'];
                                    $porcentaje = $totalIdentificaciones > 0
                                        ? ($identificaciones / $totalIdentificaciones) * 100
                                        : 0;
                                    ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($plaga['Plaga'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= number_format($identificaciones) ?></td>
                                        <td><?= number_format($porcentaje, 1, ',', '.') ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay plagas identificadas para mostrar en este filtro.</p>
                <?php endif; ?>
            </div>
        </section>

        <a class="btn btn-outline-secondary mb-4" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </main>

    <?php if ($totalIdentificaciones > 0): ?>
        <script>
            new Chart(document.getElementById('graficaPeriodo'), {
                type: 'line',
                data: {
                    labels: <?= $labelsPeriodoJson ?>,
                    datasets: [{
                        label: 'Identificaciones',
                        data: <?= $datosPeriodoJson ?>,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, .12)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Registros de identificación' }
                        },
                        x: { title: { display: true, text: 'Periodo' } }
                    }
                }
            });

            new Chart(document.getElementById('graficaRanking'), {
                type: 'bar',
                data: {
                    labels: <?= $labelsPlagasJson ?>,
                    datasets: [{
                        label: 'Identificaciones',
                        data: <?= $datosPlagasJson ?>,
                        backgroundColor: '#198754'
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
                            title: { display: true, text: 'Registros de identificación' }
                        }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
