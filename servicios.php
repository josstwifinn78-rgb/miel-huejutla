<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/mesa_ayuda.php';

$page = 'services';
$title = 'Catálogo de servicios';
$description = 'Servicios que ofrece la plataforma de Miel de Huejutla: venta en línea, seguimiento de pedidos, mesa de ayuda y tiempos de atención.';
$notice = '';
$errors = [];

$servicios = [
    ['Venta en línea de productos de la colmena', 'Catálogo, carrito y registro de pedidos de miel, propóleo y derivados.', 'Administrador del sitio', 'Sitio web', 'Disponibilidad objetivo: 99 % mensual'],
    ['Seguimiento y confirmación de pedidos', 'Validación de existencias y contacto para confirmar pago y entrega.', 'Atención a clientes', 'Sitio web, correo, WhatsApp', 'Confirmación en 1 día hábil'],
    ['Atención de incidentes', 'Registro y resolución de fallas o problemas reportados por los usuarios.', 'Mesa de ayuda', 'Formulario de ayuda', 'Primera respuesta según prioridad'],
    ['Solicitudes de servicio', 'Aclaraciones, cambios en un pedido o información de productos.', 'Mesa de ayuda', 'Formulario de ayuda', 'Primera respuesta: 1 a 2 días hábiles'],
    ['Contenido informativo', 'Artículos del blog sobre origen, conservación y uso de la miel.', 'Responsable de contenido', 'Blog', 'Publicación mensual'],
    ['Monitoreo y mejora continua', 'Medición de visitas, rendimiento y disponibilidad para proponer mejoras.', 'Responsable de DevOps', 'Analytics, Search Console, monitoreo', 'Reporte periódico'],
];
$prioridadesDesc = [
    'P1 Crítica' => 'La tienda no funciona o no se pueden registrar pedidos.',
    'P2 Alta' => 'Falla importante que afecta a varios usuarios.',
    'P3 Media' => 'Falla parcial con alternativa disponible.',
    'P4 Baja' => 'Duda, mejora o problema menor.',
];

include __DIR__ . '/includes/header.php';
?>
<main class="page">
  <span class="eyebrow">Gestión de servicios de TI</span>
  <h1>Catálogo de servicios</h1>
  <p class="lead">La plataforma de Miel de Huejutla ofrece estos servicios, con responsable, canal y tiempo de atención definidos.</p>

  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Servicio</th><th>Descripción</th><th>Responsable</th><th>Canal</th><th>Objetivo</th></tr></thead>
      <tbody>
      <?php foreach ($servicios as $s): ?>
        <tr><td><strong><?=e($s[0])?></strong></td><td><?=e($s[1])?></td><td><?=e($s[2])?></td><td><?=e($s[3])?></td><td><?=e($s[4])?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2>Tiempos de atención para incidentes</h2>
  <p>La prioridad se calcula con el impacto y la urgencia que indica el usuario al abrir su ticket.</p>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Prioridad</th><th>Cuándo aplica</th><th>Primera respuesta</th></tr></thead>
      <tbody>
      <?php foreach (TICKET_SLA as $prioridad => $tiempo): ?>
        <tr><td><strong><?=e($prioridad)?></strong></td><td><?=e($prioridadesDesc[$prioridad] ?? '')?></td><td><?=e($tiempo)?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <a class="button" href="ayuda">Ir a la mesa de ayuda</a>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
