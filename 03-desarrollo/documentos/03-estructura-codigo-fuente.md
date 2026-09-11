# 3. Estructura del código fuente

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El proyecto sigue una organización por responsabilidad: páginas PHP con salida HTML en la raíz, lógica reutilizable en `includes/`, configuración sensible en `config/` y el esquema de base de datos como script SQL independiente.

```
bufete-ia/
├── config/
│   ├── database.php        Conexión PDO a MySQL
│   └── gemini.php          Credenciales y URL de la API de Gemini
├── includes/
│   ├── auth.php            Sesión, requireLogin(), requireRole(), esAdmin()
│   ├── extractor.php       Extracción de texto (TXT / DOCX / PDF)
│   ├── ia.php               Integración con Gemini: análisis y RAG
│   └── procesador.php      Orquestador: extracción → IA → almacenamiento
├── vendor/                 Dependencias de Composer (smalot/pdfparser)
├── uploads/                Archivos físicos cargados por los usuarios
├── index.php                Dashboard
├── login.php / logout.php  Autenticación
├── repositorios.php        CRUD de repositorios/casos
├── documentos.php          Carga, listado, descarga y procesamiento de documentos
├── buscar.php               Búsqueda dentro del contenido documental
├── consulta_ia.php          Preguntas en lenguaje natural (RAG)
└── bufete_ia.sql            Script de creación de la base de datos
```

## Convenciones seguidas en el código

- Nombres de variables, funciones y comentarios en español, consistente con el dominio del negocio (bufete de abogados).
- Cada página protegida por sesión llama a `requireLogin()` como primera instrucción tras los `require_once`.
- Las funciones de `includes/` devuelven arreglos con la forma `['exito' => bool, ...]`, patrón uniforme para propagar éxito/error sin excepciones no controladas hacia las vistas.
- Toda salida hacia el HTML pasa por `htmlspecialchars()` para prevenir XSS.
