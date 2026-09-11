# 6. Pruebas del procesamiento de IA

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## CP-08 — Verificar que un documento en estado 'pendiente' se procese correctamente al invocar la función procesarDocumento()

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Procesamiento de IA |
| **Módulo / archivo** | `documentos.php` / `includes/procesador.php` / `config/gemini.php` |
| **Precondiciones** | Documento cargado con `estado_procesamiento = 'pendiente'`; API de Gemini disponible. |
| **Datos de entrada** | Acción POST `accion=procesar` con `id_documento` válido. |
| **Pasos de ejecución** | 1) Ubicar un documento pendiente. 2) Pulsar "Procesar con IA". 3) Confirmar cambio de estado. |
| **Resultado esperado** | Se extrae el texto, se envía a la API de Gemini, se inserta el resultado en `analisis_documento` y `estado_procesamiento` cambia a `'procesado'`. |
| **Resultado obtenido** | Conforme en flujo feliz: el documento pasa a "Procesado" y se genera el registro en `analisis_documento` con resumen y `datos_clave`. |
| **Estado** | ✅ Aprobado |

## CP-09 — Verificar el manejo de errores cuando la API de Gemini no responde o responde con error

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Procesamiento de IA |
| **Módulo / archivo** | `documentos.php` / `config/gemini.php` |
| **Precondiciones** | Documento pendiente; se simula una respuesta de error o tiempo de espera agotado en la API. |
| **Datos de entrada** | Acción POST `accion=procesar` con la API de Gemini forzada a fallar (clave inválida o sin conexión). |
| **Pasos de ejecución** | 1) Provocar un fallo de red o de autenticación hacia la API. 2) Intentar procesar el documento. |
| **Resultado esperado** | El sistema no rompe la aplicación: registra el error en `logs_procesamiento`, deja el documento en estado `'error'` y muestra el mensaje "No se pudo procesar el documento con IA". |
| **Resultado obtenido** | Conforme: el bloque `if ($resultado['exito'])` capta el fallo y redirige con `error=procesamiento&detalle=...` sin exponer una traza de error al usuario final. |
| **Estado** | ✅ Aprobado |
