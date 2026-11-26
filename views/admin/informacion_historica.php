<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información histórica - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .panel { border: 0; box-shadow: 0 2px 10px rgba(24, 39, 75, .07); }
        .detail-title { overflow-wrap: anywhere; }
        .evidence-image { max-width: 180px; max-height: 140px; object-fit: contain; }
        .preserve-lines { white-space: pre-wrap; }
        @media print {
            body { background: #fff; }
            .no-print, nav { display: none !important; }
            .panel { box-shadow: none; border: 1px solid #bbb; break-inside: avoid; }
            main { max-width: none !important; }
        }
    </style>
</head>
<body>
    <?php
    $moduloRegreso = ($_SESSION['rol'] ?? '') === 'Supervisor'
        ? 'super_dashboard'
        : ((($_SESSION['rol'] ?? '') === 'Tecnico') ? 'tecnico_dashboard' : 'dashboard');
    $clasesEstado = [
        'Programada' => 'text-bg-primary',
        'En Curso' => 'text-bg-warning',
        'Finalizada' => 'text-bg-success',
        'Cancelada' => 'text-bg-secondary'
    ];
    ?>
    <nav class="navbar navbar-dark bg-dark shadow-sm no-print">
        <div class="container">
            <span class="navbar-brand mb-0">Información histórica</span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">Volver al menú</a>
        </div>
    </nav>

    <main class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Historial de campañas</h1>
            <p class="text-muted mb-0">Consulta campañas, responsables y actividad operativa, incluidos registros con usuarios o zonas inactivos.</p>
        </header>

        <?php foreach ($mensajes as $mensaje): ?>
            <div class="alert alert-warning no-print" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>

        <section class="card panel mb-4 no-print">
            <div class="card-header bg-white fw-semibold">Filtros de búsqueda</div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>index.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="informacion_historica">
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label" for="busqueda">Campaña, zona o responsable</label>
                        <input class="form-control" type="search" id="busqueda" name="busqueda" maxlength="100" value="<?= htmlspecialchars($filtros['busqueda'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Código, nombre o persona">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="desde">Inicio desde</label>
                        <input class="form-control" type="date" id="desde" name="desde" value="<?= htmlspecialchars($filtros['desde'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="hasta">Inicio hasta</label>
                        <input class="form-control" type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($filtros['hasta'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="">Todos</option>
                            <?php foreach (['Programada', 'En Curso', 'Finalizada', 'Cancelada'] as $estado): ?>
                                <option value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>" <?= $filtros['estado'] === $estado ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-8 col-lg-2">
                        <label class="form-label" for="municipio">Municipio</label>
                        <select class="form-select" id="municipio" name="municipio">
                            <option value="">Todos</option>
                            <?php foreach ($municipios as $opcionMunicipio): ?>
                                <option value="<?= htmlspecialchars($opcionMunicipio['Municipio'], ENT_QUOTES, 'UTF-8') ?>" <?= $filtros['municipio'] === $opcionMunicipio['Municipio'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcionMunicipio['Municipio'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 col-lg-1">
                        <button class="btn btn-primary w-100" type="submit">Buscar</button>
                    </div>
                </form>
                <a class="small d-inline-block mt-3" href="<?= BASE_URL ?>index.php?modulo=informacion_historica">Quitar filtros</a>
            </div>
        </section>

        <section class="card panel mb-4 no-print">
            <div class="card-header bg-white fw-semibold">Campañas encontradas (<?= count($campanas) ?>)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Campaña</th>
                                <th>Zona</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th>Consulta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($campanas): ?>
                                <?php foreach ($campanas as $campana): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($campana['CodigoCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($campana['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?= htmlspecialchars($campana['NombreZona'] . ', ' . $campana['Municipio'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php if ($campana['EstadoZona'] === 'Inactiva'): ?>
                                                <span class="badge text-bg-secondary">Zona inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($campana['FechaInicio'], ENT_QUOTES, 'UTF-8') ?>
                                            –
                                            <?= htmlspecialchars($campana['FechaFin'] ?? 'Sin cierre', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $clasesEstado[$campana['Estado']] ?? 'text-bg-secondary' ?>">
                                                <?= htmlspecialchars($campana['Estado'] ?? 'Sin estado', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>index.php?modulo=informacion_historica&amp;id_campana=<?= (int)$campana['IdCampana'] ?>">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No se encontraron campañas con esos filtros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <?php if ($campanaHistorica): ?>
            <section id="detalle-campana">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h2 class="h4 detail-title mb-1">
                            <?= htmlspecialchars($campanaHistorica['CodigoCampana'] . ' — ' . $campanaHistorica['NombreCampana'], ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <div class="text-muted">
                            <?= htmlspecialchars($campanaHistorica['NombreZona'] . ', ' . $campanaHistorica['Municipio'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($campanaHistorica['EstadoZona'] === 'Inactiva'): ?>
                                · Zona inactiva
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="btn btn-outline-danger no-print" type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Resumen de campaña</div>
                    <div class="card-body row g-3">
                        <div class="col-sm-6 col-lg-3"><strong>Estado:</strong> <?= htmlspecialchars($campanaHistorica['Estado'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-sm-6 col-lg-3"><strong>Inicio:</strong> <?= htmlspecialchars($campanaHistorica['FechaInicio'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-sm-6 col-lg-3"><strong>Cierre:</strong> <?= htmlspecialchars($campanaHistorica['FechaFin'] ?? 'Sin fecha de cierre', ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="col-sm-6 col-lg-3"><strong>Municipio:</strong> <?= htmlspecialchars($campanaHistorica['Municipio'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($campanaHistorica['Referencias'] !== ''): ?>
                            <div class="col-12"><strong>Referencias de zona:</strong> <?= htmlspecialchars($campanaHistorica['Referencias'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Responsables asignados</div>
                    <div class="card-body">
                        <?php if ($responsables): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead><tr><th>Nombre</th><th>Número de empleado</th><th>Especialidad</th><th>Estado actual</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($responsables as $responsable): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($responsable['NombreCompleto'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($responsable['NumeroEmpleado'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($responsable['Especialidad'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($responsable['EstadoUsuario'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay responsables asignados registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Solicitudes relacionadas</div>
                    <div class="card-body">
                        <?php if ($solicitantes): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead><tr><th>Fecha</th><th>Origen</th><th>Solicitante</th><th>Estado</th><th>Descripción</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($solicitantes as $solicitante): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($solicitante['FechaSolicitud'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($solicitante['TipoOrigen'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($solicitante['NombreSolicitante'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($solicitante['EstadoSolicitud'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($solicitante['DescripcionSolicitud'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay solicitudes relacionadas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Acciones registradas</div>
                    <div class="card-body">
                        <?php if ($acciones): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Técnico</th><th>Resultado</th><th>Observaciones</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($acciones as $accion): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($accion['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($accion['TipoAccion'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($accion['Tecnico'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="preserve-lines"><?= htmlspecialchars($accion['DetallesResultados'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="preserve-lines"><?= htmlspecialchars($accion['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay acciones operativas registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card panel h-100">
                            <div class="card-header bg-white fw-semibold">Incidencias</div>
                            <div class="card-body">
                                <?php if ($incidencias): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead><tr><th>Fecha</th><th>Zona registrada</th><th>Actividad</th><th>Descripción</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($incidencias as $incidencia): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($incidencia['FechaRegistro'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($incidencia['Zona'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($incidencia['ActividadRelacionada'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($incidencia['Descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No hay incidencias registradas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card panel h-100">
                            <div class="card-header bg-white fw-semibold">Plagas identificadas</div>
                            <div class="card-body">
                                <?php if ($plagasIdentificadas): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead><tr><th>Fecha</th><th>Plaga</th><th>Nivel</th><th>Observaciones</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($plagasIdentificadas as $plaga): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($plaga['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($plaga['Plaga'], ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($plaga['NivelInfestacion'] ?? 'Sin nivel', ENT_QUOTES, 'UTF-8') ?></td>
                                                        <td><?= htmlspecialchars($plaga['Observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No hay plagas identificadas registradas.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Insumos utilizados</div>
                    <div class="card-body">
                        <?php if ($insumosUsados): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Unidad</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($insumosUsados as $insumo): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($insumo['FechaHora'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($insumo['Producto'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($insumo['CantidadAplicada'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($insumo['UnidadMedida'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay insumos registrados para esta campaña.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card panel mb-4">
                    <div class="card-header bg-white fw-semibold">Evidencias fotográficas</div>
                    <div class="card-body">
                        <?php if ($evidencias): ?>
                            <div class="row g-3">
                                <?php foreach ($evidencias as $evidencia): ?>
                                    <?php $rutaEvidencia = BASE_URL . ltrim($evidencia['RutaArchivo'], '/\\'); ?>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <a href="<?= htmlspecialchars($rutaEvidencia, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                            <img class="img-thumbnail evidence-image" src="<?= htmlspecialchars($rutaEvidencia, ENT_QUOTES, 'UTF-8') ?>" alt="Evidencia de campaña">
                                        </a>
                                        <div class="small mt-1"><?= htmlspecialchars($evidencia['TipoAccion'] . ' · ' . $evidencia['TipoActividad'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($evidencia['FechaSubida'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay evidencias fotográficas registradas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <footer class="text-center py-3 no-print">
            <a href="<?= BASE_URL ?>index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>">← Regresar al menú</a>
        </footer>
    </main>
</body>
</html>
