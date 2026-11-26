<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLAGUE - Estadísticas dinámicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body { background: #f5f7fa; }
        .stat-card, .panel { border: 0; box-shadow: 0 2px 10px rgba(24, 39, 75, .07); }
        .stat-value { font-size: 1.8rem; font-weight: 700; }
        .chart-wrap { min-height: 290px; position: relative; }
        #mapa-zonas { height: 420px; border-radius: .35rem; }
        .chart-panel { min-height: 390px; }
        @media (max-width: 575px) {
            #mapa-zonas { height: 320px; }
            .stat-value { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <?php
    $moduloRegreso = ($_SESSION['rol'] ?? '') === 'Supervisor'
        ? 'super_dashboard'
        : ((($_SESSION['rol'] ?? '') === 'Tecnico') ? 'tecnico_dashboard' : 'dashboard');
    $opcionesJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
    ?>
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0">Estadísticas y mapas</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">Volver al menú</a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Panel estadístico</h1>
            <p class="text-muted mb-0">Indicadores calculados a partir de campañas, acciones, plagas e insumos registrados.</p>
        </header>

        <?php foreach ($mensajes as $mensajeFiltro): ?>
            <div class="alert alert-warning" role="alert"><?= htmlspecialchars($mensajeFiltro, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4">
            <div class="card-header bg-white fw-semibold">Filtros</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="estadisticas">
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
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=estadisticas">Quitar filtros</a>
            </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Resumen">
            <div class="col-sm-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Campañas en el periodo</div>
                        <div class="stat-value"><?= number_format((int)$resumen['TotalCampanas']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">Finalizadas</div>
                        <div class="stat-value text-success"><?= number_format((int)$resumen['CampanasFinalizadas']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">En curso</div>
                        <div class="stat-value text-primary"><?= number_format((int)$resumen['CampanasEnCurso']) ?></div>
                    </div>
                </div>
            </div>
        </section>

        <h2 class="h4 mb-3">Reportes del periodo</h2>
        <section class="row g-3">
            <div class="col-lg-6">
                <article class="card panel chart-panel h-100">
                    <div class="card-header bg-white fw-semibold">Campañas iniciadas por mes</div>
                    <div class="card-body">
                        <?php if ($campanasPorPeriodo): ?>
                            <div class="chart-wrap"><canvas id="grafica-campanas" aria-label="Campañas por mes" role="img"></canvas></div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay campañas en el periodo seleccionado.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="card panel chart-panel h-100">
                    <div class="card-header bg-white fw-semibold">Plagas más identificadas</div>
                    <div class="card-body">
                        <?php if ($plagasFrecuentes): ?>
                            <div class="chart-wrap"><canvas id="grafica-plagas" aria-label="Plagas identificadas" role="img"></canvas></div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay identificaciones de plagas en el periodo.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="card panel chart-panel h-100">
                    <div class="card-header bg-white fw-semibold">Productos utilizados</div>
                    <div class="card-body">
                        <?php if ($productosMasUsados): ?>
                            <div class="chart-wrap"><canvas id="grafica-productos" aria-label="Productos utilizados" role="img"></canvas></div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay insumos aplicados en el periodo.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <div class="col-lg-6">
                <article class="card panel chart-panel h-100">
                    <div class="card-header bg-white fw-semibold">Incidencias por actividad</div>
                    <div class="card-body">
                        <?php if ($incidenciasPorActividad): ?>
                            <div class="chart-wrap"><canvas id="grafica-incidencias" aria-label="Incidencias por actividad" role="img"></canvas></div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay incidencias en el periodo.</p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="card panel mt-4">
            <div class="card-header bg-white fw-semibold">Zonas con más actividad registrada</div>
            <div class="card-body">
                <?php if ($zonasParaGrafica): ?>
                    <div class="chart-wrap"><canvas id="grafica-zonas" aria-label="Actividad por zona" role="img"></canvas></div>
                <?php else: ?>
                    <p class="text-muted mb-0">No hay zonas activas para los filtros seleccionados.</p>
                <?php endif; ?>
                <p class="small text-muted mb-0 mt-2">Se muestran acciones, identificaciones de plagas e incidencias como conteos separados; no representan una calificación de riesgo.</p>
            </div>
        </section>

        <section class="card panel mt-4">
            <div class="card-header bg-white fw-semibold">Mapa de zonas activas</div>
            <div class="card-body">
                <div id="mapa-zonas" role="img" aria-label="Mapa de zonas activas filtradas"></div>
                <p class="small text-muted mb-0 mt-2">Los marcadores muestran las coordenadas registradas para cada zona, no la ubicación exacta de cada intervención.</p>
            </div>
        </section>

        <section class="card panel mt-4">
            <div class="card-header bg-white fw-semibold">Efectividad de tratamientos</div>
            <div class="card-body">
                <p class="mb-0">Aún no se calcula un porcentaje: la base guarda observaciones en texto libre y no tiene un resultado estructurado de seguimiento o reincidencia. Así evitamos presentar una efectividad engañosa.</p>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const periodos = <?= json_encode($campanasPorPeriodo, $opcionesJson) ?>;
        const plagas = <?= json_encode($plagasFrecuentes, $opcionesJson) ?>;
        const productos = <?= json_encode($productosMasUsados, $opcionesJson) ?>;
        const incidencias = <?= json_encode($incidenciasPorActividad, $opcionesJson) ?>;
        const zonas = <?= json_encode($estadisticasZonas, $opcionesJson) ?>;
        const zonasGrafica = <?= json_encode($zonasParaGrafica, $opcionesJson) ?>;

        const crearGrafica = (id, tipo, etiquetas, conjuntos, opciones = {}) => {
            const canvas = document.getElementById(id);
            if (!canvas || typeof Chart === 'undefined') return;
            new Chart(canvas, {
                type: tipo,
                data: { labels: etiquetas, datasets: conjuntos },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    ...opciones
                }
            });
        };

        crearGrafica(
            'grafica-campanas',
            'bar',
            periodos.map((item) => item.Periodo),
            [
                { label: 'Campañas', data: periodos.map((item) => Number(item.TotalCampanas)), backgroundColor: '#0d6efd' },
                { label: 'Finalizadas', data: periodos.map((item) => Number(item.CampanasFinalizadas)), backgroundColor: '#198754' }
            ]
        );
        crearGrafica(
            'grafica-plagas',
            'bar',
            plagas.map((item) => item.Plaga),
            [{ label: 'Identificaciones', data: plagas.map((item) => Number(item.Identificaciones)), backgroundColor: '#dc3545' }],
            { indexAxis: 'y' }
        );
        crearGrafica(
            'grafica-productos',
            'bar',
            productos.map((item) => item.Producto),
            [{ label: 'Aplicaciones', data: productos.map((item) => Number(item.Aplicaciones)), backgroundColor: '#6f42c1' }],
            { indexAxis: 'y' }
        );
        crearGrafica(
            'grafica-incidencias',
            'doughnut',
            incidencias.map((item) => item.Actividad),
            [{ label: 'Incidencias', data: incidencias.map((item) => Number(item.TotalIncidencias)), backgroundColor: ['#0d6efd', '#fd7e14', '#20c997'] }]
        );
        crearGrafica(
            'grafica-zonas',
            'bar',
            zonasGrafica.map((item) => item.NombreZona),
            [
                { label: 'Acciones', data: zonasGrafica.map((item) => Number(item.TotalAcciones)), backgroundColor: '#0d6efd' },
                { label: 'Identificaciones de plagas', data: zonasGrafica.map((item) => Number(item.TotalIdentificaciones)), backgroundColor: '#dc3545' },
                { label: 'Incidencias', data: zonasGrafica.map((item) => Number(item.TotalIncidencias)), backgroundColor: '#fd7e14' }
            ],
            { indexAxis: 'y' }
        );

        if (typeof L !== 'undefined') {
            const mapa = L.map('mapa-zonas');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapa);

            const marcadores = zonas.filter((zona) =>
                zona.Latitud !== null
                && zona.Longitud !== null
                && Number.isFinite(Number(zona.Latitud))
                && Number.isFinite(Number(zona.Longitud))
            );
            if (marcadores.length > 0) {
                const limites = [];
                marcadores.forEach((zona) => {
                    const punto = [Number(zona.Latitud), Number(zona.Longitud)];
                    limites.push(punto);
                    const contenido = document.createElement('div');
                    const titulo = document.createElement('strong');
                    titulo.textContent = zona.NombreZona;
                    contenido.appendChild(titulo);
                    const municipio = document.createElement('div');
                    municipio.textContent = zona.Municipio;
                    contenido.appendChild(municipio);
                    const resumen = document.createElement('div');
                    resumen.textContent = `Acciones: ${zona.TotalAcciones} · Plagas: ${zona.TotalIdentificaciones} · Incidencias: ${zona.TotalIncidencias}`;
                    contenido.appendChild(resumen);
                    L.marker(punto).addTo(mapa).bindPopup(contenido);
                });
                mapa.fitBounds(limites, { padding: [20, 20] });
            } else {
                mapa.setView([17.99, -92.94], 8);
            }
        }
    </script>
</body>
</html>
