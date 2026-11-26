<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home','products','product','about','blog','article','contact','cart','checkout','thanks','search'];
if (!in_array($page, $allowed, true)) { http_response_code(404); $page = 'notfound'; }
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { http_response_code(400); $errors[] = 'La sesión del formulario venció. Recarga la página e inténtalo de nuevo.'; }
    elseif (isset($_POST['add_to_cart'])) {
        $id = (int) $_POST['product_id'];
        $stmt = db()->prepare('SELECT id_producto AS id, existencia AS stock FROM productos WHERE id_producto=? AND activo=1'); $stmt->execute([$id]); $product = $stmt->fetch();
        if ($product && $product['stock'] > 0) {
            $_SESSION['cart'][$id] = min((int)($_SESSION['cart'][$id] ?? 0) + 1, (int)$product['stock']);
            $_SESSION['notice'] = 'Producto agregado al carrito.';
        } else $_SESSION['notice'] = 'Este producto no está disponible.';
        header('Location: ' . SITE_URL . '/productos'); exit;
    } elseif (isset($_POST['update_cart'])) {
        foreach (($_POST['quantity'] ?? []) as $id => $qty) {
            $id = (int)$id; $qty = max(0, (int)$qty);
            $stmt = db()->prepare('SELECT existencia FROM productos WHERE id_producto=?'); $stmt->execute([$id]); $stock = $stmt->fetchColumn();
            if (!$stock || !$qty) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id] = min($qty, (int)$stock);
        }
        $_SESSION['notice'] = 'Carrito actualizado.'; header('Location: ' . SITE_URL . '/carrito'); exit;
    } elseif (isset($_POST['place_order'])) {
        $items = cart_products();
        $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? '')); $address = trim((string)($_POST['address'] ?? ''));
        $payment = (string)($_POST['payment'] ?? '');
        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$phone || !$address) $errors[] = 'Completa todos los datos y escribe un correo válido.';
        if (!in_array($payment, ['transferencia','contra_entrega'], true)) $errors[] = 'Selecciona una forma de pago.';
        if (!$items) $errors[] = 'Tu carrito está vacío.';
        if (!$errors) {
            try {
                $pdo = db(); $pdo->beginTransaction(); $total = 0;
                foreach ($items as $item) {
                    $lock = $pdo->prepare('SELECT precio AS price,existencia AS stock,nombre AS name FROM productos WHERE id_producto=? AND activo=1 FOR UPDATE'); $lock->execute([$item['id']]); $fresh = $lock->fetch();
                    if (!$fresh || $fresh['stock'] < $item['quantity']) throw new RuntimeException('El inventario cambió. Revisa tu carrito e inténtalo de nuevo.');
                    $total += (float)$fresh['price'] * (int)$item['quantity'];
                }
                $stmt = $pdo->prepare('INSERT INTO pedidos(nombre_cliente,correo_electronico,telefono,direccion_entrega,forma_pago,total) VALUES(?,?,?,?,?,?)');
                $stmt->execute([$name,$email,$phone,$address,$payment,$total]); $orderId = (int)$pdo->lastInsertId();
                foreach ($items as $item) {
                    $stmt = $pdo->prepare('INSERT INTO detalle_pedido(id_pedido,id_producto,nombre_producto,cantidad,precio_unitario) VALUES(?,?,?,?,?)');
                    $stmt->execute([$orderId,$item['id'],$item['name'],$item['quantity'],$item['price']]);
                    $stmt = $pdo->prepare('UPDATE productos SET existencia=existencia-? WHERE id_producto=?'); $stmt->execute([$item['quantity'],$item['id']]);
                }
                $pdo->commit(); unset($_SESSION['cart']); $_SESSION['order_id'] = $orderId; header('Location: ' . SITE_URL . '/index.php?page=thanks'); exit;
            } catch (Throwable $e) { if (db()->inTransaction()) db()->rollBack(); $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'No se pudo registrar el pedido. Revisa la conexión con la base de datos.'; }
        }
    } elseif (isset($_POST['send_message'])) {
        $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? '')); $message = trim((string)($_POST['message'] ?? ''));
        if (!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($message) < 8) $errors[] = 'Revisa tu nombre, correo y mensaje (mínimo 8 caracteres).';
        else { $stmt=db()->prepare('INSERT INTO mensajes_contacto(nombre,correo_electronico,mensaje) VALUES(?,?,?)'); $stmt->execute([$name,$email,$message]); $_SESSION['notice']='Gracias, recibimos tu mensaje.'; header('Location: ' . SITE_URL . '/contacto'); exit; }
    }
}

$title = ['home'=>'Miel artesanal de Huejutla | Sabor de la Huasteca','products'=>'Comprar miel artesanal de Huejutla','product'=>'Producto artesanal','about'=>'Nuestra historia | Miel de Huejutla','blog'=>'Blog de miel y apicultura','article'=>'Consejos sobre miel artesanal','contact'=>'Contacto | Miel de Huejutla','cart'=>'Carrito de compras','checkout'=>'Finalizar pedido'][$page] ?? 'Miel Artesanal de Huejutla';
$description = 'Compra miel artesanal de Huejutla, Hidalgo. Conoce productos naturales de la Huasteca, su origen y cómo realizar tu pedido en línea.';
include __DIR__ . '/includes/header.php';

try {
if ($page === 'search') {
    require __DIR__ . '/buscador/buscar.php';
} elseif ($page === 'home') {
    $products = db()->query('SELECT id_producto AS id,nombre AS name,url_amigable AS slug,descripcion AS description,precio AS price,existencia AS stock,ruta_imagen AS image FROM productos WHERE activo=1 ORDER BY id_producto LIMIT 3')->fetchAll();
    ?>
    <main><section class="hero"><div class="hero-copy"><span class="eyebrow">Cosecha local · Huasteca Hidalguense</span><h1>Miel artesanal de Huejutla, directo a tu mesa</h1><p>Descubre el sabor de la Huasteca en miel elaborada en pequeños lotes, con cuidado por las abejas y nuestra comunidad.</p><a class="button" href="productos">Ver productos</a><a class="text-link" href="nosotros">Conoce nuestra historia →</a></div><div class="hero-art"><picture><source srcset="assets/img/miel.webp" type="image/webp"><img src="assets/img/miel.svg" alt="Frasco de miel artesanal de Huejutla con panal y flores" title="Miel artesanal de la Huasteca" width="440" height="360"></picture></div></section>
    <section class="section"><div class="section-heading"><div><span class="eyebrow">De nuestra colmena</span><h2>Productos destacados</h2></div><a class="text-link" href="productos">Ver catálogo completo →</a></div><div class="grid"><?php foreach($products as $p) include __DIR__.'/includes/product-card.php'; ?></div></section>
    <section class="origin"><div><span class="eyebrow">Origen que se siente</span><h2>El sabor de la Huasteca Hidalguense</h2><p>Trabajamos con miel local de temporada y procesos cuidadosos. Cada frasco conserva el carácter natural de las flores de nuestra región.</p><a class="button button-light" href="nosotros">Conoce el proyecto</a></div><div class="origin-stat"><strong>100%</strong><span>hecha en pequeños lotes</span></div></section>
    <section class="section"><div class="section-heading"><div><span class="eyebrow">Aprende con nosotros</span><h2>Del panal al hogar</h2></div><a class="text-link" href="blog">Leer el blog →</a></div><div class="info-strip"><span class="flower">✿</span><p>Consejos para conservar la miel, conocer su origen y aprovechar cada cosecha de forma responsable.</p></div></section></main>
    <?php
} elseif ($page === 'products') {
    $products = db()->query('SELECT p.id_producto AS id,p.id_categoria AS category_id,p.nombre AS name,p.url_amigable AS slug,p.descripcion AS description,p.precio AS price,p.existencia AS stock,p.ruta_imagen AS image,c.nombre AS category_name FROM productos p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.activo=1 ORDER BY c.id_categoria,p.nombre')->fetchAll();
    ?>
    <main class="page"><span class="eyebrow">Tienda en línea</span><h1>Productos de la colmena</h1><p class="lead">Miel artesanal de Huejutla y productos seleccionados de nuestra región.</p><div class="grid"><?php foreach($products as $p) include __DIR__.'/includes/product-card.php'; ?></div></main>
    <?php
} elseif ($page === 'product') {
    $stmt=db()->prepare('SELECT p.id_producto AS id,p.id_categoria AS category_id,p.nombre AS name,p.url_amigable AS slug,p.descripcion AS description,p.precio AS price,p.existencia AS stock,p.ruta_imagen AS image,c.nombre AS category_name FROM productos p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.url_amigable=? AND p.activo=1'); $stmt->execute([$_GET['slug']??'']); $p=$stmt->fetch();
    if (!$p) { http_response_code(404); echo '<main class="page"><h1>Producto no encontrado</h1><a href="productos">Volver al catálogo</a></main>'; }
    else {
        $gallery=[['image'=>$p['image'],'alt'=>'Imagen principal de '.$p['name'].' artesanal de Huejutla']];
        $stmtImgs=db()->prepare('SELECT ruta_imagen AS image,texto_alternativo AS alt FROM imagenes_producto WHERE id_producto=? ORDER BY orden_visualizacion,id_imagen');
        $stmtImgs->execute([$p['id']]); $gallery=array_merge($gallery,$stmtImgs->fetchAll());
        $schemaImages=array_map(fn($img)=>SITE_URL.'/'.$img['image'],$gallery);
        ?><script type="application/ld+json"><?=json_encode(['@context'=>'https://schema.org/','@type'=>'Product','name'=>$p['name'],'description'=>$p['description'],'image'=>$schemaImages,'offers'=>['@type'=>'Offer','price'=>(string)$p['price'],'priceCurrency'=>'MXN','availability'=>$p['stock']>0?'https://schema.org/InStock':'https://schema.org/OutOfStock','url'=>SITE_URL.'/producto/'.$p['slug']]], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?></script><main class="page product-detail"><div class="product-image"><img id="imagenPrincipalProducto" src="<?=e($gallery[0]['image'])?>" alt="<?=e($gallery[0]['alt'])?>" title="<?=e($p['name'])?>" width="440" height="360"><?php if(count($gallery)>1):?><div class="image-gallery"><?php foreach($gallery as $photo):?><button type="button" class="gallery-thumb" data-image="<?=e($photo['image'])?>" data-alt="<?=e($photo['alt'])?>"><img src="<?=e($photo['image'])?>" alt="<?=e($photo['alt'])?>" title="<?=e($photo['alt'])?>" loading="lazy" width="88" height="72"></button><?php endforeach;?></div><?php endif;?></div><div><span class="eyebrow"><?=e($p['category_name'])?> · Hecho en Huejutla</span><h1><?=e($p['name'])?></h1><p class="price"><?=money($p['price'])?></p><p><?=e($p['description'])?></p><p class="stock"><?=$p['stock']>0?'Disponible para pedido':'Agotado'?></p><?php if($p['stock']>0):?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="product_id" value="<?=$p['id']?>"><button class="button" name="add_to_cart">Agregar al carrito</button></form><?php endif;?></div></main><?php }
} elseif ($page === 'about') { ?>
    <main class="page"><span class="eyebrow">Nuestra historia</span><h1>Miel de Huejutla, hecha con cuidado</h1><div class="prose"><p>Somos un proyecto de miel artesanal inspirado en la riqueza natural de Huejutla de Reyes, Hidalgo, en el corazón de la Huasteca Hidalguense.</p><p>Seleccionamos y envasamos la miel en lotes pequeños para compartir un producto local y acercar a más personas al trabajo de las abejas y de quienes las cuidan.</p><h2>Compromiso con la calidad</h2><p>En cada pedido buscamos informar con claridad el origen, la presentación y la disponibilidad del producto. La miel puede cristalizarse de manera natural; eso no significa que esté en mal estado.</p><a class="button" href="productos">Explorar productos</a></div></main>
<?php } elseif ($page === 'blog') {
    $posts=db()->query('SELECT id_articulo AS id,titulo AS title,url_amigable AS slug,resumen AS excerpt,contenido AS body,fecha_publicacion AS published_at FROM articulos_blog WHERE activo=1 ORDER BY fecha_publicacion DESC')->fetchAll(); ?>
    <main class="page"><span class="eyebrow">Blog</span><h1>Historias y consejos de la colmena</h1><p class="lead">Información sobre miel artesanal, cosecha y conservación en casa.</p><div class="article-list"><?php foreach($posts as $post):?><article class="article-card"><time><?=date('d/m/Y',strtotime($post['published_at']))?></time><h2><a href="blog/<?=e($post['slug'])?>"><?=e($post['title'])?></a></h2><p><?=e($post['excerpt'])?></p><a class="text-link" href="blog/<?=e($post['slug'])?>">Leer artículo →</a><div class="share">Compartir: <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?=rawurlencode(SITE_URL.'/blog/'.$post['slug'])?>">Facebook</a> · <a target="_blank" rel="noopener" href="https://wa.me/?text=<?=rawurlencode($post['title'].' '.SITE_URL.'/blog/'.$post['slug'])?>">WhatsApp</a></div></article><?php endforeach;?></div></main>
<?php } elseif ($page === 'article') {
    $stmt=db()->prepare('SELECT id_articulo AS id,titulo AS title,url_amigable AS slug,resumen AS excerpt,contenido AS body,fecha_publicacion AS published_at FROM articulos_blog WHERE url_amigable=? AND activo=1'); $stmt->execute([$_GET['slug']??'']); $post=$stmt->fetch();
    if(!$post){http_response_code(404);echo '<main class="page"><h1>Artículo no encontrado</h1><a href="blog">Volver al blog</a></main>';}
    else { ?><main class="page prose"><span class="eyebrow">Blog · <?=date('d/m/Y',strtotime($post['published_at']))?></span><h1><?=e($post['title'])?></h1><p class="lead"><?=e($post['excerpt'])?></p><p><?=nl2br(e($post['body']))?></p><a class="text-link" href="blog">← Volver al blog</a><div class="share">Compartir: <a target="_blank" rel="noopener" href="https://wa.me/?text=<?=rawurlencode($post['title'].' '.SITE_URL.'/blog/'.$post['slug'])?>">WhatsApp</a> · <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?=rawurlencode(SITE_URL.'/blog/'.$post['slug'])?>">Facebook</a></div></main><?php }
} elseif ($page === 'contact') { require_once __DIR__ . '/includes/enlaces.php'; ?>
    <main class="page narrow"><span class="eyebrow">Estamos para ayudarte</span><h1>Contacto</h1><p class="lead">Escríbenos sobre productos, disponibilidad o pedidos.</p><form class="form" method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Nombre<input name="name" required maxlength="120" value="<?=e($_POST['name']??'')?>"></label><label>Correo electrónico<input type="email" name="email" required maxlength="160" value="<?=e($_POST['email']??'')?>"></label><label>Mensaje<textarea name="message" rows="5" minlength="8" required><?=e($_POST['message']??'')?></textarea></label><button class="button" name="send_message">Enviar mensaje</button></form><div class="contact-links"><a href="<?=e($urlWa)?>" target="_blank" rel="noopener">WhatsApp de atención</a><span>Redes: <a href="<?=e($urlFb)?>" target="_blank" rel="noopener">Facebook</a> · <a href="<?=e($urlIg)?>" target="_blank" rel="noopener">Instagram</a></span><span>Reunión en línea: <a href="<?=e($urlMeet)?>" target="_blank" rel="noopener">Google Meet</a></span></div></main>
<?php } elseif ($page === 'cart') { $items=cart_products(); $total=0; ?>
    <main class="page"><span class="eyebrow">Tu pedido</span><h1>Carrito de compras</h1><?php if(!$items):?><p class="lead">Tu carrito todavía está vacío.</p><a class="button" href="productos">Ir al catálogo</a><?php else:?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="cart-list"><?php foreach($items as $item):$line=$item['price']*$item['quantity'];$total+=$line;?><div class="cart-row"><img src="<?=e($item['image'])?>" alt="<?=e($item['name'])?>" title="<?=e($item['name'])?>" width="96" height="80"><div><strong><?=e($item['name'])?></strong><small><?=money($item['price'])?> · Existencia: <?=$item['stock']?></small></div><label>Cantidad<input type="number" min="0" max="<?=$item['stock']?>" name="quantity[<?=$item['id']?>]" value="<?=$item['quantity']?>"></label><strong><?=money($line)?></strong></div><?php endforeach;?></div><div class="cart-total"><button class="button button-outline" name="update_cart">Actualizar carrito</button><strong>Total: <?=money($total)?></strong><a class="button" href="comprar">Continuar pedido</a></div></form><?php endif;?></main>
<?php } elseif ($page === 'checkout') { $items=cart_products();$total=0;foreach($items as $it)$total+=$it['price']*$it['quantity']; ?>
    <main class="page checkout"><div><span class="eyebrow">Último paso</span><h1>Finalizar pedido</h1><?php if(!$items):?><p>Agrega productos antes de continuar.</p><a href="productos" class="button">Ver productos</a><?php else:?><form method="post" class="form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Nombre completo<input name="name" required maxlength="120" value="<?=e($_POST['name']??'')?>"></label><label>Correo electrónico<input type="email" name="email" required value="<?=e($_POST['email']??'')?>"></label><label>Teléfono<input name="phone" required maxlength="30" value="<?=e($_POST['phone']??'')?>"></label><label>Dirección de entrega<textarea name="address" rows="3" required><?=e($_POST['address']??'')?></textarea></label><label>Forma de pago<select name="payment" required><option value="">Selecciona una opción</option><option value="transferencia">Transferencia bancaria (se confirma por contacto)</option><option value="contra_entrega">Pago contra entrega (demostración local)</option></select></label><p class="small-note">Esta es una tienda escolar de demostración; no procesa pagos reales.</p><button class="button" name="place_order">Registrar pedido</button></form><?php endif;?></div><aside class="summary"><h2>Resumen</h2><?php foreach($items as $it):?><p><?=e($it['name'])?> × <?=$it['quantity']?> <strong><?=money($it['price']*$it['quantity'])?></strong></p><?php endforeach;?><hr><p class="price">Total: <?=money($total)?></p></aside></main>
<?php } elseif ($page === 'thanks') { ?>
    <main class="page narrow thanks"><span class="eyebrow">Pedido registrado</span><h1>¡Gracias por tu pedido!</h1><p>Tu número de pedido es <strong>#<?=e((string)($_SESSION['order_id']??''))?></strong>. En esta demostración no se realiza ningún cobro.</p><a class="button" href="productos">Volver a la tienda</a></main>
<?php } else { ?><main class="page"><h1>Página no encontrada</h1><a href="index.php">Volver al inicio</a></main><?php }
} catch (Throwable $e) { http_response_code(503); ?><main class="page"><h1>Conecta la base de datos para continuar</h1><p>Importa <code>database/schema.sql</code> desde phpMyAdmin y revisa las credenciales en <code>config/config.php</code>.</p></main><?php }
include __DIR__ . '/includes/footer.php';
