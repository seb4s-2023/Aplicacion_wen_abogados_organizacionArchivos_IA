# 9. Implementación de clasificación, resumen y extracción

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

`analizarDocumentoConIA()` (`includes/ia.php`) construye un prompt (`construirPromptAnalisis()`) que le pide a Gemini responder **únicamente** con un JSON de estructura fija:

```json
{
  "categoria": "contrato" | "acta" | "concepto_juridico" | "sin_clasificar",
  "resumen": "máximo 5 líneas",
  "datos_clave": {
    "partes_involucradas": [...],
    "fechas_relevantes": [...],
    "montos": [...],
    "vigencia_u_obligaciones": "..."
  }
}
```

Reglas explícitas en el prompt: si el documento no encaja en las tres categorías definidas se usa `sin_clasificar`; si un dato clave no aparece en el texto, el campo se deja vacío en vez de inventarse; el resumen debe ser fiel al contenido y sin opiniones.

- **Clasificación:** cubre las 3 categorías mínimas exigidas — contrato, acta y concepto_juridico — más `sin_clasificar` como categoría de respaldo. El backend valida la categoría recibida contra la lista permitida antes de guardarla.
- **Resumen:** se guarda en `analisis_documento.resumen` y se muestra tanto en la búsqueda (`buscar.php`) como en el contexto de las preguntas en lenguaje natural (`consulta_ia.php`).
- **Extracción de información relevante:** `datos_clave` se persiste como JSON en la columna `datos_clave` (tipo JSON de MySQL), cubriendo los tres tipos de dato exigidos como mínimo — partes involucradas, fechas relevantes y montos/vigencia.

`guardarAnalisis()` (`procesador.php`) inserta o actualiza el registro en `analisis_documento` según exista o no un análisis previo para ese documento, lo que permite reprocesar un documento sin duplicar filas.
