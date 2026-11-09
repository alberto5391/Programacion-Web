<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Catálogo de Plagas - PLAGUE</title>
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
        input[type="text"], textarea, select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.92rem; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; background-color: #fff; font-family: inherit; }
        textarea { resize: vertical; min-height: 80px; }
        input:focus, textarea:focus, select:focus { border-color: #3498db; outline: none; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
        .button-group { display: flex; gap: 10px; margin-top: 15px; }
        .btn-submit { flex: 2; padding: 10px; background-color: #2c3e50; color: #ffffff; border: none; border-radius: 5px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #1a252f; }
        .btn-reset { flex: 1; padding: 10px; background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; border-radius: 5px; font-size: 0.95rem; cursor: pointer; transition: background-color 0.2s; }
        .btn-reset:hover { background-color: #dee2e6; }
        .table-responsive { width: 100%; overflow-x: auto; margin-top: 15px; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left; white-space: nowrap; }
        th { background-color: #2c3e50; color: #ffffff; font-weight: 600; padding: 12px 14px; border-bottom: 2px solid #1a252f; }
        td { padding: 10px 14px; border-bottom: 1px solid #e9ecef; color: #444; vertical-align: middle; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        tbody tr:hover { background-color: #f8f9fa; }
        .action-buttons { display: flex; gap: 6px; }
        .btn { padding: 6px 10px; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; text-decoration: none; display: inline-block; }
        .btn-details { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .btn-details:hover { background-color: #dee2e6; color: #212529; }
        .btn-edit { background-color: #3498db; color: #ffffff; }
        .btn-edit:hover { background-color: #2980b9; }
        .btn-delete { background-color: #e74c3c; color: #ffffff; }
        .btn-delete:hover { background-color: #c0392b; }
        
        /* Enlace de regreso */
        .header-link { width: 100%; max-width: 1100px; margin: 0 auto 10px; text-align: left; }
        .header-link a { color: #007bff; text-decoration: none; font-weight: 600; }
        .header-link a:hover { text-decoration: underline; }

        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>

    <div class="header-link">
        <?php 
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador') {
            echo '<a href="index.php?modulo=dashboard">← Volver al Panel de Administración</a>';
        } else {
            echo '<a href="index.php?modulo=super_dashboard">← Volver al Panel de Supervisor</a>';
        }
        ?>
        <!-- <a href="index.php?modulo=dashboard">← Volver al Panel Principal</a> -->
    </div>

    <div class="page-container">
        <h1>Gestión del Catálogo de Plagas</h1>
        <p class="module-desc">Módulo para registrar, modificar, consultar y desactivar los diferentes tipos de plagas identificados durante las inspecciones.</p>

        <h2><?= $plagaEditar ? 'Modificar Tipo de Plaga: ' . htmlspecialchars($plagaEditar['CodigoPlaga']) : 'Registrar Tipo de Plaga' ?></h2>
        
        <!-- Mensaje de estado -->
        <?= $mensaje ?>

        <!-- Apuntamos a index.php con el módulo oculto -->
        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="plagas">

            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($plagaEditar): ?>
                <input type="hidden" name="id_plaga" value="<?= $plagaEditar['IdPlaga'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="codigo_plaga">Código de Plaga:</label>
                    <input type="text" id="codigo_plaga" name="codigo_plaga" placeholder="Ej. PLG-001" 
                           value="<?= $plagaEditar ? htmlspecialchars($plagaEditar['CodigoPlaga']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre_plaga">Nombre Común:</label>
                    <input type="text" id="nombre_plaga" name="nombre_plaga" placeholder="Ej. Mosquito del Dengue" 
                           value="<?= $plagaEditar ? htmlspecialchars($plagaEditar['NombreComun']) : '' ?>" required>
                </div>
                <div class="form-group full-width">
                    <label for="nombre_cientifico">Nombre Científico (Opcional):</label>
                    <input type="text" id="nombre_cientifico" name="nombre_cientifico" placeholder="Ej. Aedes aegypti" 
                           value="<?= $plagaEditar ? htmlspecialchars($plagaEditar['NombreCientifico'] ?? '') : '' ?>">
                </div>
                <div class="form-group full-width">
                    <label for="descripcion">Descripción y Características:</label>
                    <textarea id="descripcion" name="descripcion" rows="3" placeholder="Describe los detalles de la plaga, lugares donde se anida, daños que causa, etc." required><?= $plagaEditar ? htmlspecialchars($plagaEditar['Descripcion']) : '' ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label for="tratamiento">Tratamiento Recomendado:</label>
                    <textarea id="tratamiento" name="tratamiento" rows="2" placeholder="Ej. Fumigación con insecticida especial..."><?= $plagaEditar ? htmlspecialchars($plagaEditar['TratamientoRecomendado'] ?? '') : '' ?></textarea>
                </div>

                <!-- Mostrar campo de Estado solo si se está editando -->
                <?php if ($plagaEditar): ?>
                <div class="form-group">
                    <label for="estado">Estado de la Plaga:</label>
                    <select id="estado" name="estado">
                        <option value="Activa" <?= $plagaEditar['Estado'] === 'Activa' ? 'selected' : '' ?>>Activa</option>
                        <option value="Inactiva" <?= $plagaEditar['Estado'] === 'Inactiva' ? 'selected' : '' ?>>Inactiva (Desactivada)</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $plagaEditar ? 'Guardar Cambios' : 'Guardar Plaga' ?></button>
                <?php if ($plagaEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=plagas" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar Datos</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Consulta de Plagas Registradas</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Común</th>
                        <th>Nombre Científico</th>
                        <th>Descripción Breve</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Recorremos las plagas en la base de datos
                    if ($resultadoPlagas && $resultadoPlagas->num_rows > 0) {
                        while ($fila = $resultadoPlagas->fetch_assoc()) {
                            // Limpiamos los datos
                            $idPlaga = htmlspecialchars($fila['IdPlaga']);
                            $codigo = htmlspecialchars($fila['CodigoPlaga']);
                            $nombreComun = htmlspecialchars($fila['NombreComun']);
                            
                            $nombreCientifico = !empty($fila['NombreCientifico']) ? "<em>" . htmlspecialchars($fila['NombreCientifico']) . "</em>" : "N/A";
                            
                            $descripcion = htmlspecialchars($fila['Descripcion']);
                            $descripcionCorta = strlen($descripcion) > 50 ? substr($descripcion, 0, 50) . "..." : $descripcion;
                            
                            $estado = htmlspecialchars($fila['Estado']);

                            echo "<tr>
                                    <td><strong>{$codigo}</strong></td>
                                    <td>{$nombreComun}</td>
                                    <td>{$nombreCientifico}</td>
                                    <td title='{$descripcion}'>{$descripcionCorta}</td>
                                    <td>{$estado}</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <a href='" . BASE_URL . "index.php?modulo=plagas&accion=editar&id={$idPlaga}' class='btn btn-edit'>Editar</a>
                                            <a href='" . BASE_URL . "index.php?modulo=plagas&accion=eliminar&id={$idPlaga}' class='btn btn-delete' onclick=\"return confirm('¿Estás seguro de que deseas eliminar la plaga {$codigo}? Esta acción no se puede deshacer.');\">Eliminar</a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay plagas registradas en el catálogo.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>