# 5. Implementación de backend

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El backend está escrito en PHP procedural organizado por página, con la lógica de dominio extraída a funciones en `includes/` para poder reutilizarla entre páginas (por ejemplo, `extraerTexto()` y `analizarDocumentoConIA()` son usadas por `procesador.php`, que a su vez es invocado desde `documentos.php`).

- **Control de acceso:** `auth.php` centraliza `requireLogin()` (exige sesión activa) y `requireRole()` / `esAdmin()` (control por rol admin/abogado), usados en cada página que lo requiere.
- **Acceso a datos:** PDO con consultas preparadas en todas las operaciones (ver sección 6), sin concatenar valores de usuario directamente en SQL.
- **Patrón Post/Redirect/Get:** operaciones de creación/eliminación (ej. `repositorios.php`) redirigen con `header('Location: ...')` tras un POST exitoso, y los mensajes de resultado se leen desde parámetros GET (`?ok=creado`, `?error=sin_permiso`).
- **Separación de responsabilidades:** `procesador.php` actúa como orquestador que no imprime HTML — solo coordina extracción, IA y persistencia, devolviendo un resultado que la vista (`documentos.php`) interpreta.
