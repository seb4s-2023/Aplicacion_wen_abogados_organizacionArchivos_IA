# 1. Descripción del entorno de desarrollo

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El sistema se desarrolló y probó en un entorno local usando el stack **XAMPP**, que integra los siguientes componentes:

- **Servidor web:** Apache (incluido en XAMPP).
- **Lenguaje / runtime:** PHP (procesado por el módulo de Apache — `mod_php`).
- **Motor de base de datos:** MySQL / MariaDB, administrado con phpMyAdmin.
- **Gestor de dependencias PHP:** Composer (para la librería `smalot/pdfparser`).
- **Editor de código:** cualquier IDE compatible con PHP (VS Code recomendado).
- **Navegador para pruebas:** Chrome / Edge.

El proyecto se ubica dentro de la carpeta `htdocs` de XAMPP, de forma que Apache lo sirve directamente en una URL local del tipo `http://localhost/bufete-ia/`. La base de datos se administra desde el módulo MySQL de XAMPP (puerto 3306 por defecto).

Este entorno local es adecuado para el ciclo de desarrollo y pruebas del proyecto académico; en el documento 05 — Implementación y Despliegue se describe cómo migrar esta misma configuración a un hosting con soporte PHP + MySQL para la puesta en producción, sin cambios estructurales en el código.
