<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: " . SITE_URL . "/sitemap.xml\n";
