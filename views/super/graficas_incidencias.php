<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias por tipo - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f5f7fa; }
        .panel, .stat-card { border: 0; box-shadow: 0 2px 10px rgba(24, 39, 75, .07); }
        .stat-value { font-size: 1.7rem; font-weight: 700; }
        .chart-wrap { height: 390px; position: relative; }
        @media (max-width: 575px) {
            .chart-wrap { height: 330px; }
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
            <span class="navbar-brand mb-0"><i class="bi bi-pie-chart me-2"></i>Incidencias por tipo</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=graficas">
                <i class="bi bi-arrow-left"></i> Volver a gráficas
            </a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Incidencias registradas por categoría</h1>
            <p class="text-muted mb-0">Conteo según el tipo de problema asignado al registrar cada incidencia.</p>
        </header>

        <?php foreach ($mensajes as $mensaje): ?>
            <div class="alert alert-warning" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="graficas_incidencias">
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
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=graficas_incidencias">Quitar filtros</a>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Resumen">
            <div class="col-md-6">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Total de incidencias</div>
                        <div class="stat-value"><?= number_format($totalIncidencias) ?></div>
                    </div>
                </article>
            </div>
            <div class="col-md-6">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Tipo más registrado</div>
                        <?php if ($incidenciasPorTipo): ?>
                            <div class="stat-value text-primary"><?= htmlspecialchars($incidenciasPorTipo[0]['Tipo'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-muted"><?= number_format((int) $incidenciasPorTipo[0]['TotalIncidencias']) ?> incidencias</div>
                        <?php else: ?>
                            <div class="stat-value text-secondary">Sin datos</div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Incidencias por tipo</div>
            <div class="card-body">
                <?php if ($incidenciasPorTipo): ?>
                    <div class="chart-wrap">
                        <canvas id="graficaIncidencias" role="img" aria-label="Conteo de incidencias por tipo"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay incidencias para el periodo y municipio seleccionados.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Detalle de incidencias</div>
            <div class="card-body">
                <?php if ($incidenciasPorTipo): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo de incidencia</th>
                                    <th>Total</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incidenciasPorTipo as $incidencia): ?>
                                    <?php $cantidad = (int) $incidencia['TotalIncidencias']; ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($incidencia['Tipo'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= number_format($cantidad) ?></td>
                                        <td><?= number_format(($cantidad / $totalIncidencias) * 100, 1, ',', '.') ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay registros que detallar.</p>
                <?php endif; ?>
            </div>
        </section>

        <a class="btn btn-outline-secondary mb-4" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </main>

    <?php if ($incidenciasPorTipo): ?>
        <script>
            new Chart(document.getElementById('graficaIncidencias'), {
                type: 'bar',
                data: {
                    labels: <?= $labelsJson ?>,
                    datasets: [{
                        label: 'Incidencias',
                        data: <?= $cantidadesJson ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.65)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1
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
                            title: { display: true, text: 'Número de incidencias' }
                        },
                        y: {
                            title: { display: true, text: 'Tipo de incidencia' }
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
