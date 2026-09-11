# 5. Diagrama de componentes

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

![Diagrama de componentes](./media/diagrama-componentes.png)

*Figura 3. Diagrama de componentes: vistas, núcleo funcional y configuración.*

El sistema se organiza en tres grandes bloques de componentes:

- **Interfaz (vistas PHP):** `consulta_ia.php`, `index.php` (dashboard), `buscar.php`, `documentos.php`, `login.php` y `repositorios.php`, que consumen el núcleo funcional.
- **Núcleo funcional (`includes/`):** `auth.php` (sesión y roles), `procesador.php` (orquestador), `extractor.php` (extracción de texto) e `ia.php` (integración con Gemini API).
- **Configuración (`config/`):** `gemini.php` y `database.php`, que centralizan la conexión con la API externa y con MySQL (`bufete_ia`) respectivamente.
