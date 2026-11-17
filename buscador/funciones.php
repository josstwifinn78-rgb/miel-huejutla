<?php
declare(strict_types=1);

/** Normaliza texto para búsquedas, conservando acentos de forma segura. */
function buscador_normalizar(string $texto): string {
    $texto = trim(preg_replace('/\s+/u', ' ', strip_tags($texto)) ?? '');
    return mb_strtolower($texto, 'UTF-8');
}
function buscador_terminos(string $consulta): array {
    $consulta = buscador_normalizar($consulta);
    preg_match_all('/[\p{L}\p{N}]{2,}/u', $consulta, $m);
    return array_values(array_unique(array_slice($m[0] ?? [], 0, 8)));
}
function buscador_like(string $term): string {
    return '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}
function buscador_puntaje(array $campos, array $terminos): int {
    $score = 0;
    $titulo = buscador_normalizar((string)($campos['titulo'] ?? ''));
    foreach ($terminos as $t) {
        if ($titulo === $t) $score += 100;
        elseif (str_starts_with($titulo, $t)) $score += 45;
        elseif (str_contains($titulo, $t)) $score += 25;
        foreach (['categoria','resumen','descripcion','contenido'] as $k) {
            if (!empty($campos[$k]) && str_contains(buscador_normalizar((string)$campos[$k]), $t)) {
                $score += $k === 'categoria' ? 12 : ($k === 'resumen' ? 8 : 4);
            }
        }
    }
    return $score;
}
function buscador_ejecutar(PDO $pdo, string $q, array $f): array {
    $terms = buscador_terminos($q);
    if (!$terms) return [];
    $items = [];
    $tipo = $f['tipo'] ?? 'todo';
    $whereParts = []; $params = [];
    foreach ($terms as $term) {
        $or = [];
        foreach (['p.nombre','p.descripcion','c.nombre'] as $col) { $or[] = "$col LIKE ? ESCAPE '!'"; $params[] = buscador_like($term); }
        $whereParts[] = '(' . implode(' OR ', $or) . ')';
    }
    if (in_array($tipo, ['todo','productos'], true)) {
        $sql = "SELECT p.id_producto id,p.nombre titulo,p.url_amigable slug,p.descripcion descripcion,p.precio precio,p.existencia stock,p.ruta_imagen imagen,c.nombre categoria FROM productos p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.activo=1 AND " . implode(' AND ', $whereParts);
        $p = $params;
        if (($f['categoria'] ?? '') !== '') { $sql .= ' AND p.id_categoria=?'; $p[]=(int)$f['categoria']; }
        if (($f['min'] ?? '') !== '') { $sql .= ' AND p.precio>=?'; $p[]=(float)$f['min']; }
        if (($f['max'] ?? '') !== '') { $sql .= ' AND p.precio<=?'; $p[]=(float)$f['max']; }
        if (($f['stock'] ?? '') === '1') $sql .= ' AND p.existencia>0';
        $st=$pdo->prepare($sql); $st->execute($p);
        foreach ($st->fetchAll() as $r) {
            $r['tipo']='Producto'; $r['url']='producto/'.rawurlencode($r['slug']);
            $r['score']=buscador_puntaje(['titulo'=>$r['titulo'],'descripcion'=>$r['descripcion'],'categoria'=>$r['categoria']],$terms);
            $items[]=$r;
        }
    }
    if (in_array($tipo, ['todo','blog'], true)) {
        $parts=[];$bp=[];
        foreach($terms as $term){$parts[]="(titulo LIKE ? ESCAPE '!' OR resumen LIKE ? ESCAPE '!' OR contenido LIKE ? ESCAPE '!')";$like=buscador_like($term);array_push($bp,$like,$like,$like);}
        $st=$pdo->prepare('SELECT id_articulo id,titulo,url_amigable slug,resumen descripcion,contenido FROM articulos_blog WHERE activo=1 AND '.implode(' AND ',$parts));
        $st->execute($bp);
        foreach($st->fetchAll() as $r){
            $r['tipo']='Artículo';$r['url']='blog/'.rawurlencode($r['slug']);$r['precio']=null;$r['stock']=null;$r['imagen']='assets/img/miel.svg';
            $r['score']=buscador_puntaje(['titulo'=>$r['titulo'],'resumen'=>$r['descripcion'],'contenido'=>$r['contenido']],$terms);$items[]=$r;
        }
    }
    if (in_array($tipo, ['todo','categorias'], true)) {
        $parts=[];$cp=[];
        foreach($terms as $term){$parts[]="(nombre LIKE ? ESCAPE '!' OR descripcion LIKE ? ESCAPE '!')";$like=buscador_like($term);array_push($cp,$like,$like);}
        $st=$pdo->prepare('SELECT id_categoria id,nombre titulo,url_amigable slug,descripcion FROM categorias WHERE '.implode(' AND ',$parts));$st->execute($cp);
        foreach($st->fetchAll() as $r){$r['tipo']='Categoría';$r['url']='productos';$r['precio']=null;$r['stock']=null;$r['imagen']='assets/img/miel.svg';$r['descripcion']=$r['descripcion'];$r['score']=buscador_puntaje(['titulo'=>$r['titulo'],'descripcion'=>$r['descripcion']],$terms);$items[]=$r;}
    }
    usort($items, function($a,$b) use($f) {
        return match($f['orden'] ?? 'relevancia') {
            'precio_asc' => (($a['precio'] ?? PHP_FLOAT_MAX) <=> ($b['precio'] ?? PHP_FLOAT_MAX)),
            'precio_desc' => (($b['precio'] ?? -1) <=> ($a['precio'] ?? -1)),
            'nombre' => strcasecmp($a['titulo'],$b['titulo']),
            'fecha' => strcmp((string)($b['fecha']??''),(string)($a['fecha']??'')),
            default => ($b['score'] <=> $a['score'])
        };
    });
    return $items;
}
