<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?=e(SITE_URL)?>/">
  <title><?=e(str_contains($title, 'Sabor de la Huasteca') ? $title : $title . ' | Sabor de la Huasteca')?></title>
  <meta name="description" content="<?=e($description)?>">
  <meta name="keywords" content="miel artesanal de Huejutla, miel natural Huasteca, comprar miel Hidalgo, productos de la colmena">
  <meta name="robots" content="index,follow">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="es_MX">
  <meta property="og:site_name" content="<?=e(SITE_NAME)?>">
  <meta property="og:title" content="<?=e($title)?>">
  <meta property="og:description" content="<?=e($description)?>">
  <meta property="og:image" content="<?=e(SITE_URL)?>/assets/img/miel.svg">
  <meta name="twitter:card" content="summary_large_image">
  <?php
  $canonical = SITE_URL . '/';
  if ($page === 'products') $canonical .= 'productos';
  elseif ($page === 'about') $canonical .= 'nosotros';
  elseif ($page === 'blog') $canonical .= 'blog';
  elseif ($page === 'contact') $canonical .= 'contacto';
  elseif ($page === 'help') $canonical .= 'ayuda';
  elseif ($page === 'services') $canonical .= 'servicios';
  elseif ($page === 'product') $canonical .= 'producto/' . rawurlencode((string)($_GET['slug'] ?? ''));
  elseif ($page === 'article') $canonical .= 'blog/' . rawurlencode((string)($_GET['slug'] ?? ''));
  ?>
  <link rel="canonical" href="<?=e($canonical)?>">
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="assets/css/buscador.css">
  <link rel="stylesheet" href="assets/css/mesa-ayuda.css">

  <?php if (defined('GSC_TOKEN') && GSC_TOKEN !== ''): ?>
  <meta name="google-site-verification" content="<?=e(GSC_TOKEN)?>">
  <?php endif; ?>
  <?php if (defined('GA_ID') && GA_ID !== ''): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?=e(GA_ID)?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?=e(GA_ID)?>');
  </script>
  <?php endif; ?>
</head>
<body>
<header class="site-header"><a class="brand" href="index.php"><img src="assets/img/favicon.svg" alt="Logotipo de Miel de Huejutla, gota de miel" title="Miel de Huejutla" width="38" height="38"><span>Miel de <strong>Huejutla</strong><small>Sabor de la Huasteca</small></span></a>
<button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">☰</button>
<nav class="main-nav"><a href="index.php">Inicio</a><a href="productos">Productos</a><a href="nosotros">Nosotros</a><a href="blog">Blog</a><a href="contacto">Contacto</a><a href="ayuda">Ayuda</a><a class="cart-link" href="carrito">Carrito <b><?=cart_count()?></b></a></nav>

<form class="searchbar" action="index.php" method="get" data-searchbar>
  <input type="hidden" name="page" value="search">
  <label class="sr-only" for="site-search">Buscar en la tienda</label>
  <input id="site-search" type="search" name="q"
    placeholder="Buscar productos, miel, propóleo..." autocomplete="off">
  <button type="submit">Buscar</button>
  <div class="search-suggestions" hidden></div>
</form>
</header>
<?php if ($notice): ?><div class="notice" role="status"><?=e($notice)?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="notice error" role="alert"><?php foreach($errors as $error): ?><p><?=e($error)?></p><?php endforeach; ?></div><?php endif; ?>

<script src="assets/js/buscador.js" defer></script>