# Miel Artesanal de Huejutla

Tienda escolar construida con PHP, MySQL, HTML, CSS y JavaScript. El catálogo, categorías, existencias, pedidos, artículos del blog y mensajes se guardan en MySQL.

## Instalación con XAMPP

1. Copia la carpeta `miel_huejutla_php_mysql` a `C:\xampp\htdocs\`.
2. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin`.
4. Selecciona **Importar**, elige `database/schema.sql` y pulsa **Continuar**. El script crea la nueva base de datos `miel_artesanal_huejutla`, sus tablas con nombres en español, seis productos de ejemplo y una imagen principal por producto.
5. Si tu MySQL usa usuario o contraseña distintos, actualiza `DB_USER` y `DB_PASS` en `config/config.php`.
6. Abre `http://localhost/miel_huejutla_php_mysql/`.

La nueva base se llama `miel_artesanal_huejutla` y el esquema usa las tablas `categorias`, `productos`, `imagenes_producto`, `pedidos`, `detalle_pedido`, `articulos_blog` y `mensajes_contacto`, con sus columnas en español. El catálogo se administra directamente desde phpMyAdmin, en las tablas `productos` y `categorias`. Cada producto tiene precio, existencia, descripción, categoría, dirección amigable e imagen principal. Los pedidos se almacenan en `pedidos` y `detalle_pedido`; al registrar un pedido se descuenta el inventario dentro de una transacción. La tabla `imagenes_producto` permite guardar tantas imágenes adicionales como necesites por cada producto y mostrarlas como una galería en el detalle. La versión anterior de la base no se elimina; esta nueva versión crea una base separada.

### Cambiar la imagen de un producto

1. Crea la carpeta `assets/img/productos/` si todavía no existe.
2. Copia ahí tus imágenes y usa nombres simples, sin espacios ni acentos, por ejemplo `miel-multifloral-500g.webp`.
3. En phpMyAdmin abre `miel_artesanal_huejutla` → tabla `productos` → **Examinar**.
4. Para cambiar la imagen principal, pulsa **Editar** en la fila del producto y cambia `ruta_imagen` por `assets/img/productos/miel-multifloral-500g.webp`.
5. Para añadir otra imagen al mismo producto, abre la pestaña **SQL** y registra una fila en `imagenes_producto`. El ejemplo añade una segunda imagen al producto con id 1:

   ```sql
   INSERT INTO imagenes_producto
     (id_producto, ruta_imagen, texto_alternativo, orden_visualizacion)
   VALUES
     (1, 'assets/img/productos/miel-500g-lateral.webp', 'Vista lateral del frasco de miel de 500 gramos', 1);
   ```

6. Guarda y recarga el sitio con `Ctrl + F5`. El visitante podrá pulsar las miniaturas en la página del producto.

No escribas la ruta completa de Windows (como `C:\\xampp\\htdocs\\...`) en MySQL. Guarda solamente la ruta web relativa desde la carpeta del proyecto. Para revertir la principal, vuelve a poner `assets/img/miel.svg`. Los registros de imágenes adicionales se pueden borrar desde la tabla `imagenes_producto`.

## Prueba de navegación

- Inicio: `http://localhost/miel_huejutla_php_mysql/`
- Catálogo: `http://localhost/miel_huejutla_php_mysql/productos`
- Blog: `http://localhost/miel_huejutla_php_mysql/blog`
- Sitemap: `http://localhost/miel_huejutla_php_mysql/sitemap.xml`
- Estado técnico: `http://localhost/miel_huejutla_php_mysql/health.php`

Las rutas amigables requieren `mod_rewrite` habilitado en Apache y permitir `.htaccess` (`AllowOverride All`). XAMPP normalmente trae `mod_rewrite` habilitado. Si las rutas limpias muestran 404, usa temporalmente las rutas `index.php?page=products`, `index.php?page=cart`, etc., y verifica la configuración de Apache.

## Pagos y comunicación

Las opciones de pago son demostrativas: transferencia por confirmar y pago contra entrega. El sistema no captura datos bancarios ni procesa cobros reales. La página de contacto guarda mensajes en la base de datos; WhatsApp, redes y videoconferencia son accesos de ejemplo que deben reemplazarse por los datos reales del negocio.

## Analítica y publicación

En `includes/header.php` está marcado el lugar para pegar la etiqueta de Google Analytics después de obtener el identificador `G-...`. Para Search Console hay que verificar un dominio publicado y enviar `https://tudominio/sitemap.xml`. `SITE_URL` en `config/config.php` debe cambiarse a la URL pública antes de generar el sitemap definitivo. En localhost, Google no puede rastrear el sitio. Para publicar un video, sube el archivo optimizado y añade sus metadatos `og:video` y `VideoObject` (nombre, descripción, miniatura, fecha y duración) con la información verdadera del video; no se inventan datos de previsualización.

## Seguridad para la demostración

Las consultas usan PDO y parámetros preparados, los formularios incluyen token CSRF y las salidas se escapan con `htmlspecialchars`. Antes de publicar, configura HTTPS, credenciales fuertes de MySQL, copias de seguridad y un flujo real para pagos y notificaciones.
