# 4. Diagrama de casos de uso

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

![Diagrama de casos de uso general](./media/diagrama-casos-de-uso.png)

*Figura 2. Diagrama de casos de uso general (actores: Administrador, Abogado y Google Gemini API).*

Los actores **Abogado** y **Administrador** interactúan con los siguientes casos de uso: Cargar/consultar/descargar documento, Gestionar repositorios, Consultar en lenguaje natural, Procesar documento con IA, Ver dashboard, Buscar documentos e Iniciar/cerrar sesión. El Administrador, adicionalmente, puede Eliminar repositorio ajeno. Los casos de uso **Consultar en lenguaje natural** y **Procesar documento con IA** incluyen (`<<incluye>>`) la interacción con el actor externo **Google Gemini API**.
