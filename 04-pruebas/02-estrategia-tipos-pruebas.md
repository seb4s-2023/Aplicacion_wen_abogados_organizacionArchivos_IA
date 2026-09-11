# 2. Estrategia y tipos de pruebas aplicadas

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

La estrategia adoptada es de **pruebas de caja negra**: se evaluó el comportamiento observable del sistema (mensajes, cambios de estado, redirecciones, datos almacenados) a partir de las entradas del usuario, sin instrumentar el código con pruebas unitarias automatizadas, dado el alcance académico del proyecto.

Se aplicaron los siguientes tipos de prueba:

- **Pruebas funcionales:** validan que cada función del sistema (login, logout, CRUD de repositorios) haga lo que se espera de ella.
- **Pruebas de validación de archivos:** validan las reglas de formato y tamaño máximo definidas en `documentos.php`.
- **Pruebas del procesamiento de IA:** validan la integración con la API de Gemini, tanto en el flujo exitoso como en el manejo de errores.
- **Pruebas de clasificación y extracción:** validan que la categoría asignada y los datos clave extraídos correspondan al contenido real del documento.
- **Pruebas de búsqueda y preguntas:** validan la búsqueda por palabra clave, los filtros combinados y las respuestas de la IA basadas únicamente en documentos procesados.
- **Pruebas de seguridad básicas:** validan el control de acceso por sesión y rol, y la resistencia a inyección SQL y XSS.
- **Pruebas de errores y casos límite:** validan que entradas vacías, inexistentes o inválidas no generen errores fatales ni comportamientos inesperados.

**Criterio de aceptación:** un caso de prueba se marca como "Aprobado" cuando el resultado obtenido coincide con el resultado esperado; se marca como "Fallido" cuando existe una diferencia, y en tal caso se documenta como defecto en la sección de Registro de defectos y correcciones.
