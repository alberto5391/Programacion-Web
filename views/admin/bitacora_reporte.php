<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de bitácora - PLAGUE</title>
    <style>
        body { margin: 0; padding: 2rem; color: #212529; font-family: Arial, sans-serif; }
        .report-header { border-bottom: 2px solid #343a40; margin-bottom: 1.5rem; padding-bottom: 1rem; }
        .filters { background: #f5f6f7; border: 1px solid #dee2e6; margin-bottom: 1.5rem; padding: 1rem; }
        .actions { margin-bottom: 1.5rem; }
        .actions a, .actions button { display: inline-block; margin-right: .5rem; padding: .5rem .75rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #adb5bd; padding: .5rem; text-align: left; }
        th { background: #e9ecef; }
        .footer { color: #6c757d; font-size: .85rem; margin-top: 1.5rem; }
        @page { size: landscape; margin: 15mm; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="actions no-print">
        <button type="button" onclick="window.print()">Imprimir / Guardar como PDF</button>
        <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>">Volver a la bitácora</a>
    </div>

    <header class="report-header">
        <h1>Reporte de bitácora de auditoría</h1>
        <p>PLAGUE · Generado el <?= htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') ?></p>
        <p>Registros incluidos: <?= count($registros) ?></p>
    </header>

    <section class="filters" aria-label="Filtros aplicados">
        <strong>Filtros aplicados</strong>
        <div>Usuario: <?= htmlspecialchars($usuarioSeleccionado, ENT_QUOTES, 'UTF-8') ?></div>
        <div>Módulo: <?= htmlspecialchars($moduloSeleccionado, ENT_QUOTES, 'UTF-8') ?></div>
        <div>Acción: <?= htmlspecialchars($accion !== '' ? $accion : 'Todas las acciones', ENT_QUOTES, 'UTF-8') ?></div>
        <div>Periodo: <?= htmlspecialchars($fechaInicio !== '' ? $fechaInicio : 'Sin inicio', ENT_QUOTES, 'UTF-8') ?> a <?= htmlspecialchars($fechaFin !== '' ? $fechaFin : 'Sin fin', ENT_QUOTES, 'UTF-8') ?></div>
    </section>

    <table>
        <thead>
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
            <?php if ($registros): ?>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?= (int) $registro['IdBitacora'] ?></td>
                        <td><?= htmlspecialchars($registro['Usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registro['NombreModulo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registro['TipoAccion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registro['FechaInicioBit'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registro['FechaFinBit'] ?? 'En curso', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No hay acciones registradas con esos filtros.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <footer class="footer">Reporte generado desde la bitácora del sistema PLAGUE.</footer>
</body>
</html>
