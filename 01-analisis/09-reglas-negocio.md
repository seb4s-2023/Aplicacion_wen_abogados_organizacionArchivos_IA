# 9. Reglas de negocio

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

| ID | Regla |
|---|---|
| RN-01 | Un usuario tiene exactamente un rol: admin o abogado (ENUM en la tabla `usuarios`); no existen roles combinados. |
| RN-02 | Solo el creador de un repositorio (`id_usuario_creador`) o un usuario con rol admin pueden eliminarlo. |
| RN-03 | Al eliminar un repositorio se eliminan en cascada todos sus documentos, y al eliminar un documento se elimina en cascada su análisis (`ON DELETE CASCADE`). |
| RN-04 | Un documento solo puede tener una categoría entre: `contrato`, `acta`, `concepto_juridico`, `sin_clasificar`. Si la IA no puede clasificarlo con certeza, se asigna `sin_clasificar`. |
| RN-05 | Un documento solo puede estar en uno de tres estados de procesamiento a la vez: pendiente, procesado o error. |
| RN-06 | La IA no debe inventar datos clave (partes, fechas, montos, vigencia); si un dato no aparece en el documento, el campo correspondiente debe quedar vacío. |
| RN-07 | Las respuestas del módulo de consulta en lenguaje natural deben basarse únicamente en los documentos recuperados como contexto; si la información no está en ellos, el sistema debe indicarlo explícitamente en vez de inventar una respuesta. |
| RN-08 | Solo se consideran documentos con `estado_procesamiento = 'procesado'` como fuente válida para el módulo de preguntas en lenguaje natural, ya que son los únicos con texto y resumen disponibles. |
| RN-09 | Un documento puede reprocesarse; en ese caso, el análisis anterior se actualiza (no se duplica) en `analisis_documento`. |
| RN-10 | Toda pregunta o búsqueda realizada por un usuario debe quedar registrada en el historial de `consultas_ia`, incluyendo los casos en que el procesamiento de IA falla. |
