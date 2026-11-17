<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: application/xml; charset=utf-8');
$urls = [SITE_URL . '/', SITE_URL . '/productos', SITE_URL . '/nosotros', SITE_URL . '/blog', SITE_URL . '/contacto'];
try {
    foreach (db()->query('SELECT url_amigable FROM productos WHERE activo=1') as $row) $urls[] = SITE_URL . '/producto/' . rawurlencode($row['url_amigable']);
    foreach (db()->query('SELECT url_amigable FROM articulos_blog WHERE activo=1') as $row) $urls[] = SITE_URL . '/blog/' . rawurlencode($row['url_amigable']);
} catch (Throwable $e) { http_response_code(503); }
echo '<?xml version="1.0" encoding="UTF-8"?>';
?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><?php foreach ($urls as $url): ?><url><loc><?= htmlspecialchars($url, ENT_XML1, 'UTF-8') ?></loc></url><?php endforeach; ?></urlset>
