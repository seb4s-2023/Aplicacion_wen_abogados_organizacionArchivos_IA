# 3. Diagrama de arquitectura

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

![Diagrama de arquitectura en capas del sistema](./media/diagrama-arquitectura.png)

*Figura 1. Diagrama de arquitectura en capas del sistema.*

El diagrama muestra las cuatro capas de la arquitectura y su interacción:

- **Capa de presentación (Cliente):** el navegador web (HTML + Bootstrap 5 + JS) envía peticiones HTTP (GET/POST) y recibe HTML renderizado.
- **Capa de aplicación (Backend PHP):** las páginas PHP (`login`, `documentos`, `buscar`, `repositorios`, `consulta_ia`) validan la sesión contra `auth.php` y delegan el procesamiento a `procesador.php`, que orquesta la extracción de texto (`extractor.php`) y la integración con IA (`ia.php`).
- **Servicios externos:** `ia.php` se comunica con la API de Google Gemini (`generateContent`) mediante cURL HTTPS, intercambiando JSON.
- **Capa de datos y almacenamiento:** los archivos originales se guardan en el sistema de archivos (`/uploads`, PDF/DOCX/TXT) y los datos estructurados y el resultado del análisis se guardan en MySQL (`bufete_ia`) vía PDO.
