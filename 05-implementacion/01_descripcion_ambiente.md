# 1. Descripción del ambiente de implementación

Para efectos de este proyecto académico, la implementación y la evidencia de funcionamiento (video de sustentación) se realizan sobre un **ambiente local con XAMPP**, que actúa como servidor de aplicación (Apache + PHP) y de base de datos (MySQL/MariaDB) en la misma máquina. No se contrató un hosting externo, decisión razonable dado el alcance y el tiempo del proyecto académico.

Este documento describe tanto el ambiente local usado para la evidencia, como el camino de migración a un hosting compartido/VPS con soporte PHP + MySQL, de forma que el sistema quede documentado como desplegable en producción **sin cambios estructurales de código** — solo de configuración (ver sección 5).

## Comparativa de ambientes

| Aspecto | Ambiente actual (local) | Ambiente de producción (referencia) |
|---|---|---|
| Servidor web | Apache (XAMPP) | Apache o Nginx con PHP-FPM en hosting/VPS |
| Motor de base de datos | MySQL/MariaDB (XAMPP) | MySQL/MariaDB gestionado por el proveedor de hosting |
| Acceso | `http://localhost/bufete-ia/` | Dominio o subdominio propio con HTTPS |
| Almacenamiento de archivos | Carpeta `uploads/` en el disco local | Carpeta `uploads/` en el servidor, o almacenamiento externo (ej. S3) como mejora futura |
