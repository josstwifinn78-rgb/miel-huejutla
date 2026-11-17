<?php
require_once __DIR__ . '/funciones.php';
$q = trim((string)($_GET['q'] ?? ''));
$f = [
 'tipo'=>(string)($_GET['tipo'] ?? 'todo'),
 'categoria'=>(string)($_GET['categoria'] ?? ''),
 'min'=>(string)($_GET['min'] ?? ''),
 'max'=>(string)($_GET['max'] ?? ''),
 'stock'=>(string)($_GET['stock'] ?? ''),
 'orden'=>(string)($_GET['orden'] ?? 'relevancia')
];
$categorias = db()->query('SELECT id_categoria,nombre FROM categorias ORDER BY nombre')->fetchAll();
$resultados = $q !== '' ? buscador_ejecutar(db(),$q,$f) : [];
$pagina = max(1,(int)($_GET['p'] ?? 1)); $porPagina=10; $total=count($resultados);
$paginas=max(1,(int)ceil($total/$porPagina)); $resultadosPagina=array_slice($resultados,($pagina-1)*$porPagina,$porPagina);
function buscador_url(array $cambios=[]): string { $v=array_merge($_GET,$cambios); return 'index.php?'.http_build_query($v); }
?>
<main class="page search-page">
 <span class="eyebrow">Encuentra lo que buscas</span>
 <h1>Buscar en la tienda</h1>
 <form class="search-main-form" action="index.php" method="get">
  <input type="hidden" name="page" value="search">
  <label class="sr-only" for="search-query">Palabras clave</label>
  <input id="search-query" type="search" name="q" value="<?=e($q)?>" placeholder="Ej. miel multifloral, propóleo, conservación..." required>
  <button class="button" type="submit">Buscar</button>
 </form>
 <div class="search-layout">
  <aside class="search-filters"><h2>Filtrar resultados</h2>
   <form method="get" action="index.php"><input type="hidden" name="page" value="search"><input type="hidden" name="q" value="<?=e($q)?>">
    <label>Buscar en<select name="tipo"><option value="todo" <?=$f['tipo']==='todo'?'selected':''?>>Todo el sitio</option><option value="productos" <?=$f['tipo']==='productos'?'selected':''?>>Productos</option><option value="blog" <?=$f['tipo']==='blog'?'selected':''?>>Blog</option><option value="categorias" <?=$f['tipo']==='categorias'?'selected':''?>>Categorías</option></select></label>
    <label>Categoría<select name="categoria"><option value="">Todas</option><?php foreach($categorias as $c):?><option value="<?=$c['id_categoria']?>" <?=((string)$c['id_categoria']===$f['categoria']?'selected':'')?>><?=e($c['nombre'])?></option><?php endforeach;?></select></label>
    <div class="filter-pair"><label>Precio mínimo<input type="number" min="0" step="0.01" name="min" value="<?=e($f['min'])?>"></label><label>Precio máximo<input type="number" min="0" step="0.01" name="max" value="<?=e($f['max'])?>"></label></div>
    <label class="filter-check"><input type="checkbox" name="stock" value="1" <?=$f['stock']==='1'?'checked':''?>> Solo disponibles</label>
    <label>Ordenar por<select name="orden"><option value="relevancia">Mayor relevancia</option><option value="precio_asc" <?=$f['orden']==='precio_asc'?'selected':''?>>Precio: menor a mayor</option><option value="precio_desc" <?=$f['orden']==='precio_desc'?'selected':''?>>Precio: mayor a menor</option><option value="nombre" <?=$f['orden']==='nombre'?'selected':''?>>Nombre A-Z</option></select></label>
    <button class="button" type="submit">Aplicar filtros</button><a class="clear-filters" href="index.php?page=search&amp;q=<?=rawurlencode($q)?>">Limpiar filtros</a>
   </form>
  </aside>
  <section class="search-results">
   <?php if($q===''):?><p class="search-hint">Escribe una palabra o frase para buscar productos, categorías y artículos del blog.</p>
   <?php elseif(!$total):?><h2>No encontramos resultados para “<?=e($q)?>”</h2><p>Prueba con otras palabras, revisa la ortografía o elimina algunos filtros.</p>
   <?php else:?><div class="results-heading"><h2>Resultados para “<?=e($q)?>”</h2><span><?=$total?> resultado(s)</span></div>
    <div class="search-result-list"><?php foreach($resultadosPagina as $r):?>
     <article class="search-result-card">
      <?php if($r['tipo']==='Producto'):?><a class="result-image" href="<?=e($r['url'])?>"><img src="<?=e($r['imagen'])?>" alt="<?=e($r['titulo'])?>" loading="lazy"></a><?php else:?><div class="result-icon">✿</div><?php endif;?>
      <div class="result-content"><span class="result-type"><?=e($r['tipo'])?><?=!empty($r['categoria'])?' · '.e($r['categoria']):''?></span><h3><a href="<?=e($r['url'])?>"><?=e($r['titulo'])?></a></h3><p><?=e(mb_strimwidth((string)$r['descripcion'],0,230,'…','UTF-8'))?></p>
       <?php if($r['tipo']==='Producto'):?><div class="result-meta"><strong><?=money($r['precio'])?></strong><span><?=$r['stock']>0?'Disponible':'Agotado'?></span></div><?php endif;?>
       <a class="text-link" href="<?=e($r['url'])?>">Ver <?=strtolower($r['tipo'])?> →</a>
      </div>
     </article>
    <?php endforeach;?></div>
    <?php if($paginas>1):?><nav class="search-pagination" aria-label="Paginación"><?php for($i=1;$i<=$paginas;$i++):?><a class="<?=$i===$pagina?'current':''?>" href="<?=e(buscador_url(['p'=>$i]))?>"><?=$i?></a><?php endfor;?></nav><?php endif;?>
   <?php endif;?>
  </section>
 </div>
</main>
