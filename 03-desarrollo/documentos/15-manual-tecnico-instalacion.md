# 15. Manual técnico de instalación y ejecución

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 15.1 Requisitos previos

- XAMPP (incluye Apache y MySQL/MariaDB)
- PHP con la extensión `ZipArchive` habilitada (activa por defecto en XAMPP)
- Composer
- Una API key de Google Gemini

## 15.2 Pasos de instalación

1. Copiar la carpeta del proyecto dentro de `htdocs` de XAMPP.
2. Abrir una terminal en la raíz del proyecto y ejecutar: `composer require smalot/pdfparser`
3. Abrir phpMyAdmin, crear la base de datos importando el archivo `bufete_ia.sql` (esto crea el esquema y los dos usuarios de prueba).
4. Editar `config/database.php` con host, usuario, contraseña y nombre de la base de datos (`bufete_ia`).
5. Editar `config/gemini.php` con una `GEMINI_API_KEY` válida.
6. Crear la carpeta `uploads/` en la raíz del proyecto si no existe, con permisos de escritura.
7. Iniciar los módulos Apache y MySQL desde el panel de control de XAMPP.

## 15.3 Ejecución

- Acceder desde el navegador a `http://localhost/<nombre-carpeta>/login.php`
- **Usuario administrador de prueba:** `admin@bufete.com` / `Admin123!`
- **Usuario abogado de prueba:** `carlos.restrepo@bufete.com` / `Abogado123!`

## 15.4 Flujo básico de uso

1. Iniciar sesión.
2. Crear un repositorio/caso desde Repositorios.
3. Cargar documentos (PDF, DOCX o TXT) dentro del repositorio, desde Documentos.
4. Procesar el documento (botón de procesamiento en Documentos) para que pase por extracción + IA y quede en estado Procesado.
5. Buscar contenido desde Búsqueda o hacer preguntas en lenguaje natural desde Preguntar a la IA.

## 15.5 Solución de problemas comunes

| Síntoma | Causa probable / solución |
|---|---|
| Error de extracción en PDF | Falta ejecutar `composer require smalot/pdfparser`, o el PDF está escaneado como imagen (sin texto real) |
| "No se ha configurado la API key de Gemini" | `config/gemini.php` sigue con el valor de ejemplo `PEGA_AQUI_TU_API_KEY` |
| Documento queda en estado Error | Revisar la tabla `logs_procesamiento` para el mensaje exacto (`tipo_error`: `extraccion`, `clasificacion_ia` o `almacenamiento`) |
| No aparecen resultados al preguntar a la IA | El documento debe estar en `estado_procesamiento = 'procesado'`; documentos pendientes no se consideran en la búsqueda de contexto |
