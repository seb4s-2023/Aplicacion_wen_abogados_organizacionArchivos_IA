# 9. Manual de usuario

Dirigido a los abogados y administradores del bufete que usarán el sistema en su trabajo diario.

## 9.1 Acceso al sistema

- Abrir el navegador e ingresar a la URL del sistema (`login.php`).
- Ingresar el correo electrónico y la contraseña asignados.
- Si las credenciales son incorrectas, el sistema muestra el mensaje "Correo o contraseña incorrectos" sin indicar cuál de los dos campos falló, por seguridad.

## 9.2 Crear y administrar repositorios (casos)

- Desde el menú superior, ir a **Repositorios**.
- Usar el botón "Nuevo repositorio" para crear una carpeta de trabajo (por ejemplo, un caso o cliente), con nombre y descripción opcional.
- Solo el administrador o el usuario que creó el repositorio pueden eliminarlo; al eliminarlo se eliminan también sus documentos y análisis asociados.

## 9.3 Cargar y procesar documentos

- Entrar a un repositorio desde "Abrir" y cargar archivos en formato PDF, DOCX o TXT.
- Cada documento queda inicialmente en estado **Pendiente**.
- Presionar "Procesar" para que el sistema extraiga el texto y lo envíe a la IA; el documento pasa a **Procesado** (con categoría, resumen y datos clave) o a **Error** si algo falla.
- Si un documento queda en Error, puede revisarse la causa y procesarse de nuevo una vez corregida (por ejemplo, si el archivo era un PDF escaneado sin texto real, se debe reemplazar por una versión con texto).

## 9.4 Buscar dentro de los documentos

- Ir a **Búsqueda**, escribir un término y, opcionalmente, filtrar por categoría o repositorio.
- Los resultados muestran el término resaltado y un fragmento del documento donde aparece.

## 9.5 Preguntar a la IA

- Ir a "Preguntar a la IA" y escribir una pregunta en lenguaje natural sobre los documentos ya procesados.
- La IA responde solo con base en los documentos que ya tienen estado Procesado, citando el nombre del documento entre paréntesis cuando corresponde.
- Si la respuesta no está en ningún documento procesado, la IA debe indicarlo explícitamente en vez de inventar una respuesta.
- El historial de las últimas 10 preguntas queda visible en la misma página.
