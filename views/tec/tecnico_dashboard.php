<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tecnico_Home</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-check me-2"></i>Menu/tecnico</a>
            <div class="d-flex align-items-center gap-2">
                <a href="../index.html" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a>
                <a href="notificaciones.php" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
                <a href="../administrador/logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <main class="container pb-5">

        <!-- Sección: Registrar / Acciones Principales -->
        <section class="mb-4">
            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3">Registrar y Acciones en Campo</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                
                <div class="col">
                    <a href="tecnico_coordenadas_gps.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-subtle text-success fs-5"><i class="bi bi-geo"></i></div>
                            <span class="text-dark fw-medium small">Coordenadas Gps</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="inspeccion_diagnostico.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-clipboard2-pulse"></i></div>
                            <span class="text-dark fw-medium small">Inspección y Diagnóstico en Campo</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="tratamientos.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-capsule"></i></div>
                            <span class="text-dark fw-medium small">Aplicacion de tratamientos</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="seguimiento_posterior.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-person-lines-fill"></i></div>
                            <span class="text-dark fw-medium small">Seguimiento</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="responsables.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-person-badge"></i></div>
                            <span class="text-dark fw-medium small">Resposable de la actividad</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="tec-recuperar-contraseña.html" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-key"></i></div>
                            <span class="text-dark fw-medium small">Recuperar Contraseña</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="../incidencias.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm border-warning-subtle">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-warning-subtle text-warning fs-5"><i class="bi bi-wifi-off"></i></div>
                            <span class="text-dark fw-medium small">Incidencias y Offline <span class="text-muted fs-8 d-block">RF21, RF26</span></span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="../evidencias.php" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm border-warning-subtle">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-warning-subtle text-warning fs-5"><i class="bi bi-images"></i></div>
                            <span class="text-dark fw-medium small">Evidencias <span class="text-muted fs-8 d-block">RF24, RF25</span></span>
                        </div>
                    </a>
                </div>

            </div>
        </section>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>