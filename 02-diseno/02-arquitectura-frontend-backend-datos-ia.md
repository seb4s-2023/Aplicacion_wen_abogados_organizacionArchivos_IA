# 2. Arquitectura frontend, backend, datos, almacenamiento e IA

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 2.1 Frontend

El frontend se implementa con HTML generado por PHP, Bootstrap 5.3 y Bootstrap Icons (cargados vía CDN), sin un framework de JavaScript. La interactividad del lado del cliente se limita a componentes propios de Bootstrap (modales, navbar responsiva) y formularios HTML estándar (GET/POST). Este enfoque simplifica el desarrollo y es consistente con el patrón de renderizado del lado del servidor de todo el proyecto.

## 2.2 Backend

El backend está escrito en PHP procedural organizado por responsabilidad, sin un framework MVC formal, pero respetando una separación de capas:

- Las páginas raíz (`login.php`, `documentos.php`, `repositorios.php`, `buscar.php`, `consulta_ia.php`, `index.php`) actúan como controladores + vistas.
- El directorio `includes/` concentra la lógica reutilizable (`auth.php`, `extractor.php`, `ia.php`, `procesador.php`).
- El directorio `config/` concentra la configuración de infraestructura (`database.php`, `gemini.php`).

## 2.3 Datos y almacenamiento

Los datos estructurados (usuarios, repositorios, metadatos de documentos, análisis de IA, historial de consultas y logs) se almacenan en MySQL, accedidos exclusivamente mediante PDO con sentencias preparadas. Los archivos originales (PDF/DOCX/TXT) se almacenan en el sistema de archivos del servidor (carpeta de uploads), y la base de datos guarda solo su ruta (`documentos.ruta_archivo`), siguiendo el patrón estándar de no almacenar archivos binarios grandes directamente en la base de datos.

## 2.4 Integración de Inteligencia Artificial

La capa de IA se aísla completamente en `includes/ia.php`, que expone funciones de alto nivel (`analizarDocumentoConIA()`, `responderPreguntaConIA()`) y oculta los detalles de la llamada HTTP a la API de Google Gemini (vía cURL) al resto de la aplicación. Esto permite que, si en el futuro se cambia de proveedor de IA, solo sea necesario modificar este archivo.
