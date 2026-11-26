-- Mesa de ayuda ITIL: tablas de tickets y su historial.
-- En phpMyAdmin: selecciona tu base > pestaña SQL (o Importar) > ejecutar.

CREATE TABLE IF NOT EXISTS tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(20) NULL UNIQUE,
  tipo VARCHAR(30) NOT NULL,
  categoria VARCHAR(30) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(160) NOT NULL,
  asunto VARCHAR(160) NOT NULL,
  descripcion TEXT NOT NULL,
  impacto VARCHAR(10) NOT NULL,
  urgencia VARCHAR(10) NOT NULL,
  prioridad VARCHAR(20) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'Nuevo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fecha_resolucion DATETIME NULL,
  INDEX idx_tickets_estado (estado),
  INDEX idx_tickets_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ticket_historial (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT UNSIGNED NOT NULL,
  estado VARCHAR(20) NOT NULL,
  comentario VARCHAR(500) NOT NULL,
  autor VARCHAR(60) NOT NULL DEFAULT 'Sistema',
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historial_ticket (ticket_id),
  CONSTRAINT fk_historial_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
