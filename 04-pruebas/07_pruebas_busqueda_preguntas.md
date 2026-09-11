# 7. Pruebas de búsqueda y preguntas sobre documentos

Este bloque valida la búsqueda por palabra clave, los filtros combinados y las respuestas de la IA basadas únicamente en documentos procesados (módulos `buscar.php` y `consulta_ia.php`).

## CP-12 — Búsqueda por palabra clave con resaltado

| Campo | Detalle |
|---|---|
| **ID** | CP-12 |
| **Tipo de prueba** | Búsqueda y preguntas |
| **Módulo / archivo** | `buscar.php` |
| **Objetivo** | Verificar que la búsqueda por palabra clave devuelva los documentos relevantes con el término resaltado. |
| **Precondiciones** | Existen documentos procesados con la palabra "confidencialidad" en su texto extraído. |
| **Datos de entrada** | `q = 'confidencialidad'` |
| **Pasos de ejecución** | 1) Ingresar a `buscar.php`. 2) Escribir el término. 3) Enviar el formulario GET. |
| **Resultado esperado** | Se listan los documentos coincidentes, ordenados por fecha, con el término resaltado mediante `<mark>` en el fragmento de contexto. |
| **Resultado obtenido** | Conforme: la consulta `LIKE` sobre `nombre_original`, `resumen` y `texto_extraido` retorna los documentos esperados con el resaltado aplicado. |
| **Estado** | ✅ Aprobado |

## CP-13 — Filtrado combinado por categoría y repositorio

| Campo | Detalle |
|---|---|
| **ID** | CP-13 |
| **Tipo de prueba** | Búsqueda y preguntas |
| **Módulo / archivo** | `buscar.php` |
| **Objetivo** | Verificar el filtrado combinado de búsqueda por categoría y repositorio. |
| **Precondiciones** | Documentos de distintas categorías en distintos repositorios. |
| **Datos de entrada** | `q = 'servicios'`, `categoria = 'contrato'`, `id_repositorio = 1` |
| **Pasos de ejecución** | 1) Diligenciar término de búsqueda. 2) Seleccionar categoría "Contrato" y un repositorio específico. 3) Enviar. |
| **Resultado esperado** | Solo se muestran documentos que cumplen simultáneamente el término, la categoría y el repositorio indicados. |
| **Resultado obtenido** | Conforme: las condiciones `AND d.categoria = :categoria` y `AND d.id_repositorio = :id_repositorio` se añaden correctamente a la consulta. |
| **Estado** | ✅ Aprobado |

## CP-14 — Consulta en lenguaje natural sobre documentos procesados

| Campo | Detalle |
|---|---|
| **ID** | CP-14 |
| **Tipo de prueba** | Búsqueda y preguntas |
| **Módulo / archivo** | `consulta_ia.php` |
| **Objetivo** | Verificar que la IA responda preguntas en lenguaje natural usando solo documentos ya procesados. |
| **Precondiciones** | Al menos un documento en estado "procesado" relacionado con la pregunta; otros documentos en estado "pendiente". |
| **Datos de entrada** | `pregunta = '¿Cuál es la vigencia del contrato de mantenimiento industrial?'` |
| **Pasos de ejecución** | 1) Ingresar a `consulta_ia.php`. 2) Escribir la pregunta. 3) Enviar. |
| **Resultado esperado** | `buscarDocumentosRelevantes()` solo considera documentos con `estado_procesamiento = 'procesado'`; la respuesta cita los documentos usados y la interacción queda registrada en `consultas_ia`. |
| **Resultado obtenido** | Conforme: los documentos pendientes no se incluyen en el contexto enviado a la IA y el historial se guarda correctamente. |
| **Estado** | ✅ Aprobado |

## Resumen del bloque

| Tipo de prueba | N.° de casos | Aprobados |
|---|---|---|
| Búsqueda y preguntas | 3 | 3 |

**Requisitos cubiertos:** RF-06 (búsqueda por contenido, categoría y repositorio) y RF-07 (consultas en lenguaje natural).
