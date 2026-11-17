-- Esquema para InfinityFree: la base ya se creó desde el panel.
-- Selecciona esa base en phpMyAdmin y luego usa la pestaña Importar.

CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  url_amigable VARCHAR(90) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
  id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT UNSIGNED NOT NULL,
  nombre VARCHAR(140) NOT NULL,
  url_amigable VARCHAR(160) NOT NULL UNIQUE,
  descripcion TEXT NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  existencia INT UNSIGNED NOT NULL DEFAULT 0,
  ruta_imagen VARCHAR(255) NOT NULL DEFAULT 'assets/img/miel.svg',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_categorias FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS imagenes_producto (
  id_imagen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_producto INT UNSIGNED NOT NULL,
  ruta_imagen VARCHAR(255) NOT NULL,
  texto_alternativo VARCHAR(180) NOT NULL,
  orden_visualizacion SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_imagenes_productos FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
  INDEX idx_imagenes_producto (id_producto, orden_visualizacion),
  UNIQUE KEY uq_imagen_por_producto (id_producto, ruta_imagen)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
  id_pedido BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_cliente VARCHAR(120) NOT NULL,
  correo_electronico VARCHAR(160) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  direccion_entrega VARCHAR(255) NOT NULL,
  forma_pago ENUM('transferencia','contra_entrega') NOT NULL,
  estado ENUM('recibido','en_preparacion','enviado','entregado','cancelado') NOT NULL DEFAULT 'recibido',
  total DECIMAL(10,2) NOT NULL,
  fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_pedido (
  id_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido BIGINT UNSIGNED NOT NULL,
  id_producto INT UNSIGNED NOT NULL,
  nombre_producto VARCHAR(140) NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
  CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS articulos_blog (
  id_articulo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(180) NOT NULL,
  url_amigable VARCHAR(190) NOT NULL UNIQUE,
  resumen VARCHAR(300) NOT NULL,
  contenido TEXT NOT NULL,
  fecha_publicacion DATE NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mensajes_contacto (
  id_mensaje BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo_electronico VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categorias (id_categoria,nombre,url_amigable,descripcion) VALUES
(1,'Miel natural','miel-natural','Miel artesanal de la Huasteca Hidalguense.'),
(2,'Productos de la colmena','productos-colmena','Productos elaborados con ingredientes de la colmena.');

INSERT IGNORE INTO productos (id_producto,id_categoria,nombre,url_amigable,descripcion,precio,existencia,ruta_imagen) VALUES
(1,1,'Miel multifloral 500 g','miel-multifloral-500g','Miel cruda de flores variadas de la región Huasteca. Ideal para bebidas, fruta y cocina.',145.00,24,'assets/img/miel.svg'),
(2,1,'Miel multifloral 1 kg','miel-multifloral-1kg','Frasco familiar de miel artesanal cosechada y envasada en pequeños lotes.',260.00,16,'assets/img/miel.svg'),
(3,1,'Miel de temporada 500 g','miel-de-temporada-500g','Lote de temporada con notas florales propias del paisaje de Huejutla.',160.00,12,'assets/img/miel.svg'),
(4,2,'Miel con propóleo 250 g','miel-con-propoleo-250g','Mezcla de miel natural y extracto de propóleo. Producto alimenticio artesanal.',135.00,18,'assets/img/miel.svg'),
(5,2,'Propóleo natural 30 ml','propoleo-natural-30ml','Extracto de propóleo de la colmena en presentación práctica de 30 ml.',95.00,20,'assets/img/miel.svg'),
(6,2,'Jabón artesanal de miel','jabon-artesanal-de-miel','Jabón artesanal con miel. Elaboración local en lotes pequeños.',75.00,14,'assets/img/miel.svg');

INSERT IGNORE INTO articulos_blog (id_articulo,titulo,url_amigable,resumen,contenido,fecha_publicacion) VALUES
(1,'De las flores de la Huasteca al frasco','flores-huasteca-al-frasco','Conoce de manera general cómo se obtiene y conserva la miel artesanal.','La miel comienza con el trabajo de las abejas y la floración de cada temporada. En la Huasteca Hidalguense, el clima y la variedad de plantas dan características particulares a cada cosecha. Después de recolectarla, se filtra con cuidado y se envasa para conservar su sabor.','2026-10-01'),
(2,'Cómo conservar la miel en casa','como-conservar-miel-en-casa','Consejos sencillos para cuidar la textura y el sabor de tu miel.','Guarda el frasco bien cerrado, en un lugar fresco y seco, lejos del sol directo. La cristalización puede ocurrir de forma natural; coloca el envase en agua tibia para suavizarla. No es necesario refrigerarla.','2026-10-10');
