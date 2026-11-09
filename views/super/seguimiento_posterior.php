<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/supervisor.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento</title>
</head>
<body>

    <!-- Barra Superior de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-shield-check me-2"></i>Registro de seguimiento</a>
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?modulo=home" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Inicio</a>
                <a href="index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                    <i class="bi bi-bell"></i> Notificaciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Registro de seguimiento posterior (RF20)</h2>
        
        <!-- UN SOLO FORMULARIO CON method="POST" -->
        <form method="POST" action="index.php">

            <!-- INPUT OCULTO: Define a qué módulo ir según el rol -->
            <?php
                $moduloDestino = 'home';
                if (isset($_SESSION['rol'])) {
                    if ($_SESSION['rol'] === 'Supervisor') {
                        $moduloDestino = 'super_dashboard';
                    } elseif ($_SESSION['rol'] === 'Tecnico') {
                        $moduloDestino = 'tecnico_dashboard';
                    }
                }
            ?>
            <input type="hidden" name="modulo" value="<?php echo $moduloDestino; ?>">

            <div class="form-floating mb-3">
                <input type="date" class="form-control" id="fecha_seguimiento" name="fecha_seguimiento">
                <label for="fecha_seguimiento">Fecha de seguimiento:</label>
            </div>

            <div class="mb-3">
                <label for="presencia_plaga" class="form-label">Presencia o ausencia de la plaga:</label>
                <select class="form-select" id="presencia_plaga" name="presencia_plaga">
                    <option selected disabled>Seleccione una opción</option>
                    <option value="1">Ausente</option>
                    <option value="2">Presente</option>
                    <option value="3">Reincidente</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="resultados_seg" class="form-label">Resultados obtenidos:</label>
                <input type="text" class="form-control" id="resultados_seg" name="resultados_seg">
            </div>

            <div class="mb-3">
                <label for="incidencias" class="form-label">Incidencias detectadas:</label>
                <input type="text" class="form-control" id="incidencias" name="incidencias">
            </div>

            <!-- Botones agrupados -->
            <div class="d-flex gap-2 mt-4 mb-5">
                <!-- El botón submit ya no necesita lógica PHP, solo hace su trabajo -->
                <button type="submit" class="btn btn-primary">Guardar seguimiento</button>
                
                <!-- Botón de regresar dinámico -->
                <?php
                if (isset($_SESSION['rol'])) {
                    if ($_SESSION['rol'] === 'Supervisor') {
                        echo '<a href="index.php?modulo=super_dashboard" class="btn btn-outline-secondary">← Regresar al menú supervisor</a>';
                    } elseif ($_SESSION['rol'] === 'Tecnico') {
                        echo '<a href="index.php?modulo=tecnico_dashboard" class="btn btn-outline-secondary">← Regresar al menú técnico</a>';
                    }
                }
                ?>
            </div>

        </form>
    </div>

</body>
</html>