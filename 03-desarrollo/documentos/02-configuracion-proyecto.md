# 2. Configuración del proyecto

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

La configuración del proyecto se organiza en dos archivos dentro de la carpeta `config/`, separados del resto de la lógica para aislar credenciales y parámetros de conexión:

- **`config/database.php`** — crea y expone la conexión PDO (`$pdo`) hacia la base de datos `bufete_ia`, con `PDO::ATTR_ERRMODE` en modo excepción y prepares nativos (`PDO::ATTR_EMULATE_PREPARES => false`), requerido por `consulta_ia.php` para poder repetir nombres de parámetro por palabra de búsqueda.
- **`config/gemini.php`** — define `GEMINI_API_KEY` y `GEMINI_API_URL`, usados por `includes/ia.php` para autenticar las llamadas al modelo de IA.

## Dependencias externas gestionadas con Composer

```bash
composer require smalot/pdfparser
```

Esta librería habilita la extracción de texto de archivos PDF (ver sección 7). El autoload de Composer se carga de forma defensiva en `extractor.php`: si `vendor/autoload.php` no existe, el sistema no se cae — simplemente el procesamiento de PDFs devuelve un error controlado y registrado en `logs_procesamiento`.

## Pasos de configuración inicial del proyecto

1. Clonar/copiar el proyecto dentro de `htdocs`.
2. Ejecutar `composer install` (o `composer require smalot/pdfparser`) en la raíz del proyecto.
3. Crear la base de datos ejecutando `bufete_ia.sql` desde phpMyAdmin o el cliente de MySQL.
4. Completar `config/database.php` con host, usuario, contraseña y nombre de la base de datos.
5. Completar `config/gemini.php` con una API key válida de Google Gemini.
6. Iniciar Apache y MySQL desde el panel de control de XAMPP.
