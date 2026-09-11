# 12. Matriz de trazabilidad requisito–prueba

La siguiente matriz relaciona los requisitos funcionales identificados para el sistema con los casos de prueba que los validan, permitiendo verificar la cobertura de las pruebas frente a los requisitos del proyecto.

| Requisito | Descripción del requisito | Casos de prueba asociados |
|---|---|---|
| **RF-01** | El sistema debe permitir la autenticación de usuarios por rol (administrador / abogado). | CP-01, CP-02, CP-03, CP-15 |
| **RF-02** | El sistema debe permitir crear y eliminar repositorios (casos/carpetas documentales). | CP-04, CP-16, CP-18 |
| **RF-03** | El sistema debe permitir subir documentos en formatos PDF, DOCX o TXT, hasta 20 MB. | CP-05, CP-06, CP-07, CP-19 |
| **RF-04** | El sistema debe procesar documentos con IA para extraer texto, clasificar y resumir. | CP-08, CP-09 |
| **RF-05** | El sistema debe extraer y presentar datos clave (partes, fechas, montos, vigencia). | CP-10, CP-11 |
| **RF-06** | El sistema debe permitir la búsqueda de documentos por contenido, categoría y repositorio. | CP-12, CP-13, CP-20 |
| **RF-07** | El sistema debe permitir consultas en lenguaje natural sobre los documentos procesados. | CP-14 |
| **RF-08** | El sistema debe restringir el acceso según sesión activa y rol del usuario. | CP-15, CP-16 |
| **RF-09** | El sistema debe prevenir vulnerabilidades básicas de inyección SQL y XSS. | CP-17, CP-18 |
| **RF-10** | El sistema debe manejar de forma controlada errores y casos límite en la operación de archivos. | CP-19, CP-21 |

## Cobertura

- **10 requisitos funcionales** identificados, todos con al menos un caso de prueba asociado (cobertura del 100%).
- **21 casos de prueba** distribuidos entre los requisitos, con algunos casos (CP-15, CP-18, CP-19) validando más de un requisito simultáneamente.
