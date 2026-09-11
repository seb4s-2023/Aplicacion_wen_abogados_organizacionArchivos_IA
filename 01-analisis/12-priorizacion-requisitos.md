# 12. Priorización de requisitos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Se utilizó el método **MoSCoW** (Must have / Should have / Could have / Won't have) para priorizar los requerimientos funcionales frente al alcance funcional mínimo exigido por el proyecto integrador.

| ID | Requerimiento | Prioridad | Justificación |
|---|---|---|---|
| RF-01 a RF-05 | Autenticación y roles | Must have | Crítica para el MVP |
| RF-06 a RF-09 | Gestión de repositorios | Must have | Crítica para el MVP |
| RF-10 a RF-13 | Gestión de documentos | Must have | Crítica para el MVP |
| RF-14 a RF-21 | Procesamiento y análisis con IA (extracción, clasificación, resumen, datos clave) | Must have | Crítica para el MVP |
| RF-22, RF-23 | Búsqueda por contenido | Must have | Crítica para el MVP |
| RF-24 a RF-27 | Consulta en lenguaje natural (RAG básico) | Must have | Crítica para el MVP |
| RF-28 | Dashboard con indicadores | Should have | Importante, no bloqueante |
| RF-29 | Registro de errores de procesamiento | Must have | Crítica para el MVP |
| — | Reprocesamiento manual de un documento ya procesado | Should have | Mejora la operación pero no es indispensable para el flujo mínimo |
| — | Búsqueda semántica con embeddings / base de datos vectorial | Could have | Mencionada como recomendación del proyecto integrador, no obligatoria; se documenta como trabajo futuro |
| — | OCR sobre PDFs escaneados como imagen | Won't have | Fuera del alcance mínimo definido en la sección 4.2 de este documento |
