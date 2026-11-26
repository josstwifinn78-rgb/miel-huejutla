<?php
declare(strict_types=1);

// Mesa de ayuda (ITIL): constantes y funciones compartidas.

const TICKET_TIPOS = ['Incidente', 'Solicitud de servicio'];
const TICKET_CATEGORIAS = ['Pedido', 'Pago', 'Producto', 'Sitio web', 'Otro'];
const TICKET_ESTADOS = ['Nuevo', 'En análisis', 'En atención', 'Resuelto', 'Cerrado'];
const TICKET_IMPACTO = ['Bajo' => 1, 'Medio' => 2, 'Alto' => 3];
const TICKET_URGENCIA = ['Baja' => 1, 'Media' => 2, 'Alta' => 3];
const TICKET_SLA = [
    'P1 Crítica' => '2 horas',
    'P2 Alta' => '4 horas',
    'P3 Media' => '1 día hábil',
    'P4 Baja' => '2 días hábiles',
];

// Matriz impacto x urgencia -> prioridad
function ticket_prioridad(string $impacto, string $urgencia): string
{
    $puntos = (TICKET_IMPACTO[$impacto] ?? 1) + (TICKET_URGENCIA[$urgencia] ?? 1);
    if ($puntos >= 6) return 'P1 Crítica';
    if ($puntos === 5) return 'P2 Alta';
    if ($puntos === 4) return 'P3 Media';
    return 'P4 Baja';
}

function ticket_historial(int $ticketId, string $estado, string $comentario, string $autor): void
{
    $stmt = db()->prepare('INSERT INTO ticket_historial (ticket_id, estado, comentario, autor) VALUES (?, ?, ?, ?)');
    $stmt->execute([$ticketId, $estado, $comentario, $autor]);
}

// Registra el ticket y devuelve el folio (MH-AAAA-0001)
function ticket_crear(array $d): string
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO tickets (tipo, categoria, nombre, correo, asunto, descripcion, impacto, urgencia, prioridad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$d['tipo'], $d['categoria'], $d['nombre'], $d['correo'], $d['asunto'], $d['descripcion'], $d['impacto'], $d['urgencia'], $d['prioridad']]);
        $id = (int) $pdo->lastInsertId();
        $folio = 'MH-' . date('Y') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
        $upd = $pdo->prepare('UPDATE tickets SET folio = ? WHERE id = ?');
        $upd->execute([$folio, $id]);
        ticket_historial($id, 'Nuevo', 'Ticket registrado por el usuario.', 'Sistema');
        $pdo->commit();
        return $folio;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
