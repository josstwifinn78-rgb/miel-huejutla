<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
header('Content-Type: application/json; charset=utf-8');
$q=trim((string)($_GET['q']??''));
if(mb_strlen($q,'UTF-8')<2){echo json_encode([]);exit;}
$like='%'.str_replace(['!','%','_'],['!!','!%','!_'],$q).'%';
$st=db()->prepare("SELECT nombre titulo, CONCAT('producto/',url_amigable) url, 'Producto' tipo FROM productos WHERE activo=1 AND (nombre LIKE ? ESCAPE '!' OR descripcion LIKE ? ESCAPE '!') ORDER BY (nombre LIKE ? ESCAPE '!') DESC LIMIT 5");
$st->execute([$like,$like,$like]);$rows=$st->fetchAll();
$st=db()->prepare("SELECT titulo, CONCAT('blog/',url_amigable) url, 'Artículo' tipo FROM articulos_blog WHERE activo=1 AND (titulo LIKE ? ESCAPE '!' OR resumen LIKE ? ESCAPE '!') ORDER BY fecha_publicacion DESC LIMIT 4");
$st->execute([$like,$like]);$rows=array_merge($rows,$st->fetchAll());
echo json_encode(array_slice($rows,0,7),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
