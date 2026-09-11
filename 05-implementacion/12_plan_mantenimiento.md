# 12. Plan básico de mantenimiento

- **Mantenimiento correctivo:** revisar `logs_procesamiento` periódicamente para detectar patrones de error recurrentes (por ejemplo, un tipo de documento que siempre falla en la extracción) y corregir la causa raíz.

- **Mantenimiento de credenciales:** renovar la API key de Gemini antes de que expire o alcance su límite de uso, y actualizar `config/gemini.php` sin interrumpir el servicio en horarios de alto uso.

- **Mantenimiento de dependencias:** mantener actualizada la librería `smalot/pdfparser` vía Composer, revisando el changelog antes de actualizar en producción.

- **Limpieza de datos:** definir una política de retención para `consultas_ia` (historial de preguntas y búsquedas), que puede crecer indefinidamente con el uso.

- **Mejoras futuras identificadas durante el desarrollo:** pantalla de administración de usuarios (actualmente manual por base de datos), variables de entorno en vez de constantes en `config/`, y ampliar el repositorio de documentos de prueba a las 30 unidades mínimas exigidas por el proyecto.
