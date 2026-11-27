<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLAGUE - Incidencias</title>
    <style>

        :root {
            --fondo: #f8f9fa;
            --blanco: #ffffff;
            --texto: #212529;
            --secundario: #637080;
            --borde: #dee2e6;
            --cabecera-tabla: #e9ecef;
            --azul: #007bff;
            --azul-hover: #0069d9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--fondo);
            color: var(--texto);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.5;
        }
        .contenedor { width: min(1120px, calc(100% - 32px)); margin: 28px auto 44px; }
        .encabezado-pagina { margin-bottom: 20px; }
        .encabezado-pagina h1 { font-size: 25px; line-height: 1.25; margin: 0 0 7px; font-weight: 700; }
        .encabezado-pagina p { color: var(--secundario); margin: 0; }
        .tarjeta {
            background: var(--blanco);
            border: 1px solid #dddddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,.06);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .tarjeta-titulo {
            padding: 13px 20px;
            margin: 0;
            border-bottom: 1px solid var(--borde);
            font-size: 16px;
            font-weight: 700;
            background: #fff;
        }
        .tarjeta-cuerpo { padding: 20px; }
        .texto-ayuda { margin: 0 0 18px; color: var(--secundario); }
        .campo { margin-bottom: 18px; }
        .campo label { display: block; margin-bottom: 8px; }
        .control {
            display: block;
            width: 100%;
            min-height: 39px;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background: #fff;
            color: #343a40;
            font: inherit;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        textarea.control { resize: vertical; min-height: 125px; }
        input[type="file"].control { padding: 7px 10px; }
        .miniatura { max-width: 120px; max-height: 90px; border-radius: 4px; border: 1px solid var(--borde); display: inline-block; margin: 6px 0; }
        .sin-foto { color: var(--secundario); font-size: 13px; }
        .control::placeholder { color: #697a8e; opacity: 1; }
        .control:focus { outline: none; border-color: #80bdff; box-shadow: 0 0 0 3px rgba(0,123,255,.16); }
        .boton {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 38px;
            padding: 8px 13px;
            background: var(--azul);
            border: 1px solid var(--azul);
            border-radius: 4px;
            color: #fff;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }
        .boton:hover { background: var(--azul-hover); border-color: #0062cc; }
        .boton:focus-visible, .enlace-regreso:focus-visible, .enlace-mapa:focus-visible {
            outline: 3px solid #80bdff; outline-offset: 2px;
        }
        .formulario-busqueda { display: flex; align-items: flex-end; gap: 12px; }
        .formulario-busqueda .campo { margin: 0; flex: 1; min-width: 0; }
        .tabla-scroll { width: 100%; overflow-x: auto; }
        .tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            text-align: left;
        }
        .tabla th, .tabla td { border: 1px solid #d9dfe5; padding: 12px; vertical-align: middle; }
        .tabla th { background: var(--cabecera-tabla); font-weight: 700; color: #374151; }
        .tabla tbody tr:nth-child(even) { background: #f0f0f0; }
        .etiqueta { display: inline-block; padding: 2px 7px; border-radius: 4px; color: #fff; font-size: 12px; font-weight: 700; line-height: 1.3; }
        .etiqueta-alta { background: #dc3545; }
        .mensaje { margin: 0 0 16px; padding: 10px 12px; border-radius: 4px; background: #e8f4fd; color: #174a70; }
        .rejilla-reportes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .rejilla-reportes .tarjeta { margin-bottom: 0; }
        .seccion-titulo { font-size: 20px; font-weight: 700; margin: 30px 0 14px; }
        .multimedia { width: 100%; display: block; max-height: 390px; object-fit: contain; }
        .marco-mapa { width: 100%; height: 340px; display: block; border: 1px solid var(--borde); border-radius: 4px; }
        .enlace-mapa { display: inline-block; margin-top: 12px; color: #0069d9; text-decoration: none; }
        .enlace-mapa:hover, .enlace-regreso:hover { text-decoration: underline; }
        .pie-pagina { margin-top: 6px; }
        .enlace-regreso { color: #0069d9; text-decoration: none; font-weight: 500; }
        @media (max-width: 760px) {
            .contenedor { width: min(100% - 24px, 1120px); margin-top: 20px; }
            .encabezado-pagina h1 { font-size: 22px; }
            .rejilla-reportes { grid-template-columns: 1fr; }
            .tarjeta-cuerpo { padding: 16px; }
            .tarjeta-titulo { padding: 12px 16px; }
            .formulario-busqueda { flex-direction: column; align-items: stretch; }
            .formulario-busqueda .boton { align-self: flex-start; }
            .tabla { min-width: 430px; }
        }
    </style>
</head>
<body>
    <main class="contenedor">
        <header class="encabezado-pagina">
            <h1>Registro de Incidencias</h1>
            <p>Registro y consulta de incidencias relacionadas con campañas.</p>
        </header>

        <?php if ($mensaje !== ''): ?>
            <p class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (appCan('incidencias', $incidenciaEditar ? 'modificar' : 'registrar')): ?>
        <section class="tarjeta" aria-labelledby="titulo-registro">
            <h2 class="tarjeta-titulo" id="titulo-registro"><?= $incidenciaEditar ? 'Modificar Incidencia' : 'Registrar Nueva Incidencia' ?></h2>
            <div class="tarjeta-cuerpo">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modulo" value="incidencias">
                    <input type="hidden" name="accion" value="guardar_incidencia">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($incidenciaEditar): ?>
                        <input type="hidden" name="id_incidencia" value="<?= (int)$incidenciaEditar['IdIncidencia'] ?>">
                    <?php endif; ?>
                    <div class="campo">
                        <label for="id_campana">Campaña:</label>
                        <select class="control" id="id_campana" name="id_campana" required>
                            <option value="">Selecciona una campaña</option>
                            <?php while ($campana = $resultadoCampanas->fetch_assoc()): ?>
                                <option value="<?= (int)$campana['IdCampana'] ?>" <?= $incidenciaEditar && (int)$incidenciaEditar['IdCampana'] === (int)$campana['IdCampana'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($campana['CodigoCampana'] . ' — ' . $campana['NombreCampana'] . ' (' . $campana['NombreZona'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="actividad">Actividad relacionada:</label>
                        <select class="control" id="actividad" name="actividad" required>
                            <?php foreach (['Inspección', 'Aplicación', 'Seguimiento'] as $actividad): ?>
                                <option value="<?= htmlspecialchars($actividad, ENT_QUOTES, 'UTF-8') ?>" <?= $incidenciaEditar && $incidenciaEditar['ActividadRelacionada'] === $actividad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($actividad, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="id_tipo_incidencia">Tipo de incidencia:</label>
                        <select class="control" id="id_tipo_incidencia" name="id_tipo_incidencia" required>
                            <option value="">Selecciona un tipo</option>
                            <?php foreach ($tiposIncidencia as $tipoIncidencia): ?>
                                <option value="<?= (int)$tipoIncidencia['IdTipoIncidencia'] ?>" <?= $incidenciaEditar && (int)($incidenciaEditar['IdTipoIncidencia'] ?? 0) === (int)$tipoIncidencia['IdTipoIncidencia'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipoIncidencia['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="detalle">Detalle de la incidencia:</label>
                        <textarea class="control" id="detalle" name="detalle" rows="5" placeholder="Describa la incidencia detectada" required><?= $incidenciaEditar ? htmlspecialchars($incidenciaEditar['Descripcion'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                    </div>
                    <div class="campo">
                        <label for="foto">Fotografía de la incidencia (opcional; JPEG, PNG o WebP; máximo 5 MB):</label>
                        <?php if ($incidenciaEditar && !empty($incidenciaEditar['RutaFoto'])): ?>
                            <?php $rutaFotoActual = BASE_URL . ltrim($incidenciaEditar['RutaFoto'], '/\\'); ?>
                            <p class="texto-ayuda">
                                Fotografía actual:<br>
                                <a href="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <img class="miniatura" src="<?= htmlspecialchars($rutaFotoActual, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía actual de la incidencia">
                                </a><br>
                                Si eliges otro archivo, reemplazará a esta fotografía.
                            </p>
                        <?php endif; ?>
                        <input class="control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <button class="boton" type="submit"><?= $incidenciaEditar ? 'Guardar cambios' : 'Registrar incidencia' ?></button>
                </form>
            </div>
        </section>
        <?php endif; ?>

        <section class="tarjeta" aria-labelledby="titulo-consulta">
            <h2 class="tarjeta-titulo" id="titulo-consulta">Incidencias registradas</h2>
            <div class="tarjeta-cuerpo tabla-scroll">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Campaña</th>
                            <th>Zona</th>
                            <th>Actividad</th>
                            <th>Tipo</th>
                            <th>Detalle</th>
                            <th>Fotografía</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultadoIncidencias && $resultadoIncidencias->num_rows > 0): ?>
                            <?php while ($incidencia = $resultadoIncidencias->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($incidencia['CodigoCampana'] . ' — ' . $incidencia['NombreCampana'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($incidencia['Zona'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($incidencia['ActividadRelacionada'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($incidencia['TipoIncidencia'] ?? 'Sin clasificar', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($incidencia['Descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($incidencia['RutaFoto'])): ?>
                                            <?php $rutaFoto = BASE_URL . ltrim($incidencia['RutaFoto'], '/\\'); ?>
                                            <a href="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                                <img class="miniatura" src="<?= htmlspecialchars($rutaFoto, ENT_QUOTES, 'UTF-8') ?>" alt="Fotografía de la incidencia">
                                            </a>
                                        <?php else: ?>
                                            <span class="sin-foto">Sin fotografía</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($incidencia['FechaRegistro'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (appCan('incidencias', 'modificar')): ?>
                                            <a class="boton" href="index.php?modulo=incidencias&accion=editar&id=<?= (int)$incidencia['IdIncidencia'] ?>">Editar</a>
                                        <?php endif; ?>
                                        <?php if (appCan('incidencias', 'eliminar')): ?>
                                            <form action="index.php" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar esta incidencia?');">
                                                <input type="hidden" name="modulo" value="incidencias">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?= (int)$incidencia['IdIncidencia'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                                <button class="boton" type="submit">Eliminar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8">Todavía no hay incidencias registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="pie-pagina">
            <?php
            if (isset($_SESSION['rol'])) {
                if ($_SESSION['rol'] === 'Administrador') {
                    echo '<a class="enlace-regreso" href="index.php?modulo=dashboard">← Volver al menú de administración</a>';
                } elseif ($_SESSION['rol'] === 'Supervisor') {
                    echo '<a class="enlace-regreso" href="index.php?modulo=super_dashboard">← Volver al menú de supervisor</a>';
                } elseif ($_SESSION['rol'] === 'Tecnico') {
                    echo '<a class="enlace-regreso" href="index.php?modulo=tecnico_dashboard">← Volver al menú técnico</a>';
                }
            }
            ?>
            <!-- <a class="enlace-regreso" href="index.php?modulo=super_dashboard">← Volver al menu</a> -->
        </footer>
    </main>
</body>
</html>
