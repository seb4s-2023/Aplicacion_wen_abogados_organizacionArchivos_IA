# 3. Objetivo general y objetivos específicos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 3.1 Objetivo general

Diseñar, desarrollar, probar, documentar e implementar una aplicación web que permita al Bufete Restrepo & Asociados gestionar repositorios de documentos jurídicos y aplicar Inteligencia Artificial para clasificarlos, resumirlos, extraer información relevante y responder preguntas en lenguaje natural sobre su contenido, convirtiendo información documental no estructurada en información útil para la toma de decisiones.

## 3.2 Objetivos específicos

- Implementar un módulo de autenticación y control de acceso basado en roles (administrador y abogado) que proteja el acceso a la información del bufete.
- Desarrollar la gestión de repositorios (casos/carpetas) que permita crear, listar y eliminar repositorios, asociados a su usuario creador.
- Implementar la carga, consulta, descarga y eliminación de documentos en formato PDF, DOCX y TXT dentro de cada repositorio.
- Construir un pipeline de procesamiento documental que extraiga el texto plano de cada formato soportado, de forma desacoplada del motor de IA.
- Integrar un servicio de Inteligencia Artificial (Google Gemini) que clasifique cada documento en al menos tres categorías, genere un resumen y extraiga datos clave (partes, fechas, montos, vigencia u obligaciones).
- Implementar un motor de búsqueda que permita ubicar documentos por nombre, resumen y contenido extraído.
- Implementar un módulo de consulta en lenguaje natural (enfoque RAG) que permita a los usuarios hacer preguntas sobre los documentos ya procesados y recibir respuestas fundamentadas únicamente en ese contenido.
- Registrar los errores y estados de procesamiento de cada documento para garantizar trazabilidad y soporte.
- Documentar el ciclo completo de ingeniería de software del proyecto: análisis, diseño, desarrollo, pruebas e implementación.
