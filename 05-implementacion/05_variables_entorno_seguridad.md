# 5. Variables de entorno y configuración segura

Actualmente las credenciales viven en `config/database.php` y `config/gemini.php` como constantes PHP, lo cual es válido para el entorno local de desarrollo del proyecto. Para un despliegue en producción se recomienda:

- Excluir `config/database.php` y `config/gemini.php` del control de versiones (`.gitignore`), y subir en su lugar archivos de ejemplo (`config/database.example.php`, `config/gemini.example.php`) sin valores reales.
- En el servidor de producción, cargar los valores reales directamente en esos archivos de configuración (o migrarlos a variables de entorno del sistema con `getenv()`, como mejora futura), nunca dejarlos con los valores de ejemplo del proyecto académico.
- Restringir el acceso HTTP directo a la carpeta `config/` a nivel de servidor web (por ejemplo, con una regla que deniegue el acceso a `*.php` fuera de las rutas públicas, o ubicando `config/` fuera del document root en producción).
- Usar **HTTPS** en producción para proteger las credenciales de login y las respuestas de la IA en tránsito (en el entorno local de XAMPP no aplica, al ser localhost).
- Rotar la API key de Gemini si llegara a exponerse por error (por ejemplo, si quedó registrada en un commit antes de aplicar el `.gitignore`).
