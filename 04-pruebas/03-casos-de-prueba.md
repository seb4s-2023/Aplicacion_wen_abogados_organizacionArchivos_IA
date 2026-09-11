# 3. Casos de prueba documentados

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Se documentaron **21 casos de prueba** (superando el mínimo de 10 exigido), distribuidos en siete categorías. Cada caso incluye ficha completa con ID, tipo de prueba, módulo/archivo, objetivo, precondiciones, datos de entrada, pasos de ejecución, resultado esperado, resultado obtenido y estado. Las fichas completas de cada categoría se documentan en sus respectivos archivos.

| ID | Categoría | Objetivo |
|---|---|---|
| CP-01 | Pruebas funcionales | Verificar el inicio de sesión exitoso con credenciales válidas del rol administrador. |
| CP-02 | Pruebas funcionales | Verificar que el sistema rechace un inicio de sesión con contraseña incorrecta. |
| CP-03 | Pruebas funcionales | Verificar que el cierre de sesión invalide el acceso a páginas protegidas. |
| CP-04 | Pruebas funcionales | Verificar la creación de un nuevo repositorio (caso/carpeta) por un usuario autenticado. |
| CP-05 | Pruebas de validación de archivos | Verificar la subida exitosa de un documento en formato permitido y dentro del tamaño máximo. |
| CP-06 | Pruebas de validación de archivos | Verificar que el sistema rechace un archivo que excede el tamaño máximo permitido (20 MB). |
| CP-07 | Pruebas de validación de archivos | Verificar que el sistema rechace formatos de archivo no soportados. |
| CP-08 | Pruebas del procesamiento de IA | Verificar que un documento en estado 'pendiente' se procese correctamente al invocar la función `procesarDocumento()`. |
| CP-09 | Pruebas del procesamiento de IA | Verificar el manejo de errores cuando la API de Gemini no responde o responde con error. |
| CP-10 | Pruebas de clasificación y extracción | Verificar que un documento tipo acta sea clasificado en la categoría correcta por la IA. |
| CP-11 | Pruebas de clasificación y extracción | Verificar que los datos clave extraídos (partes, fechas, montos, vigencia) se muestren correctamente en el modal de detalle. |
| CP-12 | Pruebas de búsqueda y preguntas | Verificar que la búsqueda por palabra clave devuelva los documentos relevantes con el término resaltado. |
| CP-13 | Pruebas de búsqueda y preguntas | Verificar el filtrado combinado de búsqueda por categoría y repositorio. |
| CP-14 | Pruebas de búsqueda y preguntas | Verificar que la IA responda preguntas en lenguaje natural usando solo documentos ya procesados. |
| CP-15 | Pruebas de seguridad básicas | Verificar que no se pueda acceder a páginas protegidas sin haber iniciado sesión. |
| CP-16 | Pruebas de seguridad básicas | Verificar que un usuario con rol 'abogado' no pueda eliminar un repositorio creado por otro usuario. |
| CP-17 | Pruebas de seguridad básicas | Verificar que el sistema sea resistente a inyección SQL básica en los campos de entrada. |
| CP-18 | Pruebas de seguridad básicas | Verificar que el sistema neutralice intentos básicos de Cross-Site Scripting (XSS) almacenado. |
| CP-19 | Pruebas de errores y casos límite | Verificar el comportamiento del sistema al enviar el formulario de subida sin adjuntar ningún archivo. |
| CP-20 | Pruebas de errores y casos límite | Verificar el comportamiento del sistema al realizar una búsqueda con el campo de término vacío. |
| CP-21 | Pruebas de errores y casos límite | Verificar el comportamiento del sistema al intentar descargar un documento con un id inexistente. |
