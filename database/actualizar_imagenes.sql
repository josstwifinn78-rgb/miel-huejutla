-- Asigna las fotos reales (WebP) a cada producto.
-- Ejecutar en phpMyAdmin: selecciona tu base > pestaña SQL > pega esto > Continuar.
UPDATE productos SET ruta_imagen='assets/img/productos/miel-multifloral-500g.webp'  WHERE id_producto=1;
UPDATE productos SET ruta_imagen='assets/img/productos/miel-multifloral-1kg.webp'   WHERE id_producto=2;
UPDATE productos SET ruta_imagen='assets/img/productos/miel-de-temporada-500g.webp' WHERE id_producto=3;
UPDATE productos SET ruta_imagen='assets/img/productos/miel-con-propoleo.webp'      WHERE id_producto=4;
UPDATE productos SET ruta_imagen='assets/img/productos/propoleo-natural-30ml.webp'  WHERE id_producto=5;
UPDATE productos SET ruta_imagen='assets/img/productos/jabon-artesanal-miel.webp'   WHERE id_producto=6;
