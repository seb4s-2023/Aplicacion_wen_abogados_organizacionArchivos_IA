# 7. Implementación del procesamiento de documentos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

El procesamiento documental (`includes/extractor.php`) convierte cada archivo cargado en texto plano, paso previo indispensable para que la IA pueda analizarlo:

| Formato | Técnica de extracción |
|---|---|
| TXT | Lectura directa con `file_get_contents()`; detección de codificación (UTF-8 / Windows-1252 / ISO-8859-1) con `mb_detect_encoding()` y conversión a UTF-8 |
| DOCX | Un `.docx` es un ZIP; se abre con `ZipArchive`, se lee `word/document.xml`, se convierten las marcas `</w:p>` y `</w:tab>` en saltos de línea/tabulaciones, y se eliminan las etiquetas XML restantes con `strip_tags()` |
| PDF | Librería `smalot/pdfparser` (PHP puro, sin binarios externos); limitada a PDFs con texto real — un PDF escaneado como imagen requeriría OCR, fuera del alcance mínimo del proyecto |

Tras la extracción, `limpiarTexto()` normaliza espacios y saltos de línea repetidos y trunca el resultado a 100 000 caracteres como límite defensivo, evitando que un documento muy extenso sature la petición posterior a la API de IA.

Si el archivo no existe, el formato no es soportado o el texto resultante queda vacío (por ejemplo, un PDF escaneado), `extraerTexto()` devuelve `['exito' => false, ...]` con un mensaje de error específico, que `procesador.php` registra en `logs_procesamiento` y usa para marcar el documento como `estado_procesamiento = 'error'`.
