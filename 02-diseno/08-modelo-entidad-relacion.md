# 8. Modelo entidad-relación

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El modelo de datos se compone de seis entidades. `usuarios` es la entidad central de la que dependen `repositorios` (1 usuario crea N repositorios), `documentos` (1 usuario carga N documentos, 1 repositorio contiene N documentos) y `consultas_ia` (1 usuario realiza N consultas). Cada documento tiene una relación 1 a 1 con `analisis_documento` (su resultado de procesamiento IA) y una relación 1 a N con `logs_procesamiento` (puede acumular varios errores en distintos intentos de procesamiento). Todas las relaciones dependientes usan `ON DELETE CASCADE`, de forma que eliminar un repositorio elimina sus documentos, y eliminar un documento elimina su análisis y sus logs.

![Modelo entidad-relación de la base de datos bufete_ia](./media/modelo-entidad-relacion.png)

*Figura 5. Modelo entidad-relación de la base de datos bufete_ia.*
