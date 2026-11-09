<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordenadas - PLAGUE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/administrador.css">
</head>
<body>

    <!-- Barra Superior de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-geo-alt me-2"></i>Coordenadas</a>
            <div class="d-flex align-items-center gap-2">
                <?php
                if (isset($_SESSION['rol'])) {
                    if ($_SESSION['rol'] === 'Administrador') {
                        echo '<a href="index.php?modulo=dashboard" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Regresar a administrador</a>';
                    } elseif ($_SESSION['rol'] === 'Tecnico') {
                        echo '<a href="index.php?modulo=tecnico_dashboard" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Regresar al menú técnico</a>';
                    }
                }
                ?>
                <!-- <a href="index.php?modulo=dashboard" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a> -->
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4" style="max-width: 600px;">
        <h2 class="mb-4">Registro de Coordenadas GPS (RF08)</h2>

        <div class="card shadow-sm p-4">
            <form>
                <div class="mb-3">
                    <label for="zona" class="form-label fw-bold">Zona urbana</label>
                    <input type="text" class="form-control" id="zona" name="zona" placeholder="Centro">
                </div>

                <div class="mb-3">
                    <label for="latitud" class="form-label fw-bold">Latitud</label>
                    <input type="text" class="form-control" id="latitud" name="latitud" placeholder="40° 24’ 59” N">
                </div>

                <div class="mb-3">
                    <label for="longitud" class="form-label fw-bold">Longitud</label>
                    <input type="text" class="form-control" id="longitud" name="longitud" placeholder="3° 42’ 09” O">
                </div>

                <div class="d-grid mt-4">
                    <button type="button" class="btn btn-primary" onclick="alert('Funcionalidad de captura lista.')">Guardar</button>
                </div>
            </form>
        </div>
        
        <div class="mt-4 mb-5 text-center">
            <a href="<?= BASE_URL ?>index.php?modulo=dashboard" class="text-decoration-none fw-bold">← Regresar al menú</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>