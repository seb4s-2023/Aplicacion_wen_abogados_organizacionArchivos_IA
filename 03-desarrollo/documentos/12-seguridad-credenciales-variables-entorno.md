# 12. Seguridad de credenciales y variables de entorno

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

- **Contraseñas de usuario:** nunca se almacenan en texto plano; se guardan como `password_hash` (bcrypt) y se validan con `password_verify()` en `login.php`.
- **Credenciales de base de datos y de la API de Gemini:** aisladas en `config/database.php` y `config/gemini.php`, separadas de la lógica de negocio, de modo que puedan excluirse del control de versiones.
- **Control de versiones:** `config/database.php` y `config/gemini.php` deben añadirse a `.gitignore` antes de subir el proyecto a un repositorio público, y sustituirse por archivos de ejemplo (`config/database.example.php`, `config/gemini.example.php`) sin valores reales, siguiendo la condición académica de no publicar API keys ni secretos.
- **Sesión:** el control de acceso se basa en sesiones nativas de PHP (`$_SESSION`), iniciadas de forma segura en `auth.php` (`session_start()` solo si no hay sesión activa) y destruidas completamente en `logout.php` (`$_SESSION = []; session_destroy();`).

**Nota de estado:** al momento de redactar este documento, el proyecto aún no se ha subido a un repositorio Git; este paso de seguridad (`.gitignore` + archivos de ejemplo) debe aplicarse en el momento de crear el repositorio, antes del primer commit, para no exponer nunca las credenciales reales en el historial.
