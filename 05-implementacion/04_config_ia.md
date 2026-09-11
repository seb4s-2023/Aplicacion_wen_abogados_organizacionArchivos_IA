# 4. Configuración de servicios de IA

El único servicio externo de IA que usa el sistema es la **API de Google Gemini** (`generateContent`), invocada por `includes/ia.php` mediante cURL.

- Obtener una API key de Gemini desde la consola de Google AI.
- Configurar `GEMINI_API_KEY` y `GEMINI_API_URL` en `config/gemini.php`.
- Verificar que el servidor tenga salida a Internet (en un hosting compartido puede requerir habilitar el firewall saliente hacia el dominio de la API de Google).
- El sistema ya maneja de forma controlada la ausencia de configuración: si `GEMINI_API_KEY` sigue con el valor de ejemplo `PEGA_AQUI_TU_API_KEY`, tanto `analizarDocumentoConIA()` como `responderPreguntaConIA()` devuelven un error explícito en vez de fallar de forma silenciosa (defecto DEF-01 documentado en el documento 04 — Pruebas, ya corregido).
- No se requiere ninguna infraestructura de IA local (no hay modelos ni bases de datos vectoriales corriendo en el servidor): toda la inferencia ocurre en la nube de Google, lo que simplifica el despliegue.
