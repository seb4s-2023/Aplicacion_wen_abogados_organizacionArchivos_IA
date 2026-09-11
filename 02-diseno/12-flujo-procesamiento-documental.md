# 12. Flujo de procesamiento documental

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El flujo de procesamiento documental —eje central del requisito de IA del proyecto integrador— sigue siempre la misma secuencia, implementada en `procesarDocumento()` (`procesador.php`):

1. El documento existe en la tabla `documentos` con `estado_procesamiento = 'pendiente'` (o se solicita reprocesar).
2. **Extracción de texto** (`extractor.php`): según el campo `formato`, se invoca `extraerTextoTXT()`, `extraerTextoDOCX()` o `extraerTextoPDF()`; el resultado se normaliza (`limpiarTexto()`) y se trunca a 100.000 caracteres.
3. **Análisis con IA** (`ia.php`): se construye un prompt estructurado (`construirPromptAnalisis()`) que exige a Gemini responder en JSON estricto con `categoria`, `resumen` y `datos_clave`; se invoca `llamarGemini()` vía cURL.
4. **Interpretación de la respuesta**: se limpia el posible envoltorio Markdown (```` ```json ````) y se decodifica con `json_decode()`; si la categoría no es válida, se usa `'sin_clasificar'`.
5. **Almacenamiento**: se inserta o actualiza el registro en `analisis_documento` (`guardarAnalisis()`) y se actualiza `documentos.categoria` y `estado_procesamiento = 'procesado'`.
6. **Manejo de errores**: cualquier falla en los pasos 2 a 5 se registra en `logs_procesamiento` con el tipo de error correspondiente (`extraccion`, `clasificacion_ia`, `almacenamiento`) y el documento queda en estado `'error'`, sin bloquear el resto del sistema.
7. **Disponibilidad para búsqueda/consulta**: una vez `'procesado'`, el documento queda disponible para `buscar.php` (búsqueda por palabra clave) y `consulta_ia.php` (recuperación de contexto para preguntas en lenguaje natural).
