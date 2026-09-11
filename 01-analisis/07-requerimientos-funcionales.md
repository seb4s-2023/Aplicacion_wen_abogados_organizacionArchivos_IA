# 7. Requerimientos funcionales

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Los requerimientos funcionales se numeran con el prefijo **RF** y están agrupados por módulo. Cada uno es verificable directamente contra el comportamiento observado en el código fuente del sistema.

## 7.1 Autenticación y control de usuarios

| ID | Descripción |
|---|---|
| RF-01 | El sistema debe permitir a un usuario registrado iniciar sesión con correo electrónico y contraseña, validando la contraseña contra un hash almacenado (`password_verify` sobre `password_hash`). |
| RF-02 | El sistema debe rechazar el inicio de sesión de usuarios inactivos (campo `activo = 0`) o con credenciales incorrectas, mostrando un mensaje de error genérico. |
| RF-03 | El sistema debe impedir el acceso a cualquier página protegida (repositorios, documentos, búsqueda, consulta IA, dashboard) si no existe una sesión activa, redirigiendo al login. |
| RF-04 | El sistema debe permitir cerrar sesión, destruyendo los datos de sesión y redirigiendo al login. |
| RF-05 | El sistema debe distinguir dos roles (admin, abogado) y restringir acciones administrativas (p. ej. eliminar repositorios ajenos) exclusivamente al rol admin. |

## 7.2 Gestión de repositorios

| ID | Descripción |
|---|---|
| RF-06 | El sistema debe permitir crear un repositorio indicando nombre (obligatorio, máx. 150 caracteres) y descripción (opcional, máx. 255 caracteres), asociándolo al usuario que lo crea. |
| RF-07 | El sistema debe listar todos los repositorios existentes, mostrando nombre, descripción, creador, fecha de creación y número de documentos asociados. |
| RF-08 | El sistema debe permitir eliminar un repositorio únicamente a su creador o a un usuario administrador, eliminando en cascada sus documentos, análisis y logs asociados. |
| RF-09 | El sistema debe impedir la eliminación de un repositorio por parte de un usuario sin permiso, mostrando un mensaje de error. |

## 7.3 Gestión de documentos

| ID | Descripción |
|---|---|
| RF-10 | El sistema debe permitir cargar archivos en formato PDF, DOCX o TXT dentro de un repositorio, registrando nombre original, nombre físico, ruta, formato y usuario que lo carga. |
| RF-11 | El sistema debe permitir consultar el listado de documentos de un repositorio y descargar el archivo original. |
| RF-12 | El sistema debe permitir eliminar un documento del repositorio. |
| RF-13 | Todo documento cargado debe iniciar con `estado_procesamiento = 'pendiente'` y `categoria = 'sin_clasificar'` hasta que sea procesado. |

## 7.4 Procesamiento y análisis con IA

| ID | Descripción |
|---|---|
| RF-14 | El sistema debe extraer el texto plano de cada documento soportado (TXT por lectura directa con detección de codificación; DOCX leyendo `word/document.xml` dentro del ZIP; PDF mediante la librería `smalot/pdfparser`). |
| RF-15 | El sistema debe enviar el texto extraído a la API de Google Gemini junto con un prompt que exige una respuesta en JSON estricto con categoría, resumen y datos clave. |
| RF-16 | El sistema debe clasificar cada documento en una de las categorías: `contrato`, `acta`, `concepto_juridico` o `sin_clasificar`. |
| RF-17 | El sistema debe generar un resumen de máximo 5 líneas por documento, en español. |
| RF-18 | El sistema debe extraer datos clave estructurados: partes involucradas, fechas relevantes, montos y vigencia u obligaciones, dejando vacíos los campos no encontrados en el documento (sin inventar información). |
| RF-19 | El sistema debe almacenar el resultado del análisis (resumen, texto extraído y datos clave en JSON) en la tabla `analisis_documento`, actualizando el registro si el documento ya había sido procesado antes. |
| RF-20 | El sistema debe actualizar la categoría del documento y marcar su `estado_procesamiento` como `'procesado'` al finalizar exitosamente el análisis. |
| RF-21 | Si la extracción de texto, el análisis con IA o el almacenamiento fallan, el sistema debe registrar el error en `logs_procesamiento` y marcar el documento con `estado_procesamiento = 'error'`. |

## 7.5 Búsqueda y consulta en lenguaje natural

| ID | Descripción |
|---|---|
| RF-22 | El sistema debe permitir buscar documentos por texto libre, comparando contra nombre original, resumen y texto extraído, con filtros opcionales por categoría y por repositorio. |
| RF-23 | El sistema debe resaltar visualmente el término buscado dentro del nombre del documento y del fragmento de contexto mostrado. |
| RF-24 | El sistema debe permitir formular preguntas en lenguaje natural sobre los documentos ya procesados; debe ubicar los documentos relevantes buscando coincidencias de palabras significativas (≥4 caracteres) en resumen y texto extraído. |
| RF-25 | El sistema debe enviar a Gemini la pregunta del usuario junto con el contenido de los documentos relevantes como contexto, indicando explícitamente al modelo que responda solo con base en ese contexto y que aclare si la respuesta no se encuentra en los documentos. |
| RF-26 | El sistema debe registrar cada pregunta y respuesta (o error) en la tabla `consultas_ia`, asociada al usuario que la formuló, y mostrar un historial de las últimas 10 preguntas del usuario. |
| RF-27 | El sistema debe registrar también las búsquedas realizadas en `buscar.php` como consultas de uso, con el prefijo `"[Búsqueda]"`, diferenciándolas del historial de preguntas a la IA. |

## 7.6 Dashboard y trazabilidad

| ID | Descripción |
|---|---|
| RF-28 | El sistema debe presentar un dashboard con indicadores del repositorio documental (p. ej. total de documentos, documentos por estado de procesamiento y por categoría). |
| RF-29 | El sistema debe mantener un registro (`logs_procesamiento`) de los errores ocurridos durante el procesamiento de cada documento, incluyendo tipo de error y mensaje. |
