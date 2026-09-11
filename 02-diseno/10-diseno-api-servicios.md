# 10. Diseño de API / servicios

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El sistema no expone una API REST independiente; cada página PHP actúa como endpoint que recibe peticiones HTTP (GET/POST) del navegador y responde con HTML. La siguiente tabla documenta estos endpoints internos como si fueran servicios, incluyendo el servicio externo de IA.

| Método | Endpoint | Descripción | Parámetros principales |
|---|---|---|---|
| GET/POST | `login.php` | Formulario y procesamiento de inicio de sesión | `email`, `password` |
| GET | `logout.php` | Cierra la sesión activa | — |
| GET/POST | `repositorios.php` | Lista, crea y elimina repositorios | `accion`, `nombre`, `descripcion`, `id_repositorio` |
| GET/POST | `documentos.php` | Lista, carga, descarga y elimina documentos; dispara el procesamiento IA | `id_repositorio`, `archivo`, `id_documento` |
| GET | `buscar.php` | Búsqueda de documentos por contenido | `q`, `categoria`, `id_repositorio` |
| GET/POST | `consulta_ia.php` | Formulario y respuesta de preguntas en lenguaje natural | `pregunta` |
| GET | `index.php` | Dashboard con indicadores del repositorio | — |
| POST | Gemini API — `generateContent` | Servicio externo: análisis de documento o respuesta a pregunta en lenguaje natural | `contents` (prompt), `generationConfig` (temperature, maxOutputTokens) |

### Funciones internas (no HTTP) entre módulos

- `extraerTexto(ruta, formato)` → `array{exito, texto, error}` — `extractor.php`
- `analizarDocumentoConIA(texto, nombreArchivo)` → `array{exito, categoria, resumen, datos_clave, error}` — `ia.php`
- `responderPreguntaConIA(pregunta, documentosContexto)` → `array{exito, respuesta, error}` — `ia.php`
- `procesarDocumento(PDO $pdo, int $idDocumento)` → `array{exito, mensaje}` — `procesador.php`
- `requireLogin()`, `requireRole($rol)`, `esAdmin()` — `auth.php`
