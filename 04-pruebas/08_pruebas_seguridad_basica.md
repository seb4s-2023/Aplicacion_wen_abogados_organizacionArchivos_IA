# 8. Pruebas de seguridad básicas

Este bloque valida el control de acceso por sesión y rol, y la resistencia del sistema a inyección SQL y XSS.

## CP-15 — Bloqueo de acceso a páginas protegidas sin sesión

| Campo | Detalle |
|---|---|
| **ID** | CP-15 |
| **Tipo de prueba** | Seguridad básica |
| **Módulo / archivo** | `includes/auth.php` (`requireLogin`) |
| **Objetivo** | Verificar que no se pueda acceder a páginas protegidas sin haber iniciado sesión. |
| **Precondiciones** | Sin sesión activa (navegador sin cookie de sesión o sesión cerrada). |
| **Datos de entrada** | Acceso directo por URL a `documentos.php`, `repositorios.php` o `consulta_ia.php`. |
| **Pasos de ejecución** | 1) Cerrar sesión o usar una ventana de incógnito. 2) Intentar acceder directamente a la URL de una página protegida. |
| **Resultado esperado** | El sistema redirige a `login.php` sin exponer ningún dato de la aplicación. |
| **Resultado obtenido** | Conforme: `requireLogin()` se ejecuta al inicio de cada script protegido y corta la ejecución antes de consultar datos. |
| **Estado** | ✅ Aprobado |

## CP-16 — Restricción de eliminación de repositorios ajenos por rol

| Campo | Detalle |
|---|---|
| **ID** | CP-16 |
| **Tipo de prueba** | Seguridad básica |
| **Módulo / archivo** | `repositorios.php` / `documentos.php` (control por rol) |
| **Objetivo** | Verificar que un usuario con rol "abogado" no pueda eliminar un repositorio creado por otro usuario. |
| **Precondiciones** | Repositorio creado por el usuario admin; sesión activa como abogado. |
| **Datos de entrada** | `POST accion=eliminar` con `id_repositorio` de un repositorio ajeno. |
| **Pasos de ejecución** | 1) Autenticarse como abogado. 2) Enviar directamente (o por Postman) la petición de eliminación de un repositorio que no le pertenece. |
| **Resultado esperado** | El sistema responde con `error=sin_permiso` y no elimina el registro, dado que `!$esAdmin && id_usuario_creador !== idUsuario`. |
| **Resultado obtenido** | Conforme: la validación de rol impide la eliminación y el registro permanece intacto en la base de datos. |
| **Estado** | ✅ Aprobado |

## CP-17 — Resistencia a inyección SQL básica

| Campo | Detalle |
|---|---|
| **ID** | CP-17 |
| **Tipo de prueba** | Seguridad básica |
| **Módulo / archivo** | `buscar.php` / `repositorios.php` (consultas SQL) |
| **Objetivo** | Verificar que el sistema sea resistente a inyección SQL básica en los campos de entrada. |
| **Precondiciones** | Ninguna especial. |
| **Datos de entrada** | `q = "' OR '1'='1"` en el campo de búsqueda; `nombre = "test'); DROP TABLE usuarios;--"` en creación de repositorio. |
| **Pasos de ejecución** | 1) Ingresar las cadenas de prueba en los campos indicados. 2) Enviar los formularios. |
| **Resultado esperado** | Las cadenas se tratan como texto literal (parámetros preparados con PDO) y no alteran la consulta ni afectan otras tablas. |
| **Resultado obtenido** | Conforme: todas las consultas usan sentencias preparadas (`$pdo->prepare()`) con parámetros enlazados; no se observó alteración de la base de datos. |
| **Estado** | ✅ Aprobado |

## CP-18 — Neutralización de XSS almacenado

| Campo | Detalle |
|---|---|
| **ID** | CP-18 |
| **Tipo de prueba** | Seguridad básica |
| **Módulo / archivo** | `repositorios.php` / `buscar.php` (salida HTML) |
| **Objetivo** | Verificar que el sistema neutralice intentos básicos de Cross-Site Scripting (XSS) almacenado. |
| **Precondiciones** | Sesión activa. |
| **Datos de entrada** | Nombre del repositorio = `"<script>alert('xss')</script>"` |
| **Pasos de ejecución** | 1) Crear un repositorio con el nombre indicado. 2) Visualizar el listado de repositorios. |
| **Resultado esperado** | El navegador muestra el texto literal de la etiqueta, sin ejecutar el script, gracias a `htmlspecialchars()` en la salida. |
| **Resultado obtenido** | Conforme: `htmlspecialchars()` escapa los caracteres `<` `>` y el script no se ejecuta. |
| **Estado** | ✅ Aprobado |

## Resumen del bloque

| Tipo de prueba | N.° de casos | Aprobados |
|---|---|---|
| Seguridad básica | 4 | 4 |

**Requisitos cubiertos:** RF-08 (restricción de acceso por sesión y rol) y RF-09 (prevención de inyección SQL y XSS).

**Hallazgos relacionados registrados en la sección 6 (Registro de defectos):** DEF-01 (clave de API en texto plano), DEF-03 (ausencia de token anti-CSRF), DEF-04 (sin límite de intentos fallidos de login).
