# 14. Análisis de riesgos del proyecto

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

| Riesgo | Probabilidad | Impacto | Estrategia de mitigación |
|---|---|---|---|
| Exposición accidental de la API key de Gemini o de credenciales de base de datos en el repositorio Git | Media | Alto | Externalizar credenciales a variables de entorno / archivo de configuración fuera de control de versiones (`.gitignore`); rotar la API key si se detecta exposición. |
| Cambios o límites en la API de Gemini (cuotas, disponibilidad del modelo, cambios de precios) | Media | Medio | Aislar la integración en `ia.php` detrás de funciones propias (`analizarDocumentoConIA`, `responderPreguntaConIA`) para poder cambiar de proveedor de IA con impacto acotado; manejar errores de forma controlada (timeout, HTTP code). |
| Respuestas de la IA que no cumplan el formato JSON esperado | Media | Medio | Limpieza defensiva de la respuesta (remoción de ```` ```json ````), validación con `json_decode` y manejo explícito de error si no es un JSON válido (`extraerJSONDeRespuesta`). |
| Documentos PDF escaneados como imagen sin texto extraíble | Alta | Medio | Detección explícita de texto vacío tras la extracción, con mensaje de error claro al usuario; se documenta como limitación conocida (fuera de alcance el OCR). |
| Búsqueda por LIKE con bajo rendimiento al crecer el volumen de documentos | Media | Medio | Limitar resultados (`LIMIT 50` en búsqueda, `LIMIT 5` en RAG), y documentar como mejora futura la migración a búsqueda de texto completo o vectorial. |
| Fuga de información entre repositorios/usuarios por falta de validación de permisos | Baja | Alto | Uso sistemático de `requireLogin()`/`requireRole()`/`esAdmin()` antes de cualquier operación sensible; sentencias preparadas para evitar manipulación de parámetros. |
| Pérdida de disponibilidad del entorno local (XAMPP) durante la sustentación | Baja | Alto | Preparar entorno de respaldo (segunda máquina o entorno de despliegue) y respaldo de la base de datos (`bufete_ia.sql`) para reconstrucción rápida. |
| Insuficiencia de los 30 documentos de prueba para cubrir las tres categorías exigidas | Baja | Medio | Curar el repositorio documental de prueba desde el inicio del proyecto, distribuyendo documentos equilibradamente entre contrato, acta y concepto_juridico. |
