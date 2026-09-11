# 14. Registro de avances o bitácora de desarrollo

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

A la fecha de este documento no se llevaba una bitácora formal. Se deja la plantilla que debe diligenciarse retroactivamente (con fechas reales aproximadas) y de forma continua de aquí en adelante, idealmente en conjunto con los commits de Git de la sección anterior.

| Fecha | Módulo / avance | Descripción | Responsable |
|---|---|---|---|
| | Autenticación y sesiones | `auth.php`, `login.php`, `logout.php` | |
| | Repositorios | CRUD de repositorios/casos | |
| | Documentos | Carga, listado, descarga | |
| | Extracción de texto | `extractor.php` (TXT/DOCX/PDF) | |
| | Integración con Gemini | `ia.php`, `config/gemini.php` | |
| | Orquestación de procesamiento | `procesador.php` | |
| | Búsqueda documental | `buscar.php` | |
| | Consulta en lenguaje natural (RAG) | `consulta_ia.php` | |
| | Dashboard | `index.php` | |

**Recomendación:** cada fila puede completarse consultando la fecha de creación/modificación de cada archivo en el sistema operativo, o directamente con la fecha de cada commit una vez el proyecto esté en Git (`git log --follow --format='%ad %s' -- archivo.php`).
