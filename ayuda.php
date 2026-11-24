<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/mesa_ayuda.php';

$page = 'help';
$title = 'Mesa de ayuda y soporte';
$description = 'Reporta un incidente o solicita un servicio de la tienda Miel de Huejutla y consulta el estado de tu folio en la mesa de ayuda.';
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
$errors = [];
$ticket = null;
$historial = [];
$old = [
    'tipo' => '', 'categoria' => '', 'nombre' => '', 'correo' => '', 'asunto' => '',
    'descripcion' => '', 'impacto' => 'Medio', 'urgencia' => 'Media',
];
$consulta = ['folio' => '', 'correo' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        http_response_code(400);
        $errors[] = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.';
    } elseif (isset($_POST['open_ticket'])) {
        foreach ($old as $campo => $valor) {
            $old[$campo] = trim((string) ($_POST[$campo] ?? $valor));
        }
        if (!empty($_POST['website'])) {
            $errors[] = 'No se pudo registrar la solicitud.';
        }
        if (!in_array($old['tipo'], TICKET_TIPOS, true)) $errors[] = 'Selecciona el tipo de solicitud.';
        if (!in_array($old['categoria'], TICKET_CATEGORIAS, true)) $errors[] = 'Selecciona la categoría.';
        if (mb_strlen($old['nombre']) < 3 || mb_strlen($old['nombre']) > 120) $errors[] = 'Escribe tu nombre (mínimo 3 caracteres).';
        if (!filter_var($old['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['correo']) > 160) $errors[] = 'Escribe un correo electrónico válido.';
        if (mb_strlen($old['asunto']) < 5 || mb_strlen($old['asunto']) > 160) $errors[] = 'El asunto debe tener entre 5 y 160 caracteres.';
        if (mb_strlen($old['descripcion']) < 15 || mb_strlen($old['descripcion']) > 2000) $errors[] = 'Describe el problema o la solicitud (entre 15 y 2000 caracteres).';
        if (!isset(TICKET_IMPACTO[$old['impacto']])) $errors[] = 'Selecciona el impacto.';
        if (!isset(TICKET_URGENCIA[$old['urgencia']])) $errors[] = 'Selecciona la urgencia.';

        if (!$errors) {
            $prioridad = ticket_prioridad($old['impacto'], $old['urgencia']);
            try {
                $folio = ticket_crear($old + ['prioridad' => $prioridad]);
                $_SESSION['notice'] = 'Tu ticket fue registrado. Folio: ' . $folio . ' (prioridad ' . $prioridad . ', primera respuesta en ' . TICKET_SLA[$prioridad] . '). Guarda tu folio para consultar el estado.';
                header('Location: ' . SITE_URL . '/ayuda');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'No pudimos registrar tu ticket en este momento. Inténtalo de nuevo más tarde.';
            }
        }
    } elseif (isset($_POST['check_ticket'])) {
        $consulta['folio'] = strtoupper(trim((string) ($_POST['folio'] ?? '')));
        $consulta['correo'] = strtolower(trim((string) ($_POST['correo_consulta'] ?? '')));
        try {
            $stmt = db()->prepare('SELECT * FROM tickets WHERE folio = ? AND LOWER(correo) = ?');
            $stmt->execute([$consulta['folio'], $consulta['correo']]);
            $ticket = $stmt->fetch();
            if (!$ticket) {
                $ticket = null;
                $errors[] = 'No encontramos un ticket con ese folio y correo.';
            } else {
                $h = db()->prepare('SELECT estado, comentario, autor, fecha FROM ticket_historial WHERE ticket_id = ? ORDER BY fecha ASC, id ASC');
                $h->execute([(int) $ticket['id']]);
                $historial = $h->fetchAll();
            }
        } catch (Throwable $e) {
            $errors[] = 'No pudimos consultar el ticket en este momento. Inténtalo de nuevo más tarde.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="page">
  <span class="eyebrow">Mesa de ayuda</span>
  <h1>Soporte y atención a incidentes</h1>
  <p class="lead">Reporta un problema con la tienda o solicita un servicio. Recibirás un folio para dar seguimiento a tu caso.</p>

  <div class="help-grid">
    <section class="help-box">
      <h2>Abrir un ticket</h2>
      <form class="form" method="post" action="ayuda">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <div class="hp-field" aria-hidden="true"><label>Sitio web<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <label>Tipo
          <select name="tipo" required>
            <option value="">Selecciona una opción</option>
            <?php foreach (TICKET_TIPOS as $opt): ?><option value="<?=e($opt)?>"<?=($old['tipo'] === $opt) ? ' selected' : ''?>><?=e($opt)?></option><?php endforeach; ?>
          </select>
        </label>
        <label>Categoría
          <select name="categoria" required>
            <option value="">Selecciona una opción</option>
            <?php foreach (TICKET_CATEGORIAS as $opt): ?><option value="<?=e($opt)?>"<?=($old['categoria'] === $opt) ? ' selected' : ''?>><?=e($opt)?></option><?php endforeach; ?>
          </select>
        </label>
        <label>Nombre<input name="nombre" required minlength="3" maxlength="120" value="<?=e($old['nombre'])?>"></label>
        <label>Correo electrónico<input type="email" name="correo" required maxlength="160" value="<?=e($old['correo'])?>"></label>
        <label>Asunto<input name="asunto" required minlength="5" maxlength="160" value="<?=e($old['asunto'])?>"></label>
        <label>Descripción<textarea name="descripcion" rows="5" required minlength="15" maxlength="2000"><?=e($old['descripcion'])?></textarea></label>
        <div class="form-row">
          <label>Impacto
            <select name="impacto">
              <?php foreach (array_keys(TICKET_IMPACTO) as $opt): ?><option value="<?=e($opt)?>"<?=($old['impacto'] === $opt) ? ' selected' : ''?>><?=e($opt)?></option><?php endforeach; ?>
            </select>
          </label>
          <label>Urgencia
            <select name="urgencia">
              <?php foreach (array_keys(TICKET_URGENCIA) as $opt): ?><option value="<?=e($opt)?>"<?=($old['urgencia'] === $opt) ? ' selected' : ''?>><?=e($opt)?></option><?php endforeach; ?>
            </select>
          </label>
        </div>
        <button class="button" name="open_ticket">Registrar ticket</button>
      </form>
    </section>

    <section class="help-box">
      <h2>Consultar mi ticket</h2>
      <form class="form" method="post" action="ayuda">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <label>Folio<input name="folio" required maxlength="20" placeholder="MH-2026-0001" value="<?=e($consulta['folio'])?>"></label>
        <label>Correo con el que registraste el ticket<input type="email" name="correo_consulta" required maxlength="160" value="<?=e($consulta['correo'])?>"></label>
        <button class="button" name="check_ticket">Consultar estado</button>
      </form>

      <?php if ($ticket): ?>
      <div class="ticket-card">
        <h3>Folio <?=e((string) $ticket['folio'])?></h3>
        <p><span class="badge badge-estado"><?=e((string) $ticket['estado'])?></span><span class="badge"><?=e((string) $ticket['prioridad'])?></span></p>
        <p><strong><?=e((string) $ticket['asunto'])?></strong><br><small><?=e((string) $ticket['tipo'])?> · <?=e((string) $ticket['categoria'])?> · registrado el <?=e((string) $ticket['fecha_creacion'])?></small></p>
        <ol class="timeline">
          <?php foreach ($historial as $item): ?>
          <li><strong><?=e((string) $item['estado'])?></strong> <small><?=e((string) $item['fecha'])?> · <?=e((string) $item['autor'])?></small><p><?=e((string) $item['comentario'])?></p></li>
          <?php endforeach; ?>
        </ol>
      </div>
      <?php endif; ?>
    </section>
  </div>

  <p><a class="text-link" href="servicios">Ver el catálogo de servicios y los tiempos de atención →</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
