<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bitácora de auditoría - PLAGUE</title>

    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<!-- Barra Superior de Navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php?modulo=dashboard">
            <i class="bi bi-shield-check me-2"></i> Bitácora Auditoría
        </a>

        <div class="d-flex align-items-center gap-2">
           <a href="<?= BASE_URL ?>index.php?modulo=dashboard" class="btn btn-outline-light btn-sm mr-2">
            <i class="bi bi-arrow-left"></i> Inicio 
            </a> 
            <a href="<?= BASE_URL ?>index.php?modulo=notificaciones" class="btn btn-primary btn-sm position-relative">
                <i class="bi bi-bell"></i> Notificaciones
            </a>
        </div>
    </div>
</nav>

    <!-- CONTENIDO -->
    <div class="container mt-4">

        <h2>Bitácora de auditoría y seguridad (RF45)</h2>

        <p class="text-muted">
            Consulta las acciones que están registradas en la bitácora. El esquema actual guarda usuario,
            módulo, tipo de acción y fechas; no almacena el detalle ni el registro afectado.
        </p>


        <!-- FILTROS -->
        <div class="card shadow-sm mb-4">

            <div class="card-header bg-dark text-white">
                Filtros de consulta
            </div>

            <div class="card-body">

                <form action="<?= BASE_URL ?>index.php" method="GET">
                    <input type="hidden" name="modulo" value="bitacora">

                    <div class="form-row">

                        <!-- Usuario -->
                        <div class="form-group col-md-3">
                            <label for="usuario">Usuario responsable</label>
                            <select id="usuario" name="usuario" class="form-control">
                                <option value="0">Todos los usuarios</option>
                                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                    <option value="<?= (int)$usuario['IdUsuario'] ?>" <?= $usuarioId === (int)$usuario['IdUsuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellidos'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>


                        <!-- Módulo -->
                        <div class="form-group col-md-3">
                            <label for="modulo_filtro">Módulo</label>
                            <select id="modulo_filtro" name="modulo_filtro" class="form-control">
                                <option value="0">Todos los módulos</option>
                                <?php while ($modulo = $modulos->fetch_assoc()): ?>
                                    <option value="<?= (int)$modulo['IdModulo'] ?>" <?= $moduloId === (int)$modulo['IdModulo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modulo['NombreModulo'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>


                        <!-- Acción -->
                        <div class="form-group col-md-3">
                            <label for="accion">Tipo de acción</label>
                            <select id="accion" name="accion" class="form-control">
                                <option value="">Todas las acciones</option>
                                <?php foreach (['Creación', 'Modificación', 'Desactivación', 'Eliminación lógica'] as $tipoAccion): ?>
                                    <option value="<?= htmlspecialchars($tipoAccion, ENT_QUOTES, 'UTF-8') ?>" <?= $accion === $tipoAccion ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipoAccion, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <!-- Botón -->
                        <div class="form-group col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block">
                                Consultar bitácora
                            </button>
                        </div>

                    </div>

                    <!-- FECHAS -->
                    <div class="form-row">

                        <div class="form-group col-md-3">
                            <label for="fechaInicio">Fecha inicial</label>
                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" value="<?= htmlspecialchars($fechaInicio, ENT_QUOTES, 'UTF-8') ?>">
                        </div>


                        <div class="form-group col-md-3">
                            <label for="fechaFin">Fecha final</label>
                            <input type="date" id="fechaFin" name="fechaFin" class="form-control" value="<?= htmlspecialchars($fechaFin, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                    </div>

                </form>

            </div>

        </div>


        <!-- TABLA -->
        <h3 class="mb-3">
            Registro de acciones
        </h3>

        <div class="table-responsive">

            <table class="table table-striped table-hover table-bordered">

                <thead class="thead-dark">

                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($registros && $registros->num_rows > 0): ?>
                        <?php while ($registro = $registros->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$registro['IdBitacora'] ?></td>
                                <td><?= htmlspecialchars($registro['Usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($registro['NombreModulo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($registro['TipoAccion'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($registro['FechaInicioBit'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($registro['FechaFinBit'] ?? 'En curso', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No hay acciones registradas con esos filtros.</td></tr>
                    <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- BOTONES -->
        <div class="mt-3 mb-5">

            <a href="#" class="btn btn-success" onclick="alert('Funcionalidad de exportación pendiente.')">
                Exportar bitácora
            </a>

            <a href="#" class="btn btn-primary" onclick="alert('Generación de PDF pendiente.')">
                Generar reporte
            </a>
            <br><br>
            <a href="<?= BASE_URL ?>index.php?modulo=dashboard" class="font-weight-bold">← Regresar al menú</a>
        </div>
        
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>