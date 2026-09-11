# 11. Diseño de interfaces y prototipos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Las interfaces siguen un lenguaje visual consistente en todas las páginas:

- Barra de navegación superior oscura (`navbar-dark bg-dark`) con el nombre del bufete y accesos directos a Dashboard, Repositorios, Documentos, Búsqueda y Consulta IA.
- Fondo general claro (`#f4f6f9`).
- Tarjetas (*cards*) con sombra suave para agrupar contenido.
- Badges de color para representar estado (pendiente = amarillo, procesado = verde, error = rojo) y categoría del documento.
- Iconografía de Bootstrap Icons para reforzar visualmente cada acción.

## 11.1 Inventario de pantallas

- **`login.php`** — Formulario de acceso con correo y contraseña, mensajes de error.
- **`index.php`** — Dashboard con indicadores (documentos por estado, por categoría, totales).
- **`repositorios.php`** — Listado en tarjetas de repositorios + modal de creación + confirmación de eliminación.
- **`documentos.php`** — Listado de documentos de un repositorio, carga de archivo, botón "Procesar", descarga y eliminación.
- **`buscar.php`** — Formulario de búsqueda con filtros de categoría/repositorio + resultados con fragmentos resaltados.
- **`consulta_ia.php`** — Campo de pregunta en lenguaje natural, respuesta de la IA, documentos consultados e historial.

**Nota sobre prototipos:** dado que la interfaz ya está implementada como HTML+Bootstrap funcional (no se partió de mockups estáticos previos, sino de un desarrollo directo iterativo), este documento describe las pantallas ya construidas como el prototipo validado del sistema; las capturas de pantalla del sistema en funcionamiento se incluyen en el documento de Implementación y Despliegue (05) como evidencia.
