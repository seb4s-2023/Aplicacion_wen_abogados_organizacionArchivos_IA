# 5. Identificación de actores y usuarios

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El sistema reconoce dos roles funcionales, controlados desde la tabla `usuarios` (campo `rol`) y validados en cada módulo mediante las funciones `requireLogin()`, `requireRole()` y `esAdmin()` del componente de autenticación:

| Actor | Descripción | Interacción principal con el sistema |
|---|---|---|
| **Administrador (admin)** | Usuario con control total del sistema; supervisa el uso del bufete y puede intervenir sobre cualquier repositorio. | Gestiona repositorios propios y ajenos (puede eliminar cualquiera), gestiona documentos, ejecuta procesamiento IA, búsquedas, consultas y visualiza el dashboard. |
| **Abogado (abogado)** | Usuario operativo que gestiona sus propios casos/repositorios y documentos. | Crea y administra sus repositorios y documentos, procesa documentos con IA, realiza búsquedas y consultas en lenguaje natural, y consulta el dashboard. |
| **Servicio externo: Google Gemini API** | Actor no humano (sistema externo) que recibe el texto extraído y el prompt de análisis, y retorna clasificación, resumen y datos clave, o respuestas a preguntas en lenguaje natural. | Se invoca desde `ia.php` mediante peticiones HTTP (cURL) a la API de `generateContent`. |

**Nota:** ambos roles humanos requieren sesión activa (`session_start()` y `$_SESSION['id_usuario']`); la diferencia entre admin y abogado se aplica principalmente sobre permisos de eliminación de repositorios ajenos, validados en `repositorios.php` mediante `esAdmin()` y comparación con `id_usuario_creador`.
