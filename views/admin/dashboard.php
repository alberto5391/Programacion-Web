<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Home</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Tu CSS Personalizado (Asumiendo que index.php está en la raíz) -->
    <link rel="stylesheet" href="css/administrador.css">
</head>
<body>

    <!-- Barra Superior de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-check me-2"></i>Menú / Administrador</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
                <a href="index.php?modulo=logout" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <main class="container pb-5">

        <!-- Sección: Registrar / Acciones Principales -->
        <section class="mb-4">
            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3">Registrar</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <a href="index.php?modulo=coordenadas_gps" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-subtle text-success fs-5">
                                <i class="bi bi-geo"></i>
                            </div>
                             <div>
                                 <h6 class="mb-1 text-dark fw-semibold">Coordenadas Gps</h6> 
                                <p class="mb-0 text-muted small">Registrar ubicaciones en el sistema.</p> 
                            </div> 
                        </div> 
                    </a>
                </div>  
                <div class="col-md-6">
                    <a href="index.php?modulo=bitacora" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-subtle text-success fs-5">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-dark fw-semibold">Auditorías</h6>
                                <p class="mb-0 text-muted small">Control de auditorías y registros.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Sección: Gestión y Módulos del Sistema -->
        <section>
            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3">Gestión y Módulos Administrativos</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">

                <div class="col">
                    <a href="index.php?modulo=control_acceso" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-lock"></i></div>
                            <span class="text-dark fw-medium small">Control información</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=solicitantes" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-people"></i></div>
                            <span class="text-dark fw-medium small">Gestión Solicitantes</span>
                        </div>
                    </a>
                </div>

                <!-- AQUÍ SE UNIFICAN LOS RF02, 03, 04 Y 05 EN UNA SOLA TARJETA -->
                <div class="col">
                    <a href="index.php?modulo=usuarios" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm border-primary-subtle">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-primary-subtle text-primary fs-5"><i class="bi bi-person-gear"></i></div>
                            <span class="text-dark fw-medium small">Gestión de Usuarios <span class="text-muted fs-8 d-block">Registro y Consulta (RF02-RF05)</span></span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=zonas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-map"></i></div>
                            <span class="text-dark fw-medium small">RF07 Gestión del catálogo de zonas</span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=plagas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-bug"></i></div>
                            <span class="text-dark fw-medium small">RF09 Gestión del catálogo de plagas</span>
                        </div>
                    </a>
                </div>  

                <div class="col">
                    <a href="index.php?modulo=productos" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-box-seam"></i></div>
                            <span class="text-dark fw-medium small">RF10/RF17 Catálogo de productos</span>
                        </div>
                    </a>
                </div> 

                <div class="col">
                    <a href="index.php?modulo=tecnicos" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-person-badge"></i></div>
                            <span class="text-dark fw-medium small">RF11 Gestión del personal técnico</span>
                        </div>
                    </a>
                </div>  

                <div class="col">
                    <a href="index.php?modulo=campanas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-clock-history"></i></div>
                            <span class="text-dark fw-medium small">RF22 Historial de campañas</span>
                        </div>
                    </a>
                </div> 

                <div class="col">
                    <a href="index.php?modulo=evidencias" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm border-warning-subtle">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-warning-subtle text-warning fs-5"><i class="bi bi-images"></i></div>
                            <span class="text-dark fw-medium small">Evidencias <span class="text-muted fs-8 d-block">RF24, RF25</span></span>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="index.php?modulo=estadisticas" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm border-info-subtle">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-info-subtle text-info fs-5"><i class="bi bi-map-fill"></i></div>
                            <span class="text-dark fw-medium small">Estadísticas y Mapas <span class="text-muted fs-8 d-block">RF28-RF35</span></span>
                        </div>
                    </a>
                </div>

                <!-- Histórica y Bitácora unificadas o adyacentes si lo deseas -->
                <div class="col">
                    <a href="index.php?modulo=informacion_historica" class="card nav-card text-decoration-none h-100 bg-white p-3 shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-light text-secondary fs-5"><i class="bi bi-archive"></i></div>
                            <span class="text-dark fw-medium small">Consulta Histórica</span>
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