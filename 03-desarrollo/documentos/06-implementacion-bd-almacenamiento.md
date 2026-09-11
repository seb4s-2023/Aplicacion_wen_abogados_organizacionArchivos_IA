# 6. Implementación de base de datos y almacenamiento

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

La base de datos `bufete_ia` (MySQL, charset `utf8mb4`) se define en `bufete_ia.sql` con seis tablas relacionadas por llaves foráneas con `ON DELETE CASCADE`, de forma que eliminar un repositorio elimina en cascada sus documentos, análisis y logs asociados.

| Tabla | Propósito |
|---|---|
| `usuarios` | Administradores y abogados; `password_hash` con bcrypt, rol ENUM(admin, abogado) |
| `repositorios` | Carpetas/casos del bufete, ligadas al usuario que las creó |
| `documentos` | Metadatos del archivo cargado: formato, categoría, ruta física y estado de procesamiento |
| `analisis_documento` | Resultado de la IA por documento: resumen, texto extraído y `datos_clave` (JSON) |
| `consultas_ia` | Historial de preguntas en lenguaje natural y de búsquedas registradas |
| `logs_procesamiento` | Registro de errores de extracción, clasificación o almacenamiento por documento |

## Almacenamiento de archivos

Los documentos cargados se guardan físicamente en el sistema de archivos del servidor (carpeta `uploads/`), mientras que la base de datos conserva `nombre_original`, `nombre_archivo` (nombre físico) y `ruta_archivo`. El texto extraído y el análisis de IA se guardan aparte, en `analisis_documento`, para no mezclar el archivo binario con su contenido procesado.

## Acceso a la base de datos

Siempre mediante PDO con sentencias preparadas. En `buscarDocumentosRelevantes()` (`consulta_ia.php`) cada palabra de búsqueda usa un marcador de parámetro con nombre único (`:resumen0`, `:texto0a`, `:texto0b`, ...) porque el driver opera con `PDO::ATTR_EMULATE_PREPARES => false`, que no permite repetir el mismo marcador varias veces en una misma consulta.
