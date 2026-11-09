<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Personal Técnico - PLAGUE</title>
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
        input[type="text"], input[type="tel"], select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 0.92rem; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; background-color: #fff; font-family: inherit; }
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
        <h1>RF_11 Gestión del Personal Técnico</h1>
        <p class="module-desc">Módulo para registrar, administrar y consultar la información de los técnicos encargados de realizar las inspecciones y aplicar los tratamientos de fumigación.</p>

        <h2><?= $tecnicoEditar ? 'Modificar Datos del Técnico' : 'Registrar Técnico' ?></h2>
        
        <?= $mensaje ?>

        <!-- Apuntamos a index.php enviando por POST con campo oculto -->
        <form action="<?= BASE_URL ?>index.php" method="POST">
            <input type="hidden" name="modulo" value="tecnicos">

            <!-- Campo oculto para identificar si es actualización -->
            <?php if ($tecnicoEditar): ?>
                <input type="hidden" name="id_tecnico" value="<?= $tecnicoEditar['IdTecnico'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label for="id_usuario">Empleado asignado:</label>
                    <?php if ($tecnicoEditar): ?>
                        <!-- Al editar, no permitimos cambiar el usuario vinculado -->
                        <select id="id_usuario_disabled" name="id_usuario_disabled" disabled>
                            <option value="<?= $tecnicoEditar['IdUsuario'] ?>" selected>
                                <?= htmlspecialchars($tecnicoEditar['Nombre'] . ' ' . $tecnicoEditar['Apellidos']) ?>
                            </option>
                        </select>
                        <small style="color: #666; display: block; margin-top: 5px;">*El usuario asignado no se puede modificar. Si cometiste un error, elimina este registro y crea uno nuevo.</small>
                    <?php else: ?>
                        <!-- Al crear, mostramos los disponibles -->
                        <select id="id_usuario" name="id_usuario" required>
                            <option value="" disabled selected>Seleccione un usuario...</option>
                            <?php
                            if ($resultadoUsuarios && $resultadoUsuarios->num_rows > 0) {
                                while ($usr = $resultadoUsuarios->fetch_assoc()) {
                                    $id = $usr['IdUsuario'];
                                    $nombreCompleto = htmlspecialchars($usr['Nombre'] . ' ' . $usr['Apellidos']);
                                    echo "<option value='{$id}'>{$nombreCompleto}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No hay usuarios disponibles para asignar como técnico</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="numero_empleado">Número de Empleado:</label>
                    <input type="text" id="numero_empleado" name="numero_empleado" placeholder="Ej. TEC-045" 
                           value="<?= $tecnicoEditar ? htmlspecialchars($tecnicoEditar['NumeroEmpleado']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="especialidad">Especialidad o Función Principal:</label>
                    <select id="especialidad" name="especialidad" required>
                        <option value="" disabled <?= !$tecnicoEditar ? 'selected' : '' ?>>Selecciona una función...</option>
                        <option value="Inspección" <?= ($tecnicoEditar && $tecnicoEditar['Especialidad'] == 'Inspección') ? 'selected' : '' ?>>Inspector (Diagnóstico)</option>
                        <option value="Aplicación" <?= ($tecnicoEditar && $tecnicoEditar['Especialidad'] == 'Aplicación') ? 'selected' : '' ?>>Aplicador (Fumigación)</option>
                        <option value="Ambas" <?= ($tecnicoEditar && $tecnicoEditar['Especialidad'] == 'Ambas') ? 'selected' : '' ?>>Ambas funciones</option>
                    </select>
                </div>

            </div>

            <div class="button-group">
                <button type="submit" class="btn-submit"><?= $tecnicoEditar ? 'Guardar Cambios' : 'Guardar Técnico' ?></button>
                <?php if ($tecnicoEditar): ?>
                    <a href="<?= BASE_URL ?>index.php?modulo=tecnicos" class="btn-reset" style="text-align: center; text-decoration: none; display: inline-block;">Cancelar</a>
                <?php else: ?>
                    <button type="reset" class="btn-reset">Limpiar Datos</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Consulta y Administración de Técnicos</h2>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th>Registro de Actividades</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultadoTecnicos && $resultadoTecnicos->num_rows > 0) {
                        while ($fila = $resultadoTecnicos->fetch_assoc()) {
                            $idTecnico = htmlspecialchars($fila['IdTecnico']);
                            $noEmpleado = htmlspecialchars($fila['NumeroEmpleado']);
                            $nombreCompleto = htmlspecialchars($fila['Nombre'] . " " . $fila['Apellidos']);
                            $telefono = htmlspecialchars($fila['Contacto']);
                            $especialidad = htmlspecialchars($fila['Especialidad']);
                            $estado = htmlspecialchars($fila['Estado']); // Estado viene de la tabla Usuarios

                            $selectedActivo = ($estado == 'Activo') ? 'selected' : '';
                            $selectedInactivo = ($estado == 'Inactivo') ? 'selected' : '';

                            echo "<tr>
                                    <td><strong>{$noEmpleado}</strong></td>
                                    <td>{$nombreCompleto}</td>
                                    <td>{$telefono}</td>
                                    <td>{$especialidad}</td>
                                    <td>
                                        <select name='estado_tec_{$idTecnico}' disabled title='El estado general se cambia desde Gestión de Usuarios'>
                                            <option value='Activo' {$selectedActivo}>Activo (En campo)</option>
                                            <option value='Inactivo' {$selectedInactivo}>Inactivo (Vacaciones/Baja)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class='action-buttons'>
                                            <button type='button' class='btn btn-history'>Ver historial</button>
                                            <a href='" . BASE_URL . "index.php?modulo=tecnicos&accion=editar&id={$idTecnico}' class='btn btn-edit'>Editar</a>
                                            <a href='" . BASE_URL . "index.php?modulo=tecnicos&accion=eliminar&id={$idTecnico}' class='btn btn-delete' onclick=\"return confirm('¿Estás seguro de que deseas eliminar a este técnico? Esto NO borrará su usuario del sistema, solo su perfil técnico.');\">Eliminar</a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No hay técnicos registrados aún.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>