# 8. Requerimientos no funcionales

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

| ID | Categoría | Descripción |
|---|---|---|
| RNF-01 | Seguridad | Las contraseñas deben almacenarse como hash (bcrypt vía `password_hash`/`password_verify`), nunca en texto plano. |
| RNF-02 | Seguridad | Las credenciales de base de datos y la API key de Gemini deben gestionarse como configuración separada del código y no publicarse en el repositorio de control de versiones. |
| RNF-03 | Seguridad | Todo acceso a datos debe usar sentencias preparadas (PDO con parámetros nombrados o posicionales) para prevenir inyección SQL. |
| RNF-04 | Seguridad | Toda salida de datos generados por el usuario hacia HTML debe escaparse con `htmlspecialchars()` para prevenir XSS. |
| RNF-05 | Usabilidad | La interfaz debe ser responsiva (Bootstrap 5) y consistente en navegación entre módulos (barra superior con acceso a Dashboard, Repositorios, Documentos, Búsqueda y Consulta IA). |
| RNF-06 | Disponibilidad/Resiliencia | Un fallo en la extracción de texto o en la llamada a la API de IA no debe interrumpir la disponibilidad general del sistema; debe registrarse como error controlado y permitir reprocesar el documento. |
| RNF-07 | Rendimiento | El texto extraído por documento se limita a 100.000 caracteres y el contexto enviado por documento a la IA en consultas se limita a 6.000 caracteres, para evitar exceder límites de tokens y tiempos de espera excesivos. |
| RNF-08 | Rendimiento | Las llamadas HTTP a la API de Gemini deben tener un tiempo máximo de espera (timeout) definido, para no bloquear indefinidamente el procesamiento. |
| RNF-09 | Mantenibilidad | El código debe separar responsabilidades por capas: extracción de texto (`extractor.php`), integración con IA (`ia.php`), orquestación (`procesador.php`), acceso a datos (`database.php`) y control de acceso (`auth.php`). |
| RNF-10 | Portabilidad | El sistema debe poder ejecutarse sobre un entorno estándar tipo XAMPP (PHP + MySQL) sin dependencias de infraestructura adicionales distintas a Composer para la librería de PDF. |
| RNF-11 | Compatibilidad | El sistema debe soportar como mínimo los formatos de archivo PDF, DOCX y TXT, con detección automática de codificación de texto en archivos TXT. |
| RNF-12 | Trazabilidad | Todo documento debe tener en todo momento un estado de procesamiento identificable (pendiente, procesado, error) consultable desde la interfaz. |
