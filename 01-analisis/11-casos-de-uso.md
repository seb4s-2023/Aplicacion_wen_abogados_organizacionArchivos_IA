# 11. Casos de uso y especificaciones de los casos de uso

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

A continuación se listan los casos de uso principales del sistema, derivados de los requerimientos funcionales de las secciones 7 y 10.

### Diagrama general de casos de uso (descripción textual)

**Actores:** Administrador, Abogado (ambos heredan de "Usuario autenticado") y el sistema externo Google Gemini API.

**Casos de uso principales:** Iniciar sesión, Cerrar sesión, Gestionar repositorios (crear/eliminar/listar), Gestionar documentos (cargar/consultar/descargar/eliminar), Procesar documento con IA (incluye Extraer texto y Analizar con IA como sub-flujos), Buscar documentos, Consultar en lenguaje natural (incluye Recuperar documentos relevantes y Generar respuesta con IA), Ver dashboard.

---

## CU-01 — Iniciar sesión

| Campo | Detalle |
|---|---|
| **Actor(es)** | Abogado, Administrador |
| **Precondiciones** | El usuario posee una cuenta activa en la tabla `usuarios`. |
| **Flujo principal** | 1. El usuario ingresa a `login.php`.<br>2. El usuario digita correo y contraseña y envía el formulario.<br>3. El sistema busca el usuario activo por correo.<br>4. El sistema valida la contraseña con `password_verify()`.<br>5. Si es válida, el sistema crea la sesión (`$_SESSION`) con `id_usuario`, nombre y rol, y redirige a `index.php`. |
| **Postcondiciones** | El usuario queda autenticado y puede navegar por los módulos protegidos. |
| **Excepciones / flujos alternos** | Correo/contraseña incorrectos o usuario inactivo: se muestra "Correo o contraseña incorrectos" y no se crea sesión. |

## CU-02 — Procesar documento con IA

| Campo | Detalle |
|---|---|
| **Actor(es)** | Abogado, Administrador, Google Gemini API |
| **Precondiciones** | El documento existe en la tabla `documentos` con `estado_procesamiento` distinto de `'procesado'`, o el usuario solicita reprocesarlo. |
| **Flujo principal** | 1. El usuario selecciona un documento y ejecuta "Procesar" desde `documentos.php`.<br>2. El sistema invoca `procesarDocumento()`, que obtiene la ruta y formato del documento.<br>3. El sistema invoca `extraerTexto()` (`extractor.php`) según el formato (txt/docx/pdf).<br>4. El sistema invoca `analizarDocumentoConIA()` (`ia.php`), que construye el prompt y llama a Gemini vía `llamarGemini()`.<br>5. El sistema interpreta el JSON de respuesta (categoría, resumen, datos_clave).<br>6. El sistema guarda o actualiza el registro en `analisis_documento` (`guardarAnalisis()`).<br>7. El sistema actualiza `documentos.categoria` y `documentos.estado_procesamiento = 'procesado'`. |
| **Postcondiciones** | El documento queda clasificado, resumido y con datos clave disponibles para búsqueda y consulta en lenguaje natural. |
| **Excepciones / flujos alternos** | - Fallo en extracción de texto: se registra en `logs_procesamiento` (tipo `'extraccion'`) y el documento queda en estado `'error'`.<br>- Fallo en la llamada a Gemini o JSON inválido: se registra en `logs_procesamiento` (tipo `'clasificacion_ia'`) y el documento queda en estado `'error'`.<br>- Fallo al guardar en base de datos: se registra en `logs_procesamiento` (tipo `'almacenamiento'`) y el documento queda en estado `'error'`. |

## CU-03 — Consultar en lenguaje natural

| Campo | Detalle |
|---|---|
| **Actor(es)** | Abogado, Administrador, Google Gemini API |
| **Precondiciones** | Existen uno o más documentos con `estado_procesamiento = 'procesado'`. |
| **Flujo principal** | 1. El usuario escribe una pregunta en `consulta_ia.php` y la envía.<br>2. El sistema ejecuta `buscarDocumentosRelevantes()`, que filtra palabras clave (≥4 caracteres) de la pregunta.<br>3. El sistema construye una consulta SQL que busca esas palabras en `resumen` y `texto_extraido` de documentos procesados, ordenando por relevancia.<br>4. El sistema arma el contexto (hasta 5 documentos, 6000 caracteres c/u) y lo envía junto con la pregunta a `responderPreguntaConIA()` (`ia.php`).<br>5. Gemini responde con base exclusivamente en el contexto entregado.<br>6. El sistema muestra la respuesta y los documentos consultados, y guarda la pregunta/respuesta en `consultas_ia`. |
| **Postcondiciones** | El usuario obtiene una respuesta fundamentada en sus documentos, y la interacción queda registrada en el historial. |
| **Excepciones / flujos alternos** | - No se encuentran documentos relevantes: el sistema responde que no encontró documentos relacionados, sin llamar a Gemini.<br>- Falla la llamada a Gemini: se muestra el mensaje de error y se registra igualmente en `consultas_ia` con el prefijo `[ERROR]`. |

## CU-04 — Buscar documentos

| Campo | Detalle |
|---|---|
| **Actor(es)** | Abogado, Administrador |
| **Precondiciones** | El usuario tiene sesión activa. |
| **Flujo principal** | 1. El usuario ingresa un término de búsqueda en `buscar.php`, con filtros opcionales de categoría y repositorio.<br>2. El sistema ejecuta una consulta SQL con `LIKE` sobre `nombre_original`, `resumen` y `texto_extraido`, aplicando los filtros indicados.<br>3. El sistema resalta el término encontrado y muestra un fragmento de contexto de cada resultado.<br>4. El sistema registra la búsqueda en `consultas_ia` con el prefijo `"[Búsqueda]"`. |
| **Postcondiciones** | El usuario visualiza hasta 50 documentos coincidentes, ordenados por fecha de carga descendente. |
| **Excepciones / flujos alternos** | Sin resultados: se muestra el mensaje "No se encontraron documentos que coincidan con tu búsqueda". |

## CU-05 — Gestionar repositorios

| Campo | Detalle |
|---|---|
| **Actor(es)** | Abogado, Administrador |
| **Precondiciones** | El usuario tiene sesión activa. |
| **Flujo principal** | 1. El usuario accede a `repositorios.php`.<br>2. El usuario crea un repositorio nuevo indicando nombre y descripción, o solicita eliminar uno existente.<br>3. El sistema valida los datos y ejecuta la operación (INSERT o DELETE) mediante PDO.<br>4. El sistema redirige con un mensaje de confirmación (patrón POST-REDIRECT-GET). |
| **Postcondiciones** | El listado de repositorios refleja la operación realizada. |
| **Excepciones / flujos alternos** | Eliminación sin permiso (no es admin ni creador): redirección con error `'sin_permiso'` y el repositorio no se elimina. |
