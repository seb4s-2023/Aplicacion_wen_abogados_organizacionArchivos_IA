# 7. Proceso de despliegue

**Estado actual:** el sistema se despliega únicamente en el ambiente local de XAMPP, usado para la demostración en video y la sustentación. A continuación se documenta el proceso que se seguiría para llevarlo a un hosting o VPS real, como referencia de que el sistema es desplegable sin cambios de código:

1. Contratar un hosting o VPS con PHP (con ZipArchive habilitada) y MySQL/MariaDB.
2. Subir el código fuente al servidor (vía Git, FTP/SFTP o el panel del proveedor).
3. Ejecutar `composer install` en el servidor para instalar `smalot/pdfparser`.
4. Crear la base de datos remota e importar `bufete_ia.sql`.
5. Configurar `config/database.php` y `config/gemini.php` con las credenciales del entorno de producción (nunca las de desarrollo).
6. Configurar el dominio/subdominio y, si el proveedor lo permite, un certificado HTTPS (ej. Let's Encrypt).
7. Verificar permisos de escritura de la carpeta `uploads/` en el nuevo servidor.
8. Ejecutar manualmente los casos de prueba funcionales críticos (login, carga y procesamiento de un documento, búsqueda) para confirmar que el despliegue quedó operativo, siguiendo el mismo plan de pruebas del documento 04.
