# 11. Gestión de errores y validaciones

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

- **Validación de entrada:** campos vacíos (nombre de repositorio, pregunta, email/password de login) se rechazan antes de tocar la base de datos, con mensajes de error específicos por caso.
- **Errores de extracción/IA:** `extraerTexto()` y `analizarDocumentoConIA()` devuelven siempre `['exito' => bool, 'error' => string|null]`; `procesador.php` centraliza qué hacer con ese resultado — registrar en `logs_procesamiento` (con `tipo_error`: `extraccion`, `clasificacion_ia` o `almacenamiento`) y marcar el documento como `estado_procesamiento = 'error'`.
- **Excepciones no controladas:** el guardado del análisis está envuelto en `try/catch` (`Throwable`) en `procesador.php`, de modo que un fallo inesperado de base de datos no rompe la página, sino que se registra igual como error de tipo `almacenamiento`.
- **Autorización:** `repositorios.php` verifica que solo el administrador o el usuario creador puedan eliminar un repositorio, devolviendo `error=sin_permiso` vía redirección si no se cumple.
- **Prevención de XSS:** toda variable proveniente de la base de datos o del usuario se imprime con `htmlspecialchars()` antes de insertarse en el HTML.
- **Prevención de inyección SQL:** 100% de las consultas usan sentencias preparadas de PDO; no hay concatenación directa de datos de usuario en SQL en ningún archivo del proyecto.
