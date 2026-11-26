<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mesa_ayuda.php';

// El panel solo existe si defines ADMIN_PASS en config/config.php
if (!defined('ADMIN_PASS') || ADMIN_PASS === '') {
    http_response_code(404);
    exit('No disponible.');
}

$error = '';
$aviso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(400);
        $error = 'La sesión del formulario venció. Recarga la página.';
    } elseif (isset($_POST['login'])) {
        if (hash_equals((string) ADMIN_PASS, (string) ($_POST['password'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: tickets.php');
            exit;
        }
        usleep(700000);
        $error = 'Contraseña incorrecta.';
    } elseif (!empty($_SESSION['admin'])) {
        if (isset($_POST['logout'])) {
            unset($_SESSION['admin']);
            header('Location: tickets.php');
            exit;
        }
        if (isset($_POST['update_ticket'])) {
            $id = (int) ($_POST['ticket_id'] ?? 0);
            $estado = (string) ($_POST['estado'] ?? '');
            $comentario = trim((string) ($_POST['comentario'] ?? ''));
            if ($id < 1 || !in_array($estado, TICKET_ESTADOS, true) || mb_strlen($comentario) < 3 || mb_strlen($comentario) > 500) {
                $error = 'Revisa el estado y escribe un comentario de 3 a 500 caracteres.';
            } else {
                try {
                    $stmt = db()->prepare("UPDATE tickets SET estado = ?, fecha_resolucion = IF(? = 'Resuelto' AND fecha_resolucion IS NULL, NOW(), fecha_resolucion) WHERE id = ?");
                    $stmt->execute([$estado, $estado, $id]);
                    ticket_historial($id, $estado, $comentario, 'Soporte');
                    $aviso = 'Ticket actualizado.';
                } catch (Throwable $e) {
                    $error = 'No se pudo actualizar el ticket.';
                }
            }
        }
    }
}

$esAdmin = !empty($_SESSION['admin']);
$tickets = [];
$porEstado = [];
$porPrioridad = [];
$promedio = 'Sin datos';
$filtro = (string) ($_GET['estado'] ?? '');

if ($esAdmin) {
    try {
        if (in_array($filtro, TICKET_ESTADOS, true)) {
            $stmt = db()->prepare('SELECT * FROM tickets WHERE estado = ? ORDER BY fecha_creacion DESC LIMIT 100');
            $stmt->execute([$filtro]);
        } else {
            $stmt = db()->query('SELECT * FROM tickets ORDER BY fecha_creacion DESC LIMIT 100');
        }
        $tickets = $stmt->fetchAll();
        $porEstado = db()->query('SELECT estado, COUNT(*) AS total FROM tickets GROUP BY estado')->fetchAll();
        $porPrioridad = db()->query('SELECT prioridad, COUNT(*) AS total FROM tickets GROUP BY prioridad')->fetchAll();
        $min = db()->query('SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_resolucion)) FROM tickets WHERE fecha_resolucion IS NOT NULL')->fetchColumn();
        if ($min !== null && $min !== false) {
            $promedio = number_format(((float) $min) / 60, 1) . ' horas';
        }
    } catch (Throwable $e) {
        $error = 'No se pudo leer la base de datos. ¿Importaste database/itil_mesa_ayuda.sql?';
    }
}
?><!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Panel de tickets | Miel de Huejutla</title>
  <link rel="stylesheet" href="<?=e(SITE_URL)?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?=e(SITE_URL)?>/assets/css/mesa-ayuda.css">
</head>
<body>
<main class="page admin-tickets">
  <span class="eyebrow">Mesa de ayuda · Soporte</span>
  <h1>Panel de tickets</h1>
  <?php if ($error): ?><div class="notice error" role="alert"><?=e($error)?></div><?php endif; ?>
  <?php if ($aviso): ?><div class="notice" role="status"><?=e($aviso)?></div><?php endif; ?>

<?php if (!$esAdmin): ?>
  <form class="form" method="post" style="max-width:380px">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <label>Contraseña de soporte<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="button" name="login">Entrar</button>
  </form>
<?php else: ?>
  <form method="post" style="float:right"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="button button-outline" name="logout">Cerrar sesión</button></form>

  <h2>Resumen</h2>
  <div class="stat-row">
    <?php foreach ($porEstado as $fila): ?><div class="stat-box"><strong><?=e((string) $fila['total'])?></strong><?=e((string) $fila['estado'])?></div><?php endforeach; ?>
    <?php foreach ($porPrioridad as $fila): ?><div class="stat-box"><strong><?=e((string) $fila['total'])?></strong><?=e((string) $fila['prioridad'])?></div><?php endforeach; ?>
    <div class="stat-box"><strong><?=e($promedio)?></strong>Tiempo promedio de resolución</div>
  </div>

  <p>Filtrar:
    <a href="tickets.php">Todos</a>
    <?php foreach (TICKET_ESTADOS as $opt): ?> · <a href="tickets.php?estado=<?=e(rawurlencode($opt))?>"><?=e($opt)?></a><?php endforeach; ?>
  </p>

  <h2>Tickets (<?=e((string) count($tickets))?>)</h2>
  <?php foreach ($tickets as $t): ?>
  <details>
    <summary><?=e((string) $t['folio'])?> · <?=e((string) $t['asunto'])?> · <span class="badge badge-estado"><?=e((string) $t['estado'])?></span><span class="badge"><?=e((string) $t['prioridad'])?></span></summary>
    <p><small><?=e((string) $t['tipo'])?> · <?=e((string) $t['categoria'])?> · <?=e((string) $t['nombre'])?> (<?=e((string) $t['correo'])?>) · <?=e((string) $t['fecha_creacion'])?></small></p>
    <p><?=nl2br(e((string) $t['descripcion']))?></p>
    <form class="form" method="post">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
      <input type="hidden" name="ticket_id" value="<?=e((string) $t['id'])?>">
      <label>Nuevo estado
        <select name="estado">
          <?php foreach (TICKET_ESTADOS as $opt): ?><option value="<?=e($opt)?>"<?=($t['estado'] === $opt) ? ' selected' : ''?>><?=e($opt)?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Comentario para el usuario<textarea name="comentario" rows="2" required minlength="3" maxlength="500"></textarea></label>
      <button class="button" name="update_ticket">Actualizar ticket</button>
    </form>
  </details>
  <?php endforeach; ?>
  <?php if (!$tickets): ?><p>No hay tickets para mostrar.</p><?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>
