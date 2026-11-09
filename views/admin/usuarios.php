<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - PLAGUE</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; gap: 30px; }
        .card-container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); width: 100%; box-sizing: border-box; }
        .form-card { max-width: 800px; margin: 0 auto; }
        .table-card { max-width: 1200px; margin: 0 auto; }
        h2 { font-size: 1.35rem; color: #2c3e50; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; }
        
        /* Estilos del Formulario */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .full-width { grid-column: span 2; }
        label { display: block; font-size: 0.88rem; margin-bottom: 6px; color: #444; font-weight: 500; }
        input[type="text"], input[type="tel"], input[type="email"], input[type="password"], select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.92rem; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; background-color: #fff; }
        input:focus, select:focus { border-color: #3498db; outline: none; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15); }
        .button-group { display: flex; gap: 10px; margin-top: 10px; }
        .btn-submit { flex: 2; padding: 10px; background-color: #2c3e50; color: #ffffff; border: none; border-radius: 5px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #1a252f; }
        .btn-reset { flex: 1; padding: 10px; background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; border-radius: 5px; font-size: 0.95rem; cursor: pointer; transition: background-color 0.2s; }
        
        /* Estilos de la Tabla */
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left; white-space: nowrap; }
        th { background-color: #2c3e50; color: #ffffff; font-weight: 600; padding: 12px 14px; border-bottom: 2px solid #1a252f; }
        td { padding: 10px 14px; border-bottom: 1px solid #e9ecef; color: #444; vertical-align: middle; }
        tbody tr:hover { background-color: #f8f9fa; }
        .action-buttons { display: flex; gap: 6px; align-items: center; }
        .btn { display: inline-block; padding: 6px 10px; border: none; border-radius: 4px; font-size: 0.8rem; font-weight: 500; cursor: pointer; text-decoration: none; text-align: center; }
        .btn-details { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .btn-edit { background-color: #3498db; color: #ffffff; }
        .btn-delete { background-color: #e74c3c; color: #ffffff; }
        .btn-delete:hover { background-color: #c0392b; }
        
        /* Header Regreso */
        .header-link { width: 100%; max-width: 1200px; margin: 0 auto 10px; text-align: left; }
        .header-link a { color: #007bff; text-decoration: none; font-weight: 600; }
        .header-link a:hover { text-decoration: underline; }

        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>

    <div class="header-link">
        <a href="<?= BASE_URL ?>index.php?modulo=dashboard">← Volver al Panel Principal</a>
    </div>

    <!-- SECCIÓN 1: REGISTRO/EDICIÓN DE USUARIOS -->
    <div class="card-container form-card">
        <h2><?= $usuarioEditar ? 'Modificar Usuario: ' . htmlspecialchars($usuarioEditar['Nombre']) . ' ' . htmlspecialchars($usuarioEditar['Apellidos']) : 'Registrar Nuevo Usuario' ?></h2>
        
        <!-- Mensaje desde el controlador -->
        <?= $mensaje ?>
        
        <!-- Apunta al controlador mediante método POST -->
        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="usuarios">
            
            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($usuarioEditar): ?>
                <input type="hidden" name="id_usuario" value="<?= $usuarioEditar['IdUsuario'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Nombre']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" placeholder="Ej. Pérez López" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Apellidos']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="contacto">Contacto:</label>
                    <input type="tel" id="contacto" name="contacto" placeholder="10 dígitos" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Contacto']) : '' ?>" required>
                </div>

                <!-- SELECT DINÁMICO DE ROLES -->
                <div class="form-group">
                    <label for="rol">Rol del usuario:</label>
                    <select id="rol" name="rol" required>
                        <option value="" disabled <?= !$usuarioEditar ? 'selected' : '' ?>>Selecciona un rol...</option>
                        <?php
                        if ($resultadoRoles && $resultadoRoles->num_rows > 0) {
                            while ($rol = $resultadoRoles->fetch_assoc()) {
                                $nombreRol = htmlspecialchars($rol['NombreRol']);
                                $selected = ($usuarioEditar && $usuarioEditar['NombreRol'] === $nombreRol) ? 'selected' : '';
                                echo "<option value='{$nombreRol}' {$selected}>{$nombreRol}</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No hay roles en la base de datos</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Correo']) : '' ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="password">Contraseña de acceso:</label>
                    <input type="password" id="password" name="password" 
                           placeholder="<?= $usuarioEditar ? 'Dejar en blanco para conservar la actual' : 'Mínimo 8 caracteres' ?>" 
                           <?= $usuarioEditar ? '' : 'required' ?>>
                </div>
                
                <div class="form-group">
                    <label for="curp">CURP:</label>
                    <input type="text" id="curp" name="curp" maxlength="18" placeholder="18 caracteres" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Curp']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc" maxlength="13" placeholder="12 o 13 caracteres" 
                           value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['Rfc']) : '' ?>" required>
                </div>

                <!-- Mostrar campo de Estado solo si se está editando -->
                <?php if ($usuarioEditar): ?>
                <div class="form-group full-width">
                    <label for="estado">Estado del Usuario:</label>
                    <select id="estado" name="estado">
                        <option value="Activo" <?= $usuarioEditar['Estado'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $usuarioEditar['Estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $usuarioEditar ? 'Guardar Cambios' : 'Registrar Usuario' ?></button>
                <?php if ($usuarioEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=usuarios" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- SECCIÓN 2: CONSULTA DE USUARIOS -->
    <div class="card-container table-card">
        <h2>Consulta de Usuarios Registrados</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Contacto</th>
                        <th>Correo Electrónico</th>
                        <th>CURP</th>
                        <th>RFC</th>
                        <th>Rol Asignado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultadoUsuarios && $resultadoUsuarios->num_rows > 0) {
                        while ($fila = $resultadoUsuarios->fetch_assoc()) {
                            $id = htmlspecialchars($fila['IdUsuario']);
                            $nombre = htmlspecialchars($fila['Nombre']);
                            $apellidos = htmlspecialchars($fila['Apellidos']);
                            $contacto = htmlspecialchars($fila['Contacto']);
                            $correo = htmlspecialchars($fila['Correo']);
                            $curp = htmlspecialchars($fila['Curp']);
                            $rfc = htmlspecialchars($fila['Rfc']);
                            $rol = htmlspecialchars($fila['NombreRol']);
                            $estado = htmlspecialchars($fila['Estado']);

                            echo "<tr>
                                    <td>{$nombre}</td>
                                    <td>{$apellidos}</td>
                                    <td>{$contacto}</td>
                                    <td>{$correo}</td>
                                    <td>{$curp}</td>
                                    <td>{$rfc}</td>
                                    <td>{$rol}</td>
                                    <td>{$estado}</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <a href='" . BASE_URL . "index.php?modulo=usuarios&accion=editar&id={$id}' class='btn btn-edit'>Editar</a>
                                            <a href='" . BASE_URL . "index.php?modulo=usuarios&accion=eliminar&id={$id}' class='btn btn-delete' onclick=\"return confirm('¿Estás seguro de eliminar a este usuario? Esta acción no se puede deshacer.');\">Eliminar</a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align: center; padding: 20px;'>No se encontraron usuarios registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>