# Miel de Huejutla

Tienda en línea de miel artesanal de Huejutla, Hidalgo (PHP + MySQL).
Proyecto de la materia Gestiones Tecnológicas Empresariales (ISC, IT Huejutla).

Sitio en producción: https://mielhuejutla.infinityfreeapp.com

## Estructura

| Carpeta / archivo | Contenido |
|---|---|
| `index.php` | Router y controlador (carrito, pedidos, páginas) |
| `.htaccess` | URLs amigables, sitemap y robots dinámicos |
| `config/config.example.php` | Plantilla de configuración (copiar como `config.php`) |
| `includes/` | Header, footer y tarjeta de producto |
| `buscador/` | Búsqueda con sugerencias en vivo |
| `assets/` | CSS, JS e imágenes (WebP) |
| `database/` | Esquema SQL y actualización de imágenes |
| `docs/` | Documentación (SEM, DevOps, inicio rápido) |
| `.github/workflows/deploy.yml` | Pipeline CI/CD |

## Ramas

- `main`: producción. Cada push despliega automáticamente.
- `desarrollo`: integración de cambios antes de pasar a producción.
- `feature/*`: una rama por cambio o funcionalidad.

Flujo: `feature/*` -> Pull Request a `desarrollo` -> Pull Request a `main`.

## Pipeline

En cada push o Pull Request se revisa la sintaxis PHP y los archivos indispensables.
Solo al llegar a `main`, el sitio se sube por FTP a InfinityFree.
`config/config.php` (claves) nunca se sube ni se sobrescribe en el servidor.

## Instalación local

1. Copia `config/config.example.php` como `config/config.php` y edita los valores.
2. Crea una base MySQL e importa `database/schema_infinityfree.sql`.
3. Importa `database/actualizar_imagenes.sql` para asignar las fotos.
Redes sociales: pendientes de configurar
