# 10. Historias de usuario con criterios de aceptación

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## HU-01 — Inicio de sesión

Como usuario del bufete (admin o abogado), quiero iniciar sesión con mi correo y contraseña, para acceder únicamente a la información que me corresponde.

**Criterios de aceptación:**

- Dado un correo y contraseña válidos de un usuario activo, cuando envío el formulario, entonces accedo al dashboard (`index.php`).
- Dado un correo o contraseña incorrectos, cuando envío el formulario, entonces veo el mensaje "Correo o contraseña incorrectos" y permanezco en `login.php`.
- Dado que ya tengo sesión activa, cuando visito `login.php`, entonces soy redirigido automáticamente al dashboard.

## HU-02 — Creación de repositorio

Como abogado, quiero crear un repositorio para un caso nuevo, para organizar los documentos de ese caso de forma independiente.

**Criterios de aceptación:**

- Dado un nombre no vacío de máximo 150 caracteres, cuando creo el repositorio, entonces aparece en el listado con mi nombre como creador.
- Dado un nombre vacío, cuando intento crear el repositorio, entonces veo el mensaje "El nombre del repositorio es obligatorio" y no se crea el registro.

## HU-03 — Carga de documento

Como abogado, quiero cargar un documento PDF, DOCX o TXT a un repositorio, para que quede disponible para su procesamiento y consulta posterior.

**Criterios de aceptación:**

- Dado un archivo en formato soportado, cuando lo cargo, entonces queda registrado en la tabla `documentos` con `estado_procesamiento = 'pendiente'`.
- Dado un archivo en formato no soportado, cuando intento cargarlo, entonces el sistema rechaza la carga e informa el error.

## HU-04 — Procesamiento automático con IA

Como abogado, quiero procesar un documento con un clic, para obtener su clasificación, resumen y datos clave sin leerlo completo.

**Criterios de aceptación:**

- Dado un documento pendiente, cuando ejecuto "Procesar", entonces el sistema extrae su texto, lo envía a Gemini y guarda categoría, resumen y datos clave.
- Dado que el análisis fue exitoso, cuando reviso el documento, entonces su estado cambia a `'procesado'` y muestra la categoría asignada.
- Dado que la extracción o el análisis fallan, cuando se ejecuta el procesamiento, entonces el documento queda en estado `'error'` y el motivo queda registrado en `logs_procesamiento`.

## HU-05 — Búsqueda documental

Como usuario del bufete, quiero buscar un término dentro del nombre, resumen o contenido de los documentos, para encontrar rápidamente el documento que necesito sin recordar su nombre exacto.

**Criterios de aceptación:**

- Dado un término de búsqueda, cuando lo ejecuto, entonces veo la lista de documentos cuyo nombre, resumen o texto extraído lo contienen, con el término resaltado.
- Dado que aplico un filtro de categoría o repositorio, cuando busco, entonces los resultados respetan ambos filtros combinados con el término de búsqueda.

## HU-06 — Consulta en lenguaje natural

Como abogado, quiero preguntar en lenguaje natural sobre el contenido de mis documentos procesados, para obtener una respuesta directa sin tener que leer los documentos completos.

**Criterios de aceptación:**

- Dado que existen documentos procesados relacionados con la pregunta, cuando la envío, entonces recibo una respuesta basada en su contenido y veo los documentos consultados.
- Dado que no hay documentos procesados relacionados, cuando envío la pregunta, entonces el sistema indica que no encontró documentos relacionados en vez de inventar una respuesta.
- Toda pregunta enviada, exitosa o fallida, queda registrada en mi historial de consultas.

## HU-07 — Eliminación de repositorio

Como administrador, quiero eliminar cualquier repositorio del bufete, incluso si no lo creé yo, para poder depurar información duplicada u obsoleta.

**Criterios de aceptación:**

- Dado que tengo rol admin, cuando solicito eliminar cualquier repositorio, entonces el sistema lo elimina junto con sus documentos, análisis y logs asociados, previa confirmación.
- Dado que tengo rol abogado y el repositorio no es mío, cuando intento eliminarlo, entonces el sistema lo rechaza con el mensaje "No tienes permiso para eliminar ese repositorio".
