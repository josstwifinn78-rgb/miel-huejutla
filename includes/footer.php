<?php
$urlFb = defined('URL_FACEBOOK') ? URL_FACEBOOK : 'https://www.facebook.com/';
$urlIg = defined('URL_INSTAGRAM') ? URL_INSTAGRAM : 'https://www.instagram.com/';
$urlWa = defined('URL_WHATSAPP') ? URL_WHATSAPP : 'https://wa.me/527897000000';
$urlMeet = defined('URL_MEET') ? URL_MEET : 'https://meet.google.com/';
?>
<footer class="site-footer"><div class="footer-main"><div class="footer-brand"><span class="eyebrow">Desde Huejutla, Hidalgo</span><h2>Miel que cuenta una historia.</h2><p>Un proyecto local inspirado en las abejas, las flores y el trabajo de nuestra comunidad.</p></div><div><h3>Explora</h3><a href="productos">Productos</a><a href="nosotros">Nosotros</a><a href="blog">Blog</a><a href="contacto">Contacto</a></div><div><h3>Conecta</h3><a href="<?=e($urlFb)?>" target="_blank" rel="noopener">Facebook</a><a href="<?=e($urlIg)?>" target="_blank" rel="noopener">Instagram</a><a href="<?=e($urlWa)?>" target="_blank" rel="noopener">WhatsApp</a><a href="<?=e($urlMeet)?>" target="_blank" rel="noopener">Videoconferencia</a></div></div><div class="footer-bottom"><span>© <?=date('Y')?> Miel Artesanal de Huejutla · Proyecto escolar</span><span>Pedidos de demostración · Sin cobros reales</span></div></footer>
<script src="assets/js/app.js" defer></script>
</body></html>
