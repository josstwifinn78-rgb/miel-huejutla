# Flujo local de versiones y despliegue

Este proyecto está preparado para trabajar en local con Git y XAMPP, sin depender de un repositorio remoto. Crea el repositorio en la carpeta y usa ramas como `main`, `desarrollo` y ramas cortas `feature/catalogo`, `feature/checkout`.

El archivo `scripts/pipeline-local.ps1` revisa la sintaxis PHP y, si no encuentra errores, copia el sitio a la carpeta indicada de XAMPP. En PowerShell:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
..\scripts\pipeline-local.ps1 -TargetPath C:\xampp\htdocs\miel_huejutla
```

La ejecución local automatiza validaciones de sintaxis e instalación en el entorno de práctica. Un despliegue continuo a producción y las métricas públicas de disponibilidad requieren un servidor accesible y credenciales de despliegue; no se simulan aquí. `health.php` entrega JSON para comprobar que PHP responde y que MySQL está conectado. En el navegador se puede revisar el tiempo de carga con las herramientas de desarrollador y registrar errores HTTP desde Apache.

## Métricas a documentar en la práctica

- Tiempo de carga de la página de inicio y catálogo (segundos).
- Disponibilidad del sitio de práctica durante la sesión.
- Respuestas HTTP 200, 404 y 500 observadas en las pruebas manuales.
- Resultado de `health.php` y cantidad de productos disponibles.
- Cambios de código revisados en una rama antes de integrarlos a `main`.
