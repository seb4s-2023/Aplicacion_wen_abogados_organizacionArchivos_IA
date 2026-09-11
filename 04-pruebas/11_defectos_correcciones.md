# 11. Registro de defectos y correcciones

Durante la revisión y ejecución de las pruebas, especialmente las de seguridad básica, se identificaron los siguientes hallazgos. Ninguno impide el funcionamiento del sistema, pero se recomienda corregirlos antes de un uso en producción.

## DEF-01 — Severidad: Alta

| Campo | Detalle |
|---|---|
| **Módulo afectado** | `config/gemini.php` |
| **Descripción** | La clave de la API de Gemini está escrita en texto plano directamente en el código fuente (`define('GEMINI_API_KEY', ...)`). |
| **Riesgo** | Si el repositorio se publica o se comparte, la clave queda expuesta y puede ser usada por terceros, generando consumo indebido o bloqueo del servicio. |
| **Corrección recomendada** | Mover la clave a una variable de entorno (`.env`) no versionada, agregar el archivo de configuración al `.gitignore` y regenerar la clave actual. |
| **Estado** | 🔴 Pendiente por corregir |

## DEF-02 — Severidad: Media

| Campo | Detalle |
|---|---|
| **Módulo afectado** | `documentos.php` (subida de archivos) |
| **Descripción** | La validación del tipo de archivo se basa únicamente en la extensión del nombre (`pathinfo`), no en el contenido real del archivo. |
| **Riesgo** | Un archivo con contenido distinto al declarado podría renombrarse con extensión `.pdf`, `.docx` o `.txt` y superar la validación. |
| **Corrección recomendada** | Complementar la validación de extensión con una verificación del tipo MIME real (por ejemplo, con `finfo_file()`). |
| **Estado** | 🟠 Pendiente por corregir |

## DEF-03 — Severidad: Media

| Campo | Detalle |
|---|---|
| **Módulo afectado** | `repositorios.php` / `documentos.php` (formularios POST) |
| **Descripción** | Los formularios de creación y eliminación no incluyen un token anti-CSRF. |
| **Riesgo** | Un sitio malicioso podría inducir a un usuario autenticado a enviar, sin saberlo, una petición de eliminación o creación de registros. |
| **Corrección recomendada** | Generar un token CSRF por sesión, incluirlo como campo oculto en cada formulario y validarlo en el servidor antes de procesar la acción. |
| **Estado** | 🟠 Pendiente por corregir |

## DEF-04 — Severidad: Baja

| Campo | Detalle |
|---|---|
| **Módulo afectado** | `login.php` |
| **Descripción** | No existe un límite de intentos fallidos de inicio de sesión por usuario o por IP. |
| **Riesgo** | Facilita ataques de fuerza bruta contra las cuentas registradas. |
| **Corrección recomendada** | Registrar intentos fallidos y bloquear temporalmente la cuenta o exigir una espera progresiva tras varios intentos consecutivos. |
| **Estado** | 🟡 Pendiente por corregir |

## Resumen de hallazgos

| ID | Severidad | Módulo | Estado |
|---|---|---|---|
| DEF-01 | Alta | `config/gemini.php` | Pendiente por corregir |
| DEF-02 | Media | `documentos.php` | Pendiente por corregir |
| DEF-03 | Media | `repositorios.php` / `documentos.php` | Pendiente por corregir |
| DEF-04 | Baja | `login.php` | Pendiente por corregir |
