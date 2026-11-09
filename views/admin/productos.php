<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Catálogo de Productos - PLAGUE</title>
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
        .action-buttons { display: flex; gap: 6px; }
        .btn { padding: 6px 10px; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; text-decoration: none; display: inline-block; }
        .btn-history { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .btn-history:hover { background-color: #dee2e6; color: #212529; }
        .btn-edit { background-color: #3498db; color: #ffffff; }
        .btn-edit:hover { background-color: #2980b9; }
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
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador') {
            echo '<a href="index.php?modulo=dashboard">← Volver al Panel de Administración</a>';
        } else {
            echo '<a href="index.php?modulo=super_dashboard">← Volver al Panel de Supervisor</a>';
        }
        ?>
        <!-- <a href="index.php?modulo=dashboard">← Volver al Panel Principal</a> -->
    </div>

    <div class="page-container">
        <h1>RF_10 y RF_17 Gestión del Catálogo de Productos</h1>
        <p class="module-desc">Módulo para registrar, administrar y consultar los productos químicos y materiales utilizados en los tratamientos de fumigación y campañas.</p>

        <h2><?= $productoEditar ? 'Modificar Producto: ' . htmlspecialchars($productoEditar['CodigoProducto']) : 'Registrar Producto' ?></h2>
        
        <!-- Mensaje de éxito o error -->
        <?= $mensaje ?>

        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="productos">

            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($productoEditar): ?>
                <input type="hidden" name="id_producto" value="<?= $productoEditar['IdProducto'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="codigo_producto">Código de Producto:</label>
                    <input type="text" id="codigo_producto" name="codigo_producto" placeholder="Ej. PRD-001" 
                           value="<?= $productoEditar ? htmlspecialchars($productoEditar['CodigoProducto']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="nombre_comercial">Nombre Comercial:</label>
                    <input type="text" id="nombre_comercial" name="nombre_comercial" placeholder="Ej. Biothrine WG 250" 
                           value="<?= $productoEditar ? htmlspecialchars($productoEditar['NombreComercial']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="ingrediente_activo">Ingrediente Activo:</label>
                    <input type="text" id="ingrediente_activo" name="ingrediente_activo" placeholder="Ej. Deltametrina al 25%" 
                           value="<?= $productoEditar ? htmlspecialchars($productoEditar['IngredienteActivo'] ?? '') : '' ?>">
                </div>
                <div class="form-group">
                    <label for="tipo_producto">Tipo de Producto:</label>
                    <select id="tipo_producto" name="tipo_producto" required>
                        <option value="" disabled <?= !$productoEditar ? 'selected' : '' ?>>Selecciona una categoría...</option>
                        <option value="Insecticida" <?= ($productoEditar && $productoEditar['TipoProducto'] == 'Insecticida') ? 'selected' : '' ?>>Insecticida</option>
                        <option value="Rodenticida" <?= ($productoEditar && $productoEditar['TipoProducto'] == 'Rodenticida') ? 'selected' : '' ?>>Rodenticida (Raticida)</option>
                        <option value="Larvicida" <?= ($productoEditar && $productoEditar['TipoProducto'] == 'Larvicida') ? 'selected' : '' ?>>Larvicida</option>
                        <option value="Repelente" <?= ($productoEditar && $productoEditar['TipoProducto'] == 'Repelente') ? 'selected' : '' ?>>Repelente</option>
                        <option value="Otro" <?= ($productoEditar && $productoEditar['TipoProducto'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="presentacion">Presentación y Dosis Recomendada:</label>
                    <textarea id="presentacion" name="presentacion" rows="3" placeholder="Ej. Bote de 1 Litro. Dosis: 10ml por litro de agua."><?= $productoEditar ? htmlspecialchars($productoEditar['PresentacionDosis'] ?? '') : '' ?></textarea>
                </div>

                <!-- Mostrar campo de Estado solo si se está editando -->
                <?php if ($productoEditar): ?>
                <div class="form-group">
                    <label for="estado">Estado (Stock):</label>
                    <select id="estado" name="estado">
                        <option value="Activo" <?= $productoEditar['Estado'] === 'Activo' ? 'selected' : '' ?>>Activo (Disponible)</option>
                        <option value="Inactivo" <?= $productoEditar['Estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo (Agotado/Descontinuado)</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $productoEditar ? 'Guardar Cambios' : 'Guardar Producto' ?></button>
                <?php if ($productoEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=productos" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar Datos</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Consulta y Administración de Productos</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Comercial</th>
                        <th>Tipo</th>
                        <th>Ingrediente Activo</th>
                        <th>Estado (Stock)</th>
                        <th>Acciones de Campaña</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultadoProductos && $resultadoProductos->num_rows > 0) {
                        while ($fila = $resultadoProductos->fetch_assoc()) {
                            $idProducto = htmlspecialchars($fila['IdProducto']);
                            $codigo = htmlspecialchars($fila['CodigoProducto']);
                            $nombre = htmlspecialchars($fila['NombreComercial']);
                            $tipo = ucfirst(htmlspecialchars($fila['TipoProducto'])); 
                            $ingrediente = !empty($fila['IngredienteActivo']) ? htmlspecialchars($fila['IngredienteActivo']) : "No especificado";
                            $estado = htmlspecialchars($fila['Estado']);

                            echo "<tr>
                                    <td><strong>{$codigo}</strong></td>
                                    <td>{$nombre}</td>
                                    <td>{$tipo}</td>
                                    <td>{$ingrediente}</td>
                                    <td>{$estado}</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <button type='button' class='btn btn-history'>Ver historial</button>
                                            <a href='" . BASE_URL . "index.php?modulo=productos&accion=editar&id={$idProducto}' class='btn btn-edit'>Editar</a>
                                            <a href='" . BASE_URL . "index.php?modulo=productos&accion=eliminar&id={$idProducto}' class='btn btn-delete' onclick=\"return confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');\">Eliminar</a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay productos registrados en el catálogo.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>