# 2. Requisitos de hardware y software

## 2.1 Software

- PHP con la extensión **ZipArchive** habilitada (requerida para extraer texto de archivos DOCX).
- Servidor **Apache** (incluido en XAMPP) o **Nginx** equivalente en producción.
- **MySQL** o **MariaDB**.
- **Composer**, para instalar `smalot/pdfparser` (extracción de texto de PDF).
- Acceso a Internet saliente del servidor, necesario para que `includes/ia.php` pueda llamar a la API de Google Gemini.
- Navegador web moderno (Chrome, Edge, Firefox) para el uso de la interfaz.

## 2.2 Hardware

El sistema no tiene requisitos de hardware exigentes: al ser una aplicación PHP tradicional (sin procesos de IA corriendo localmente, ya que la inferencia la hace la API de Gemini en la nube), una máquina de gama modesta es suficiente para el entorno de desarrollo y pruebas:

- Procesador de doble núcleo o superior.
- 4 GB de RAM como mínimo para XAMPP + navegador.
- Espacio en disco acorde al volumen de documentos que se carguen en `uploads/` (los documentos de prueba del proyecto son pocos y livianos).
