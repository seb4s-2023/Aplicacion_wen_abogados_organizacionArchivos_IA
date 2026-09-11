# 4. Alcance y exclusiones

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 4.1 Alcance

El sistema cubre el ciclo completo descrito en el alcance funcional mínimo del proyecto integrador: autenticación y roles; administración de repositorios; carga, consulta, descarga y eliminación de archivos PDF/DOCX/TXT; extracción de texto; procesamiento automático mediante IA (clasificación, resumen, extracción de datos clave); búsqueda por contenido; consulta en lenguaje natural sobre documentos procesados; y registro de errores/estados de procesamiento. El sistema está dirigido a un bufete de abogados como caso de uso concreto, pero el modelo de datos y el flujo de procesamiento son genéricos y aplicables a cualquier organización que gestione documentos.

## 4.2 Exclusiones

- OCR sobre documentos escaneados como imagen: el sistema solo procesa PDF con texto real (no imágenes escaneadas), DOCX y TXT.
- Búsqueda semántica basada en embeddings o bases de datos vectoriales: la búsqueda y la recuperación de contexto para el módulo de preguntas (RAG) se implementan mediante coincidencia de palabras clave (LIKE) sobre resumen y texto extraído, no mediante similitud vectorial.
- Firma electrónica, flujos de aprobación o integración con sistemas externos de gestión de casos (ERP/CRM jurídico).
- Edición del contenido de los documentos cargados; el sistema es de gestión y análisis, no un editor de texto.
- Aplicaciones móviles nativas; el alcance del proyecto es una aplicación web responsiva.
- Multiidioma: la interfaz y los prompts de IA están en español.
