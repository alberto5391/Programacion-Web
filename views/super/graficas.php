<?php
// Regreso al panel según el rol (la página también se puede abrir como administrador).
$rolActual = $_SESSION['rol'] ?? '';
$moduloRegreso = $rolActual === 'Supervisor'
    ? 'super_dashboard'
    : ($rolActual === 'Tecnico' ? 'tecnico_dashboard' : 'dashboard');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficas - PLAGUE</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Mismo estilo de tarjetas que los paneles de supervisor y técnico -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .nav-card {
            transition: all 0.2s ease-in-out;
            border: 1px solid rgba(0,0,0,0.08);
        }
        .nav-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
            border-color: #0d6efd;
        }
        .nav-card:hover .icon-box {
            background-color: #0d6efd !important;
            color: #fff !important;
        }
        .icon-box {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        .fs-7 { font-size: .8rem; }
    </style>
</head>
<body>

    <!-- Barra Superior de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-bar-chart-line me-2"></i>Gráficas</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <main class="container pb-5">

        <header class="mb-4">
            <h1 class="h3 mb-1">Panel de gráficas</h1>
            <p class="text-muted mb-0">Selecciona un reporte para consultar sus estadísticas.</p>
        </header>

        <section class="mb-4">
            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3">Reportes disponibles</h6>
            <div class="row row-cols-1 row-cols-md-2 g-3">

                <div class="col">
                    <a href="index.php?modulo=graficas_productos" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-primary-subtle text-primary fs-5"><i class="bi bi-box-seam"></i></div>
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">Productos utilizados</h6>
                                <p class="mb-0 text-muted small">Productos más aplicados en los tratamientos.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=graficas_incidencias" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-warning-subtle text-warning fs-5"><i class="bi bi-exclamation-triangle"></i></div>
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">Incidencias</h6>
                                <p class="mb-0 text-muted small">Incidencias registradas por tipo.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=graficas_plagas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-danger-subtle text-danger fs-5"><i class="bi bi-bug"></i></div>
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">Plagas</h6>
                                <p class="mb-0 text-muted small">Plagas identificadas en los diagnósticos.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=graficas_zonas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-subtle text-success fs-5"><i class="bi bi-geo-alt"></i></div>
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">Zonas</h6>
                                <p class="mb-0 text-muted small">Acciones, plagas e incidencias por zona.</p>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </section>

        <a href="index.php?modulo=<?= htmlspecialchars($moduloRegreso, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Regresar al menú
        </a>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
