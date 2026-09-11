# 13. Matriz de trazabilidad inicial

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Esta matriz inicial vincula cada grupo de requerimientos funcionales con el objetivo específico que lo origina, el actor responsable y el módulo (archivo) del sistema que lo implementa. Sirve de base para la matriz de trazabilidad requisito–prueba que se desarrollará en el entregable de Pruebas (08).

| Requisitos | Objetivo específico relacionado | Actor(es) | Módulo / archivo |
|---|---|---|---|
| RF-01 a RF-05 | Implementar autenticación y control de acceso basado en roles | Admin, Abogado | `login.php`, `logout.php`, `auth.php` |
| RF-06 a RF-09 | Desarrollar la gestión de repositorios | Admin, Abogado | `repositorios.php` |
| RF-10 a RF-13 | Implementar carga, consulta, descarga y eliminación de documentos | Admin, Abogado | `documentos.php`, `database.php` |
| RF-14, RF-15 | Construir el pipeline de extracción de texto y envío a IA | Sistema / Gemini API | `extractor.php`, `ia.php`, `procesador.php` |
| RF-16 a RF-20 | Clasificar, resumir y extraer datos clave con IA | Sistema / Gemini API | `ia.php`, `procesador.php` |
| RF-21, RF-29 | Registrar errores y estados de procesamiento | Sistema | `procesador.php` (`logs_procesamiento`) |
| RF-22, RF-23 | Implementar búsqueda por contenido | Admin, Abogado | `buscar.php` |
| RF-24 a RF-27 | Implementar consulta en lenguaje natural (RAG) | Admin, Abogado, Gemini API | `consulta_ia.php`, `ia.php` |
| RF-28 | Dashboard con indicadores del repositorio | Admin, Abogado | `index.php` |
