# 9. Pruebas de errores y casos límite

Este bloque valida que entradas vacías, inexistentes o inválidas no generen errores fatales ni comportamientos inesperados.

## CP-19 — Subida de archivo sin adjuntar ningún archivo

| Campo | Detalle |
|---|---|
| **ID** | CP-19 |
| **Tipo de prueba** | Errores y casos límite |
| **Módulo / archivo** | `documentos.php` |
| **Objetivo** | Verificar el comportamiento del sistema al enviar el formulario de subida sin adjuntar ningún archivo. |
| **Precondiciones** | Repositorio válido seleccionado. |
| **Datos de entrada** | Formulario de subida enviado sin archivo adjunto. |
| **Pasos de ejecución** | 1) Seleccionar un repositorio. 2) Dejar el campo de archivo vacío. 3) Enviar. |
| **Resultado esperado** | El sistema detecta `UPLOAD_ERR_NO_FILE` y muestra "Debes seleccionar un archivo" sin generar error fatal. |
| **Resultado obtenido** | Conforme: el condicional captura el caso antes de intentar mover un archivo inexistente. |
| **Estado** | ✅ Aprobado |

## CP-20 — Búsqueda con término vacío

| Campo | Detalle |
|---|---|
| **ID** | CP-20 |
| **Tipo de prueba** | Errores y casos límite |
| **Módulo / archivo** | `buscar.php` |
| **Objetivo** | Verificar el comportamiento del sistema al realizar una búsqueda con el campo de término vacío. |
| **Precondiciones** | Ninguna. |
| **Datos de entrada** | `q = ''` (cadena vacía) |
| **Pasos de ejecución** | 1) Ingresar a `buscar.php` sin diligenciar el término. 2) Enviar el formulario. |
| **Resultado esperado** | El sistema no ejecuta la consulta contra la base de datos y muestra el mensaje orientativo para escribir un término de búsqueda. |
| **Resultado obtenido** | Conforme: el bloque `if ($q !== '')` evita consultas innecesarias y no se registra una búsqueda vacía en `consultas_ia`. |
| **Estado** | ✅ Aprobado |

## CP-21 — Descarga de documento con id inexistente

| Campo | Detalle |
|---|---|
| **ID** | CP-21 |
| **Tipo de prueba** | Errores y casos límite |
| **Módulo / archivo** | `documentos.php` (descarga) |
| **Objetivo** | Verificar el comportamiento del sistema al intentar descargar un documento con un id inexistente. |
| **Precondiciones** | Ninguna. |
| **Datos de entrada** | `documentos.php?descargar=99999` (id que no existe en la base de datos) |
| **Pasos de ejecución** | 1) Modificar manualmente el parámetro `descargar` en la URL a un id inexistente. 2) Enviar la petición. |
| **Resultado esperado** | El sistema redirige con `error=archivo_no_encontrado` en lugar de generar un error fatal de PHP. |
| **Resultado obtenido** | Conforme: la validación `!$doc \|\| !is_file($rutaCompleta)` controla ambos casos (registro inexistente y archivo físico faltante). |
| **Estado** | ✅ Aprobado |

## Resumen del bloque

| Tipo de prueba | N.° de casos | Aprobados |
|---|---|---|
| Errores y casos límite | 3 | 3 |

**Requisitos cubiertos:** RF-10 (manejo controlado de errores y casos límite en la operación de archivos).
