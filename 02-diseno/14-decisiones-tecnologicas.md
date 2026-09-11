# 14. Decisiones tecnológicas y su justificación

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

| Decisión | Justificación |
|---|---|
| PHP nativo (sin framework tipo Laravel/Symfony) | Curva de aprendizaje más corta para el equipo dentro del tiempo del semestre; control total y explícito del flujo de cada módulo, útil para poder explicar el código completo en la sustentación. |
| MySQL + PDO | Motor relacional estándar en entornos XAMPP/LAMP académicos; PDO con sentencias preparadas ofrece protección nativa contra inyección SQL sin librerías adicionales. |
| Bootstrap 5 (CDN) | Permite construir una interfaz responsiva y consistente rápidamente, sin necesidad de escribir CSS extenso ni de un pipeline de build de frontend. |
| Google Gemini API | Capa gratuita amplia, buen desempeño en español para clasificación/resumen de texto largo, y una integración sencilla vía HTTP/JSON sin SDKs complejos. |
| smalot/pdfparser (Composer) | Librería pura en PHP para extraer texto de PDF sin depender de binarios externos (como `pdftotext`), simplificando la instalación en XAMPP. |
| Búsqueda por palabras clave (LIKE) en vez de embeddings/vectorial | Suficiente para el volumen de documentos de prueba exigido (mínimo 30) y evita la complejidad operativa de una base de datos vectorial, cumpliendo igualmente el requisito de justificar la técnica adoptada. |
| Separación en `config/` e `includes/` | Aísla configuración sensible (credenciales, API key) y lógica reutilizable de las vistas, facilitando mantenimiento y la futura externalización de credenciales a variables de entorno. |
