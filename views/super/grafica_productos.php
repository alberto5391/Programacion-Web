<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos utilizados - PLAGUE</title>
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
    $labelsGrafica = array_column($productosMasUsados, 'Producto');
    $aplicacionesGrafica = array_map(
        static function ($producto) {
            return (int) $producto['Aplicaciones'];
        },
        $productosMasUsados
    );
    $labelsJson = json_encode($labelsGrafica, $opcionesJson);
    $aplicacionesJson = json_encode($aplicacionesGrafica, $opcionesJson);
    ?>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0"><i class="bi bi-bar-chart-line me-2"></i>Productos utilizados</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=graficas">
                <i class="bi bi-arrow-left"></i> Volver a gráficas
            </a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Uso de productos en aplicaciones</h1>
            <p class="text-muted mb-0">Aplicaciones registradas y cantidad aplicada por producto en el periodo seleccionado.</p>
        </header>

        <?php foreach ($mensajes as $mensaje): ?>
            <div class="alert alert-warning" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="graficas_productos">
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
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=graficas_productos">Quitar filtros</a>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Resumen">
            <div class="col-md-6">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Productos en el reporte</div>
                        <div class="stat-value"><?= count($productosMasUsados) ?></div>
                        <div class="small text-muted">Se muestran hasta 10 productos con aplicaciones registradas.</div>
                    </div>
                </article>
            </div>
            <div class="col-md-6">
                <article class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Producto con más aplicaciones</div>
                        <?php if ($productosMasUsados): ?>
                            <div class="stat-value text-primary"><?= htmlspecialchars($productosMasUsados[0]['Producto'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="small text-muted"><?= number_format((int) $productosMasUsados[0]['Aplicaciones']) ?> aplicaciones registradas</div>
                        <?php else: ?>
                            <div class="stat-value text-secondary">Sin datos</div>
                            <div class="small text-muted">No hay aplicaciones para los filtros seleccionados.</div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Productos con más aplicaciones</div>
            <div class="card-body">
                <?php if ($productosMasUsados): ?>
                    <div class="chart-wrap">
                        <canvas id="graficaProductos" role="img" aria-label="Número de aplicaciones por producto"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay aplicaciones de productos en el periodo y municipio seleccionados.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Detalle de cantidades aplicadas</div>
            <div class="card-body">
                <?php if ($productosMasUsados): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Aplicaciones</th>
                                    <th>Cantidad total aplicada</th>
                                    <th>Unidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productosMasUsados as $producto): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($producto['Producto'], ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= number_format((int) $producto['Aplicaciones']) ?></td>
                                        <td><?= number_format((float) $producto['CantidadUtilizada'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($producto['UnidadMedida'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay cantidades registradas para mostrar.</p>
                <?php endif; ?>
            </div>
        </section>

        <a class="btn btn-outline-secondary mb-4" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>
    </main>

    <?php if ($productosMasUsados): ?>
        <script>
            new Chart(document.getElementById('graficaProductos'), {
                type: 'bar',
                data: {
                    labels: <?= $labelsJson ?>,
                    datasets: [{
                        label: 'Aplicaciones registradas',
                        data: <?= $aplicacionesJson ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.65)',
                        borderColor: 'rgb(13, 110, 253)',
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
                            title: { display: true, text: 'Número de aplicaciones' }
                        },
                        y: {
                            title: { display: true, text: 'Producto' }
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>
