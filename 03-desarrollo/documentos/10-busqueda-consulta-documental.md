# 10. Implementación de búsqueda y consulta documental

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 10.1 Búsqueda documental (`buscar.php`)

Búsqueda por coincidencia (`LIKE`) sobre tres campos: `nombre_original`, `resumen` y `texto_extraido`, con filtros opcionales por categoría y por repositorio. Cada resultado muestra un fragmento de contexto alrededor de la primera coincidencia (`fragmentoConContexto()`) con el término resaltado, priorizando el texto extraído y, si no hay coincidencia ahí, el resumen.

## 10.2 Consulta en lenguaje natural — RAG básico (`consulta_ia.php`)

Implementa *Retrieval-Augmented Generation* en su forma más simple, sin embeddings ni base de datos vectorial:

- **Recuperación (Retrieval):** `buscarDocumentosRelevantes()` extrae las palabras significativas de la pregunta (≥4 caracteres, máximo 6, sin duplicados), construye una consulta SQL dinámica que puntúa cada documento procesado según cuántas de esas palabras aparecen en su texto extraído, y devuelve los 5 documentos más relevantes.
- **Aumento del contexto (Augmentation):** por cada documento recuperado se arma un bloque con su nombre, resumen y hasta 6000 caracteres de texto extraído (límite defensivo de tokens), ensamblado por `construirPromptPregunta()`.
- **Generación (Generation):** el prompt instruye a Gemini a responder únicamente con base en los documentos entregados como contexto, a declarar explícitamente cuando la respuesta no está en los documentos (en vez de inventarla), y a citar el nombre del documento fuente entre paréntesis.

Toda pregunta y su respuesta (o el error, prefijado como `[ERROR]`) se guarda en `consultas_ia`, junto con las búsquedas de `buscar.php` (prefijadas `[Búsqueda]`), lo que alimenta el historial visible en `consulta_ia.php` y sirve como evidencia de trazabilidad de uso del sistema.
