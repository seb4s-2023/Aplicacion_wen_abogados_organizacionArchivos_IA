# 4. Pruebas funcionales

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## CP-01 — Verificar el inicio de sesión exitoso con credenciales válidas del rol administrador

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Funcional |
| **Módulo / archivo** | `login.php` |
| **Precondiciones** | Usuario `admin@bufete.com` existe y está activo en la tabla `usuarios`. |
| **Datos de entrada** | `email = admin@bufete.com` / `password = Admin123!` |
| **Pasos de ejecución** | 1) Ingresar a `login.php`. 2) Diligenciar correo y contraseña. 3) Enviar el formulario. |
| **Resultado esperado** | El sistema valida el hash con `password_verify()`, crea la sesión (`$_SESSION`) y redirige a `index.php`. |
| **Resultado obtenido** | Coincide con lo esperado: `password_verify()` valida el hash bcrypt y redirige correctamente. |
| **Estado** | ✅ Aprobado |

## CP-02 — Verificar que el sistema rechace un inicio de sesión con contraseña incorrecta

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Funcional |
| **Módulo / archivo** | `login.php` |
| **Precondiciones** | Usuario existente y activo. |
| **Datos de entrada** | `email = carlos.restrepo@bufete.com` / `password = incorrecta123` |
| **Pasos de ejecución** | 1) Ingresar a `login.php`. 2) Diligenciar correo válido y contraseña errónea. 3) Enviar. |
| **Resultado esperado** | El sistema muestra el mensaje "Correo o contraseña incorrectos" y no crea sesión. |
| **Resultado obtenido** | Conforme: `password_verify()` retorna `false` y no se asignan variables de sesión. |
| **Estado** | ✅ Aprobado |

## CP-03 — Verificar que el cierre de sesión invalide el acceso a páginas protegidas

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Funcional |
| **Módulo / archivo** | `logout.php` |
| **Precondiciones** | Sesión activa (usuario autenticado). |
| **Datos de entrada** | N/A (acceso directo a `logout.php`) |
| **Pasos de ejecución** | 1) Con sesión activa, acceder a `logout.php`. 2) Intentar acceder nuevamente a `index.php` o `repositorios.php`. |
| **Resultado esperado** | Se destruye la sesión (`$_SESSION = []; session_destroy();`) y se redirige a `login.php`; el acceso posterior a páginas protegidas vuelve a exigir login. |
| **Resultado obtenido** | Conforme: `requireLogin()` detecta la ausencia de `$_SESSION['id_usuario']` y redirige a login. |
| **Estado** | ✅ Aprobado |

## CP-04 — Verificar la creación de un nuevo repositorio (caso/carpeta) por un usuario autenticado

| Campo | Detalle |
|---|---|
| **Tipo de prueba** | Funcional |
| **Módulo / archivo** | `repositorios.php` |
| **Precondiciones** | Sesión activa como abogado o admin. |
| **Datos de entrada** | `nombre = 'Casos activos 2026'`, `descripcion = 'Repositorio de prueba'` |
| **Pasos de ejecución** | 1) Abrir modal 'Nuevo repositorio'. 2) Diligenciar nombre y descripción. 3) Enviar formulario. |
| **Resultado esperado** | Se inserta el registro en la tabla `repositorios` y el usuario es redirigido con el mensaje "Repositorio creado correctamente". |
| **Resultado obtenido** | Conforme: patrón POST-REDIRECT-GET funciona; el repositorio aparece listado con contador de documentos en 0. |
| **Estado** | ✅ Aprobado |
