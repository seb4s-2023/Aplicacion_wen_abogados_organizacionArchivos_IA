# 5. Pruebas de validación de archivos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## CP-05 — Verificar la subida exitosa de un documento en formato permitido y dentro del tamaño máximo

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Validación de archivos |
| **Módulo / archivo** | `documentos.php` |
| **Precondiciones** | Repositorio existente; sesión activa. |
| **Datos de entrada** | Archivo `contrato_prueba.pdf` de 2 MB. |
| **Pasos de ejecución** | 1) Seleccionar repositorio. 2) Adjuntar archivo PDF. 3) Enviar formulario de subida. |
| **Resultado esperado** | El archivo se guarda en `/uploads` con nombre único (`uniqid`), se registra en la tabla `documentos` con `estado_procesamiento = 'pendiente'`. |
| **Resultado obtenido** | Conforme: `move_uploaded_file()` almacena el archivo y el registro queda en estado Pendiente. |
| **Estado** | ✅ Aprobado |

## CP-06 — Verificar que el sistema rechace un archivo que excede el tamaño máximo permitido (20 MB)

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Validación de archivos |
| **Módulo / archivo** | `documentos.php` |
| **Precondiciones** | Repositorio existente; sesión activa. |
| **Datos de entrada** | Archivo de 25 MB en formato PDF. |
| **Pasos de ejecución** | 1) Adjuntar el archivo de 25 MB. 2) Enviar formulario. |
| **Resultado esperado** | El sistema muestra "El archivo supera el tamaño máximo permitido (20 MB)" y no guarda el registro. |
| **Resultado obtenido** | Conforme: la validación `$_FILES['archivo']['size'] > TAMANO_MAXIMO` detiene el flujo antes de mover el archivo. |
| **Estado** | ✅ Aprobado |

## CP-07 — Verificar que el sistema rechace formatos de archivo no soportados

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Validación de archivos |
| **Módulo / archivo** | `documentos.php` |
| **Precondiciones** | Repositorio existente; sesión activa. |
| **Datos de entrada** | Archivo `imagen.jpg` (formato no permitido). |
| **Pasos de ejecución** | 1) Adjuntar un archivo .jpg. 2) Enviar formulario de subida. |
| **Resultado esperado** | El sistema muestra "Formato no soportado. Solo se aceptan PDF, DOCX y TXT" y no almacena el archivo. |
| **Resultado obtenido** | Conforme: la extensión no está en `$formatosPermitidos` y el flujo se detiene antes de mover el archivo. |
| **Estado** | ✅ Aprobado |
