<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Catálogo de Zonas - PLAGUE</title>
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
        td { padding: 10px 14px; border-bottom: 1px solid #e9ecef; color: #444; vertical-align: middle; }
        tbody tr:hover { background-color: #f8f9fa; }
        .btn-edit { padding: 6px 12px; background-color: #3498db; color: #ffffff; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; text-decoration: none; display: inline-block;}
        .btn-edit:hover { background-color: #2980b9; }
        .btn-delete { padding: 6px 12px; background-color: #e74c3c; color: #ffffff; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; text-decoration: none; display: inline-block; margin-left: 5px;}
        .btn-delete:hover { background-color: #c0392b; }
        .header-link { width: 100%; max-width: 1100px; margin: 0 auto 10px; text-align: left; }
        .header-link a { color: #007bff; text-decoration: none; font-weight: 600; }
        .header-link a:hover { text-decoration: underline; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>

    <div class="header-link">
        <a href="<?= BASE_URL ?>index.php?modulo=dashboard">← Volver al Panel Principal</a>
    </div>

    <div class="page-container">
        <h1>Gestión del Catálogo de Zonas</h1>
        <p class="module-desc">Módulo para registrar, modificar y desactivar zonas urbanas para inspecciones y campañas de fumigación.</p>

        <h2><?= $zonaEditar ? 'Modificar Zona: ' . htmlspecialchars($zonaEditar['CodigoZona']) : 'Registrar Zona' ?></h2>
        
        <!-- Bloque para mostrar errores o éxitos del registro provenientes del controlador -->
        <?= $mensaje ?>

        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="zonas">
            
            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($zonaEditar): ?>
                <input type="hidden" name="id_zona" value="<?= $zonaEditar['IdZona'] ?>">
            <?php endif; ?>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="codigo_zona">Código de Zona:</label>
                    <input type="text" id="codigo_zona" name="codigo_zona" placeholder="Ej. ZN-001" 
                           value="<?= $zonaEditar ? htmlspecialchars($zonaEditar['CodigoZona']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="municipio">Municipio / Localidad:</label>
                    <input type="text" id="municipio" name="municipio" placeholder="Ej. Centro" 
                           value="<?= $zonaEditar ? htmlspecialchars($zonaEditar['Municipio']) : '' ?>" required>
                </div>
                <div class="form-group full-width">
                    <label for="nombre_zona">Nombre de la Zona o Colonia:</label>
                    <input type="text" id="nombre_zona" name="nombre_zona" placeholder="Nombre de la zona urbana" 
                           value="<?= $zonaEditar ? htmlspecialchars($zonaEditar['NombreZona']) : '' ?>" required>
                </div>
                <div class="form-group full-width">
                    <label for="referencias">Referencias de Ubicación:</label>
                    <textarea id="referencias" name="referencias" rows="3" placeholder="Descripción para ubicar la zona..." required><?= $zonaEditar ? htmlspecialchars($zonaEditar['Referencias']) : '' ?></textarea>
                </div>
                
                <!-- Mostrar campo de Estado solo si se está editando -->
                <?php if ($zonaEditar): ?>
                <div class="form-group">
                    <label for="estado">Estado de la Zona:</label>
                    <select id="estado" name="estado">
                        <option value="Activa" <?= $zonaEditar['Estado'] === 'Activa' ? 'selected' : '' ?>>Activa</option>
                        <option value="Inactiva" <?= $zonaEditar['Estado'] === 'Inactiva' ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $zonaEditar ? 'Guardar Cambios' : 'Guardar Zona' ?></button>
                <?php if ($zonaEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=zonas" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar Datos</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Zonas Registradas</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre de la Zona</th>
                        <th>Municipio</th>
                        <th>Referencias</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultadoZonas && $resultadoZonas->num_rows > 0) {
                        while ($fila = $resultadoZonas->fetch_assoc()) {
                            $idZona = htmlspecialchars($fila['IdZona']);
                            $codigo = htmlspecialchars($fila['CodigoZona']);
                            $nombre = htmlspecialchars($fila['NombreZona']);
                            $municipio = htmlspecialchars($fila['Municipio']);
                            $referencias = htmlspecialchars($fila['Referencias']);
                            $estado = htmlspecialchars($fila['Estado']);

                            echo "<tr>
                                    <td><strong>{$codigo}</strong></td>
                                    <td>{$nombre}</td>
                                    <td>{$municipio}</td>
                                    <td>{$referencias}</td>
                                    <td>{$estado}</td>
                                    <td>
                                        <a href='" . BASE_URL . "index.php?modulo=zonas&accion=editar&id={$idZona}' class='btn-edit'>Editar</a>
                                        <a href='" . BASE_URL . "index.php?modulo=zonas&accion=eliminar&id={$idZona}' class='btn-delete' onclick=\"return confirm('¿Estás seguro de que deseas eliminar la zona {$codigo}? Esta acción no se puede deshacer.');\">Eliminar</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay zonas registradas aún.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>