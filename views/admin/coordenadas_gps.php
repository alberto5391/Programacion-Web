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
            <a class="navbar-brand" href="#"><i class="bi bi-geo-alt me-2"></i>Ubicación de zonas</a>
            <div class="d-flex align-items-center gap-2">
                <?php
                if (isset($_SESSION['rol'])) {
                    if ($_SESSION['rol'] === 'Tecnico') {
                        echo '<a href="index.php?modulo=tecnico_dashboard" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Regresar al menú técnico</a>';
                    }
                }
                ?>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4" style="max-width: 600px;">
        <h2 class="mb-2">Captura de coordenadas GPS</h2>
        <p class="text-muted">Las coordenadas se guardan en la zona seleccionada; no se crea un registro GPS separado.</p>
        <?= $mensaje ?>

        <div class="card shadow-sm p-4">
            <form action="<?= BASE_URL ?>index.php" method="POST">
                <input type="hidden" name="modulo" value="coordenadas_gps">
                <input type="hidden" name="accion" value="actualizar_coordenadas">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label for="id_zona" class="form-label fw-bold">Zona urbana</label>
                    <select class="form-select" id="id_zona" name="id_zona" required>
                        <option value="">Selecciona una zona</option>
                        <?php if ($resultadoZonas): ?>
                            <?php while ($zona = $resultadoZonas->fetch_assoc()): ?>
                                <?php
                                $idZona = (int)$zona['IdZona'];
                                $coordenadasZona = $zona['Latitud'] !== null && $zona['Longitud'] !== null
                                    ? $zona['Latitud'] . ', ' . $zona['Longitud']
                                    : 'sin coordenadas';
                                $nombreZona = htmlspecialchars($zona['CodigoZona'] . ' - ' . $zona['NombreZona'] . ' (' . $zona['Municipio'] . ') — ' . $coordenadasZona);
                                ?>
                                <option value="<?= $idZona ?>" <?= $zonaSeleccionada === $idZona ? 'selected' : '' ?>>
                                    <?= $nombreZona ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="latitud" class="form-label fw-bold">Latitud</label>
                    <input type="number" class="form-control" id="latitud" name="latitud" min="-90" max="90" step="0.00000001" required>
                </div>

                <div class="mb-3">
                    <label for="longitud" class="form-label fw-bold">Longitud</label>
                    <input type="number" class="form-control" id="longitud" name="longitud" min="-180" max="180" step="0.00000001" required>
                </div>

                <button type="button" class="btn btn-outline-secondary w-100" id="obtener-ubicacion">
                    Usar ubicación actual del dispositivo
                </button>
                <p class="small text-muted mt-2 mb-0" id="estado-ubicacion" aria-live="polite"></p>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary" <?= !$resultadoZonas || $resultadoZonas->num_rows === 0 ? 'disabled' : '' ?>>Guardar coordenadas en la zona</button>
                </div>
            </form>
        </div>
        
        <div class="mt-4 mb-5 text-center">
            <a href="<?= BASE_URL ?>index.php?modulo=tecnico_dashboard" class="text-decoration-none fw-bold">← Regresar al menú</a>
        </div>
    </div>

    <script>
        const botonUbicacion = document.getElementById('obtener-ubicacion');
        const estadoUbicacion = document.getElementById('estado-ubicacion');

        botonUbicacion.addEventListener('click', function () {
            if (!navigator.geolocation) {
                estadoUbicacion.textContent = 'Este dispositivo no ofrece geolocalización desde el navegador.';
                return;
            }

            estadoUbicacion.textContent = 'Obteniendo ubicación...';
            navigator.geolocation.getCurrentPosition(
                function (posicion) {
                    document.getElementById('latitud').value = posicion.coords.latitude.toFixed(8);
                    document.getElementById('longitud').value = posicion.coords.longitude.toFixed(8);
                    estadoUbicacion.textContent = 'Ubicación obtenida. Revisa los valores y guarda la zona.';
                },
                function () {
                    estadoUbicacion.textContent = 'No se pudo obtener la ubicación. Verifica los permisos del navegador o captura las coordenadas manualmente.';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>