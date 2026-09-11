# 1. Arquitectura general de la solución

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El sistema sigue una arquitectura en capas de tipo monolítico, típica de aplicaciones web PHP tradicionales, ejecutada sobre un entorno LAMP/XAMPP (Linux/Windows + Apache + MySQL + PHP). Se optó deliberadamente por esta arquitectura, en vez de una arquitectura de microservicios o SPA+API separada, porque el alcance funcional mínimo del proyecto integrador no exige escalabilidad distribuida y una solución monolítica reduce la complejidad de despliegue para un entorno académico, permitiendo a todo el equipo entender y mantener el sistema completo.

La aplicación se organiza en cuatro capas lógicas:

1. **Presentación** — vistas PHP con Bootstrap 5.
2. **Aplicación / lógica de negocio** — páginas PHP + módulos en `includes/`.
3. **Datos y almacenamiento** — MySQL vía PDO + sistema de archivos para los documentos originales.
4. **Integración con IA** — componente de integración con un servicio externo de Inteligencia Artificial (Google Gemini API).

Estas capas se comunican de forma sincrónica: cada solicitud HTTP del navegador es atendida por un script PHP que orquesta la lógica necesaria y devuelve HTML renderizado del lado del servidor (no hay una API REST separada ni renderizado del lado del cliente vía JavaScript, salvo los componentes interactivos de Bootstrap).
