# 15. Diseño básico de seguridad

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

| Riesgo | Control de diseño aplicado |
|---|---|
| Contraseñas en texto plano | Almacenamiento como hash bcrypt (`password_hash`) y verificación con `password_verify()`; nunca se compara ni se muestra la contraseña en texto plano. |
| Inyección SQL | Todas las consultas usan PDO con sentencias preparadas (parámetros posicionales o nombrados) y `PDO::ATTR_EMULATE_PREPARES => false` para forzar preparación nativa. |
| XSS (Cross-Site Scripting) | Todo dato proveniente del usuario o de la base de datos que se imprime en HTML se procesa con `htmlspecialchars()` antes de mostrarse. |
| Acceso no autenticado | `requireLogin()` se invoca al inicio de cada página protegida; si no hay sesión activa, se redirige a `login.php`. |
| Escalamiento de privilegios | `requireRole()` y `esAdmin()` verifican el rol almacenado en la sesión antes de ejecutar acciones administrativas (p. ej. eliminar cualquier repositorio). |
| Manipulación de sesión | Uso del mecanismo nativo de sesiones de PHP (`session_start()`, `$_SESSION`); `logout.php` limpia el arreglo de sesión y destruye la sesión (`session_destroy()`). |
| Exposición de credenciales y API keys | Diseño previsto: externalizar `GEMINI_API_KEY` y las credenciales de `database.php` a variables de entorno fuera del control de versiones (pendiente de implementar como mejora — ver riesgo identificado en el documento de Análisis, sección 14). |
| Carga de archivos maliciosos | Restricción del formato de archivo aceptado a `pdf`, `docx` y `txt` (ENUM en base de datos); se recomienda como buena práctica adicional validar la extensión y el tipo MIME real del archivo en el momento de la carga. |
