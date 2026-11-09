<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión y Registro de Campañas - PLAGUE</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; gap: 30px; }
        .page-container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); width: 100%; max-width: 1100px; box-sizing: border-box; }
        h1 { font-size: 1.6rem; color: #2c3e50; margin-top: 0; margin-bottom: 6px; }
        p.module-desc { font-size: 0.95rem; color: #666; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        h2 { font-size: 1.25rem; color: #2c3e50; margin-top: 25px; margin-bottom: 18px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 10px; }
        .full-width { grid-column: span 2; }
        label { display: block; font-size: 0.88rem; margin-bottom: 6px; color: #444; font-weight: 500; }
        input[type="text"], input[type="date"], select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.92rem; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; background-color: #fff; font-family: inherit; }
        input:focus, select:focus { border-color: #3498db; outline: none; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
        .button-group { display: flex; gap: 10px; margin-top: 15px; }
        .btn-submit { flex: 2; padding: 10px; background-color: #2c3e50; color: #ffffff; border: none; border-radius: 5px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #1a252f; }
        .btn-reset { flex: 1; padding: 10px; background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; border-radius: 5px; font-size: 0.95rem; cursor: pointer; transition: background-color 0.2s; }
        .btn-reset:hover { background-color: #dee2e6; }
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 15px; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left; white-space: nowrap; }
        th { background-color: #2c3e50; color: #ffffff; font-weight: 600; padding: 12px 14px; border-bottom: 2px solid #1a252f; }
        td { padding: 10px 14px; border-bottom: 1px solid #e9ecef; color: #444; vertical-align: middle; }
        td small { color: #6c757d; font-size: 0.78rem; }
        tbody tr:hover { background-color: #f8f9fa; }
        .action-buttons { display: flex; gap: 6px; }
        .btn { padding: 6px 10px; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; text-decoration: none; display: inline-block; }
        .btn-edit { background-color: #3498db; color: #ffffff; }
        .btn-edit:hover { background-color: #2980b9; }
        .btn-history { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .btn-history:hover { background-color: #dee2e6; color: #212529; }
        .btn-delete { background-color: #e74c3c; color: #ffffff; }
        .btn-delete:hover { background-color: #c0392b; }
        
        .header-link { width: 100%; max-width: 1100px; margin: 0 auto 10px; text-align: left; }
        .header-link a { color: #007bff; text-decoration: none; font-weight: 600; }
        .header-link a:hover { text-decoration: underline; }

        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>

    <div class="header-link">
        <?php
        //determinamos la URL de regreso según el rol del usuario
        if (isset($_SESSION['rol'])){
            if ($_SESSION['rol'] === 'Administrador') {
                echo '<a href="index.php?modulo=admin_dashboard">← Volver al Panel de Administración</a>';
            } elseif ($_SESSION['rol'] === 'Supervisor') {
                echo '<a href="index.php?modulo=super_dashboard">← Volver al Panel de Supervisor</a>';
            }
        }
        ?>
        <!--<a href="index.php?modulo=dashboard">← Volver al Panel Principal</a> -->
    </div>

    <div class="page-container">
        <h1>Gestión y Registro de Campañas</h1>
        <p class="module-desc">Módulo para dar de alta nuevas campañas de fumigación/inspección, asignarles una zona operativa y consultar su estado general.</p>

        <h2><?= $campanaEditar ? 'Modificar Campaña: ' . htmlspecialchars($campanaEditar['CodigoCampana']) : 'Registrar / Programar Nueva Campaña' ?></h2>
        
        <!-- Mensaje de éxito/error -->
        <?= $mensaje ?>

        <!-- Formulario configurado para el Front Controller -->
        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="campanas">

            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($campanaEditar): ?>
                <input type="hidden" name="id_campana" value="<?= $campanaEditar['IdCampana'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="codigo_campana">Código de Campaña:</label>
                    <input type="text" id="codigo_campana" name="codigo_campana" placeholder="Ej. CAMP-2026-10" 
                           value="<?= $campanaEditar ? htmlspecialchars($campanaEditar['CodigoCampana']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre_campana">Nombre de la Campaña:</label>
                    <input type="text" id="nombre_campana" name="nombre_campana" placeholder="Ej. Prevención Dengue Octubre" 
                           value="<?= $campanaEditar ? htmlspecialchars($campanaEditar['NombreCampana']) : '' ?>" required>
                </div>
                <div class="form-group full-width">
                    <label for="id_zona">Zona a intervenir:</label>
                    <select id="id_zona" name="id_zona" required>
                        <option value="" disabled <?= !$campanaEditar ? 'selected' : '' ?>>Seleccione la zona o colonia...</option>
                        <?php
                        // Mostramos zonas, si estamos editando y la zona ya no está "Activa" igual debemos permitir que se vea preseleccionada.
                        if ($resultadoZonas && $resultadoZonas->num_rows > 0) {
                            while ($zona = $resultadoZonas->fetch_assoc()) {
                                // Solo mostramos activas o la que ya estaba seleccionada
                                if ($zona['Estado'] === 'Activa' || ($campanaEditar && $campanaEditar['IdZona'] == $zona['IdZona'])) {
                                    $idZona = $zona['IdZona'];
                                    $nombreZona = htmlspecialchars($zona['CodigoZona'] . " - " . $zona['NombreZona']);
                                    $selected = ($campanaEditar && $campanaEditar['IdZona'] == $idZona) ? 'selected' : '';
                                    echo "<option value='{$idZona}' {$selected}>{$nombreZona}</option>";
                                }
                            }
                        } else {
                            echo "<option value='' disabled>No hay zonas registradas. Registre una zona primero.</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                           value="<?= $campanaEditar ? htmlspecialchars($campanaEditar['FechaInicio']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin (Opcional):</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" 
                           value="<?= $campanaEditar ? htmlspecialchars($campanaEditar['FechaFin'] ?? '') : '' ?>">
                </div>
                
                <div class="form-group full-width">
                    <label for="estado_campana">Estado de la Campaña:</label>
                    <select id="estado_campana" name="estado_campana" required>
                        <option value="Programada" <?= ($campanaEditar && $campanaEditar['Estado'] == 'Programada') || !$campanaEditar ? 'selected' : '' ?>>Programada</option>
                        <option value="En Curso" <?= ($campanaEditar && $campanaEditar['Estado'] == 'En Curso') ? 'selected' : '' ?>>En Curso</option>
                        <option value="Finalizada" <?= ($campanaEditar && $campanaEditar['Estado'] == 'Finalizada') ? 'selected' : '' ?>>Finalizada</option>
                        <option value="Cancelada" <?= ($campanaEditar && $campanaEditar['Estado'] == 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $campanaEditar ? 'Guardar Cambios' : 'Guardar Campaña' ?></button>
                <?php if ($campanaEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=campanas" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar Formulario</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Campañas Registradas</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Campaña</th>
                        <th>Zona Asignada</th>
                        <th>Periodo (Inicio - Fin)</th>
                        <th>Estado Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Recorremos las campañas para dibujarlas en la tabla
                    if ($resultadoCampanas && $resultadoCampanas->num_rows > 0) {
                        while ($fila = $resultadoCampanas->fetch_assoc()) {
                            $idCampana = $fila['IdCampana'];
                            $codigo = htmlspecialchars($fila['CodigoCampana']);
                            $nombre = htmlspecialchars($fila['NombreCampana']);
                            $zona = htmlspecialchars($fila['CodigoZona'] . " " . $fila['NombreZona']);
                            
                            $fInicio = date("d/m/Y", strtotime($fila['FechaInicio']));
                            $fFin = !empty($fila['FechaFin']) ? date("d/m/Y", strtotime($fila['FechaFin'])) : "Sin definir";
                            
                            $estado = htmlspecialchars($fila['Estado']);

                            echo "<tr>
                                    <td><strong>{$codigo}</strong></td>
                                    <td>{$nombre}</td>
                                    <td>{$zona}</td>
                                    <td><small>De:</small> {$fInicio} <br><small>A:</small> {$fFin}</td>
                                    <td>{$estado}</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <a href='" . BASE_URL . "index.php?modulo=campanas&accion=historial&id_campana={$codigo}' class='btn btn-history'>Ver Actividades</a>
                                            <a href='" . BASE_URL . "index.php?modulo=campanas&accion=editar&id={$idCampana}' class='btn btn-edit'>Editar</a>
                                            <a href='" . BASE_URL . "index.php?modulo=campanas&accion=eliminar&id={$idCampana}' class='btn btn-delete' onclick=\"return confirm('¡ADVERTENCIA! ¿Estás seguro de eliminar la campaña {$codigo}? Esto borrará también TODAS las actividades, inspecciones y registros vinculados a esta campaña. Esta acción NO se puede deshacer.');\">Eliminar</a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay campañas registradas en el sistema.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>