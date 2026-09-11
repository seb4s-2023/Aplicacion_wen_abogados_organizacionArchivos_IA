# 13. Conclusiones de las pruebas

- Se documentaron y ejecutaron **21 casos de prueba**, cubriendo las siete categorías exigidas para el proyecto, con una tasa de aprobación del **100%**.

- Los módulos de **autenticación, control de acceso por rol, gestión de repositorios y validación de archivos** cumplen con los criterios de aceptación definidos.

- El flujo de **procesamiento con IA** (extracción, clasificación, resumen) funciona correctamente en el caso exitoso y maneja de forma controlada los errores de la API externa, sin generar errores fatales visibles al usuario.

- Las pruebas de **seguridad básica** confirman que el uso de sentencias preparadas (PDO) y de `htmlspecialchars()` protege al sistema frente a inyección SQL y XSS en los puntos evaluados.

- Se identificaron **4 hallazgos de mejora** (sección 11 — Registro de defectos), siendo el más relevante la exposición de la clave de la API de Gemini en el código fuente, que debe corregirse antes de publicar el repositorio o desplegar el sistema en un entorno distinto al local.

- Se recomienda, como trabajo futuro, incorporar **pruebas automatizadas** (por ejemplo con PHPUnit) para los módulos críticos y una **validación de tipo MIME real** en la carga de archivos.
