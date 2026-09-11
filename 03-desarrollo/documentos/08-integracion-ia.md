# 8. Integración de Inteligencia Artificial

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El flujo real de IA implementado es:

> *archivo → `extraerTexto()` → `analizarDocumentoConIA()` (Gemini) → `guardarAnalisis()` → `estado_procesamiento = 'procesado'` → disponible para búsqueda y consulta en lenguaje natural.*

**Servicio y técnica utilizados:** Google Gemini (API `generateContent`), consumida directamente por cURL desde `includes/ia.php` — sin SDK adicional. Se eligió Gemini por ofrecer una capa gratuita suficiente para el volumen de pruebas del proyecto académico y por soportar prompts largos con salida en JSON estructurado.

- La función `llamarGemini()` arma el cuerpo de la petición (`contents`, `generationConfig` con `temperature` 0.2 para respuestas consistentes y `maxOutputTokens` 2048), la envía por POST a `GEMINI_API_URL` con la API key como query param, y maneja errores de conexión (cURL) y errores HTTP devueltos por la API.
- A la respuesta cruda se le aplica `extraerJSONDeRespuesta()`, que limpia posibles fences de markdown (```` ```json ````) que el modelo agrega pese a la instrucción explícita de no hacerlo, y decodifica el JSON de forma tolerante.

**Justificación de la técnica frente a alternativas:** no se implementó una base de datos vectorial ni embeddings por dos razones — el volumen de documentos del proyecto (repositorio de prueba de 30 documentos) no lo justifica en términos de costo/beneficio, y el alcance funcional mínimo exige demostrar el flujo completo extracción→IA→almacenamiento→consulta, no necesariamente con búsqueda semántica vectorial. En su lugar se implementó un RAG básico por coincidencia léxica (ver sección 10), documentado como decisión técnica consciente y no como una omisión.
