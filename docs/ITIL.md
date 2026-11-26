# Gestión de servicios de TI (ITIL) - Miel de Huejutla

Documento del Módulo 4 de la materia Gestiones Tecnológicas Empresariales.
Las funciones y los roles son simulados para el proyecto escolar.

## 1. Alineación entre estrategia de negocio y servicio (Diseño del servicio)

| Objetivo del negocio | Servicio de TI que lo habilita | Qué percibe el usuario |
|---|---|---|
| Vender miel artesanal de Huejutla en línea | Tienda web (catálogo, carrito, pedidos) | Compra sencilla desde cualquier dispositivo |
| Ser encontrado en buscadores | SEO on-page, sitemap, robots, analítica | Aparecer al buscar "miel artesanal Huejutla" |
| Generar confianza | Mesa de ayuda con folio y seguimiento | Sabe que su problema será atendido y en cuánto tiempo |
| Mejorar con datos | Analytics, Search Console, monitoreo | Un sitio más rápido y claro con cada iteración |

## 2. Catálogo de servicios

Publicado para los usuarios en `/servicios`.

| Servicio | Responsable | Canal | Objetivo |
|---|---|---|---|
| Venta en línea de productos de la colmena | Administrador del sitio | Sitio web | Disponibilidad 99 % mensual |
| Seguimiento y confirmación de pedidos | Atención a clientes | Sitio web, correo, WhatsApp | Confirmación en 1 día hábil |
| Atención de incidentes | Mesa de ayuda | Formulario `/ayuda` | Primera respuesta según prioridad |
| Solicitudes de servicio | Mesa de ayuda | Formulario `/ayuda` | 1 a 2 días hábiles |
| Contenido informativo (blog) | Responsable de contenido | Blog | Publicación mensual |
| Monitoreo y mejora continua | Responsable de DevOps | Analytics, Search Console, monitoreo | Reporte periódico |

## 3. Ciclo de vida del servicio aplicado al proyecto

| Fase ITIL | Qué se hizo en el proyecto |
|---|---|
| Estrategia | Definir el negocio (venta de miel), el público y la campaña SEM (`docs/CAMPANA_SEM.md`) |
| Diseño | Arquitectura PHP + MySQL, URLs amigables, catálogo de servicios, matriz de prioridades |
| Transición | Repositorio con ramas, pruebas automáticas y despliegue continuo (GitHub Actions) |
| Operación | Mesa de ayuda (incidentes y solicitudes), monitoreo de disponibilidad |
| Mejora continua | Reporte periódico con Analytics, Search Console y métricas DevOps (`docs/REPORTE_MEJORA_CONTINUA.md`) |

## 4. Operación y soporte: mesa de ayuda

### Incidente y solicitud de servicio

- **Incidente:** algo que funcionaba dejó de funcionar o funciona mal (por ejemplo, no se puede registrar un pedido).
- **Solicitud de servicio:** petición estándar (por ejemplo, un cambio en un pedido o información de un producto).

### Flujo de resolución de incidentes

1. **Identificación y registro:** el usuario abre un ticket en `/ayuda`; el sistema genera el folio `MH-AAAA-0001`.
2. **Categorización:** pedido, pago, producto, sitio web u otro.
3. **Priorización:** el usuario indica impacto y urgencia; el sistema calcula la prioridad.
4. **Diagnóstico:** soporte revisa el caso (estado "En análisis").
5. **Resolución:** se aplica la solución (estado "En atención" y luego "Resuelto"), con un comentario visible para el usuario.
6. **Cierre:** se confirma con el usuario y el ticket pasa a "Cerrado".

Estados: Nuevo → En análisis → En atención → Resuelto → Cerrado. Cada cambio queda en el historial del ticket.

### Matriz de prioridad (impacto + urgencia)

| Impacto \ Urgencia | Baja | Media | Alta |
|---|---|---|---|
| Bajo | P4 Baja | P4 Baja | P3 Media |
| Medio | P4 Baja | P3 Media | P2 Alta |
| Alto | P3 Media | P2 Alta | P1 Crítica |

### Tiempos objetivo de primera respuesta

| Prioridad | Primera respuesta |
|---|---|
| P1 Crítica | 2 horas |
| P2 Alta | 4 horas |
| P3 Media | 1 día hábil |
| P4 Baja | 2 días hábiles |

### Roles

| Rol | Responsabilidad |
|---|---|
| Usuario | Reporta el caso y confirma la solución |
| Mesa de ayuda (nivel 1) | Registra, clasifica y da seguimiento con el panel `/admin/tickets.php` |
| Soporte técnico (nivel 2) | Diagnostica y corrige fallas del sitio, base de datos o despliegue |

### Cómo se opera

1. El usuario crea un ticket en `/ayuda` y guarda su folio.
2. Soporte entra a `/admin/tickets.php` con la contraseña `ADMIN_PASS` definida en `config/config.php`.
3. Soporte cambia el estado y escribe un comentario; el usuario lo consulta en `/ayuda` con su folio y correo.

## 5. Mejora continua

El panel de soporte muestra tickets por estado y prioridad, y el tiempo promedio de resolución. Esos datos, junto con Google Analytics, Search Console y las métricas DevOps, alimentan el reporte periódico descrito en `docs/REPORTE_MEJORA_CONTINUA.md`.
