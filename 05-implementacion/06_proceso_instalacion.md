# 6. Proceso de instalación

1. Copiar el proyecto dentro de `htdocs/` de XAMPP.
2. `composer require smalot/pdfparser`.
3. Importar `bufete_ia.sql` desde phpMyAdmin (crea el esquema + usuarios de prueba).
4. Completar `config/database.php` con las credenciales de MySQL.
5. Completar `config/gemini.php` con una `GEMINI_API_KEY` válida.
6. Crear la carpeta `uploads/` en la raíz del proyecto, con permisos de escritura.
7. Iniciar Apache y MySQL desde el panel de control de XAMPP.

Este procedimiento coincide con el manual técnico del documento 03 — Desarrollo; se repite aquí en el contexto específico de puesta en marcha del ambiente.
