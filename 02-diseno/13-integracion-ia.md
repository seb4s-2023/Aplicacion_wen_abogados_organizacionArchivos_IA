# 13. Diseño de la integración con IA

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 13.1 Servicio y modelo utilizado

El sistema utiliza la API de Google Gemini (endpoint `generateContent`) como motor de Inteligencia Artificial, invocada mediante peticiones HTTP POST mediante cURL desde `ia.php`. Se eligió esta API por su capa gratuita generosa, su buen desempeño en tareas de clasificación y resumen sobre texto largo en español, y por no requerir infraestructura propia de entrenamiento o inferencia de modelos.

## 13.2 Técnica: prompting estructurado + RAG básico por palabras clave

El proyecto no implementa embeddings ni una base de datos vectorial (explícitamente fuera del alcance definido en la sección 4.2 del documento de Análisis). En su lugar, se utilizan dos técnicas complementarias:

- **Prompting estructurado con salida JSON forzada:** para clasificación, resumen y extracción de datos clave, el prompt exige explícitamente una estructura JSON fija, lo que permite un parseo confiable con `json_decode()` sin necesidad de post-procesamiento complejo.
- **RAG (Retrieval-Augmented Generation) simplificado:** para las preguntas en lenguaje natural, la "recuperación" (Retrieval) se implementa con búsqueda de palabras clave (`LIKE`) sobre `resumen` y `texto_extraido` de documentos ya procesados, en vez de similitud semántica vectorial. Los documentos recuperados se insertan como contexto en el prompt, y se instruye explícitamente al modelo a responder solo con base en ese contexto y a declarar cuando la información no está disponible, reduciendo el riesgo de alucinaciones.

Esta decisión se documenta y justifica: para el volumen de documentos manejado por un bufete de tamaño mediano (decenas a cientos de documentos), la búsqueda por palabras clave ofrece una precisión suficiente con una complejidad de implementación mucho menor que una solución vectorial, cumpliendo el requisito del proyecto integrador de "justificar técnicamente la solución adoptada" sin necesidad de implementar todos los conceptos recomendados (RAG completo, embeddings, bases vectoriales).

## 13.3 Justificación técnica exigida por el proyecto

El flujo implementado cumple explícitamente la exigencia de la sección 5 del enunciado ("archivo → extracción de contenido → procesamiento IA → análisis → almacenamiento de resultados → búsqueda/consulta → respuesta"):

`extractor.php` (extracción) → `ia.php` (procesamiento IA) → `procesador.php` (análisis y orquestación) → `analisis_documento` (almacenamiento) → `buscar.php` / `consulta_ia.php` (búsqueda y consulta) → respuesta mostrada al usuario.

No se trata de un CRUD con un botón "IA" aislado: cada paso persiste su resultado y es consumido por el siguiente.
