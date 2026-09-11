# 7. Diagramas de secuencia de procesos principales

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

Se documentan los tres procesos de mayor relevancia funcional y técnica del sistema: autenticación, procesamiento de un documento con IA y consulta en lenguaje natural sobre los documentos ya procesados (RAG básico).

## 7.1 Iniciar sesión (CU-01)

![Diagrama de secuencia — Iniciar sesión](./media/secuencia-iniciar-sesion.png)

El usuario envía correo y contraseña por POST a `login.php`, que consulta el usuario activo en MySQL, valida la contraseña con `password_verify()`, crea la sesión (`session_start()` + `$_SESSION`) y redirige a `index.php`.

## 7.2 Procesar documento con IA (CU-02)

![Diagrama de secuencia — Procesar documento con IA](./media/secuencia-procesar-documento-ia.png)

Desde `documentos.php`, el usuario dispara `procesarDocumento(id)`. `procesador.php` obtiene la ruta y formato del documento en MySQL, invoca `extraerTexto()` en `extractor.php`, envía el texto a `analizarDocumentoConIA()` en `ia.php`, que llama a la API de Gemini (`generateContent`) y recibe un JSON con categoría, resumen y datos clave. Finalmente se guarda el análisis y se actualiza el estado del documento.

## 7.3 Consultar en lenguaje natural — RAG (CU-03)

![Diagrama de secuencia — Consultar en lenguaje natural](./media/secuencia-consulta-lenguaje-natural.png)

El usuario envía una pregunta por POST a `consulta_ia.php`, que ejecuta `buscarDocumentosRelevantes()` en MySQL para obtener resumen y texto de los documentos relevantes. Ese contexto, junto con la pregunta, se envía a `responderPreguntaConIA()` en `ia.php`, que llama a Gemini y devuelve una respuesta en lenguaje natural. La interacción se registra en `consultas_ia` y se muestra al usuario junto con los documentos usados como fuente.
