<?php
declare(strict_types=1);

// ============================================================
// PLANTILLA. Copia este archivo como config/config.php y edita los valores.
// config/config.php NO se sube a GitHub (está en .gitignore): ahí van tus claves.
// ============================================================
const DB_HOST = 'PEGA_AQUI_EL_HOST_MYSQL';      // ej. sql123.infinityfree.com
const DB_NAME = 'PEGA_AQUI_EL_NOMBRE_DE_LA_BASE'; // ej. if0_12345678_miel
const DB_USER = 'PEGA_AQUI_EL_USUARIO';          // ej. if0_12345678
const DB_PASS = 'PEGA_AQUI_LA_CONTRASENA';
const SITE_URL = 'https://PEGA-AQUI-TU-SUBDOMINIO.infinityfreeapp.com'; // sin "/" al final
const SITE_NAME = 'Miel Artesanal de Huejutla';

// Opcionales (si no los defines, no se activan o se usan enlaces genéricos)
// const GA_ID = 'G-XXXXXXXXXX';                       // Google Analytics
// const GSC_TOKEN = '';                              // Search Console (etiqueta HTML)
// const URL_FACEBOOK = 'https://www.facebook.com/TU_PAGINA';
// const URL_INSTAGRAM = 'https://www.instagram.com/TU_CUENTA';
// const URL_WHATSAPP = 'https://wa.me/52TUNUMERO';
// const URL_MEET = 'https://meet.google.com/TU-ENLACE';
const CURRENCY = 'MXN';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

function cart_count(): int
{
    return array_sum(array_map('intval', $_SESSION['cart'] ?? []));
}

function cart_products(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) return [];
    $ids = array_values(array_filter(array_map('intval', array_keys($cart))));
    if (!$ids) return [];
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id_producto AS id, nombre AS name, descripcion AS description, precio AS price, existencia AS stock, ruta_imagen AS image FROM productos WHERE activo = 1 AND id_producto IN ($marks)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) $row['quantity'] = min((int) ($_SESSION['cart'][$row['id']] ?? 0), (int) $row['stock']);
    return $rows;
}

function money(float|int|string $amount): string
{
    return '$' . number_format((float) $amount, 2) . ' MXN';
}
