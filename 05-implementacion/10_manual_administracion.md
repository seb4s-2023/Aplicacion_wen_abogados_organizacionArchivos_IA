# 10. Manual de administración o soporte

## 10.1 Gestión de usuarios

La creación de usuarios se realiza actualmente por inserción directa en la tabla `usuarios` (por ejemplo, desde phpMyAdmin), generando el `password_hash` con `password_hash()` en PHP antes de insertarlo. No existe todavía una pantalla de administración de usuarios en la interfaz; queda anotado como mejora futura (sección 12).

## 10.2 Revisión de errores de procesamiento

- Ante un documento en estado Error, el administrador puede consultar la tabla `logs_procesamiento` filtrando por `id_documento` para ver el `tipo_error` (`extraccion`, `clasificacion_ia` o `almacenamiento`) y el mensaje exacto.
- Los errores más comunes son: API key de Gemini inválida o vencida, PDF sin texto real (escaneado) y archivos DOCX corruptos o con formato inesperado.

## 10.3 Reprocesar un documento

- Al volver a presionar "Procesar" sobre un documento ya existente, `guardarAnalisis()` actualiza el registro en `analisis_documento` en vez de duplicarlo, por lo que reprocesar es seguro.

## 10.4 Monitoreo básico

- Revisar periódicamente la cantidad de documentos en estado Error desde la base de datos, como indicador de salud del procesamiento de IA.
- Vigilar el consumo de la API de Gemini si el proveedor de la API key aplica límites de uso.
