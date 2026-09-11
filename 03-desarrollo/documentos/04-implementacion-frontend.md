# 4. Implementación de frontend

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El frontend es HTML generado por PHP (server-side rendering), estilizado con Bootstrap 5 y Bootstrap Icons cargados por CDN, sin build step ni framework de JavaScript — decisión coherente con el alcance funcional mínimo del proyecto y con el tiempo disponible del semestre.

- **Layout común:** barra de navegación superior (`bg-dark`) con el nombre e inicial del bufete, el usuario autenticado y su rol, y botón de salir; barra secundaria de accesos rápidos a Dashboard, Repositorios, Documentos, Búsqueda y Preguntar a la IA.
- **Componentes Bootstrap usados:** cards, badges de estado (Pendiente/Procesado/Error), modales para formularios de creación (ej. modal "Nuevo repositorio" en `repositorios.php`), grupos de inputs y formularios GET/POST estándar.
- **Resaltado de coincidencias de búsqueda:** `buscar.php` aplica la función `resaltar()` para envolver el término buscado en `<mark>` sobre el texto ya escapado con `htmlspecialchars()`, evitando inyección de HTML mientras se conserva el resaltado.
- **Feedback al usuario:** alertas contextuales (`alert-success` / `alert-danger`) tras operaciones CRUD, usando el patrón Post/Redirect/Get (ver `repositorios.php`) para evitar reenvíos de formulario al recargar.
- **Interfaz de preguntas a la IA (`consulta_ia.php`):** formulario simple de pregunta, tarjeta de respuesta con los documentos consultados como badges, e historial de las últimas 10 preguntas del usuario.
