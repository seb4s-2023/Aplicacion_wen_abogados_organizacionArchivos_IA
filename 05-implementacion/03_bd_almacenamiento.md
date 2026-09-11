# 3. Configuración de base de datos y almacenamiento

## 3.1 Base de datos

- Crear la base de datos ejecutando el script `bufete_ia.sql` (incluye las 6 tablas del esquema y dos usuarios de prueba: un admin y un abogado).
- Configurar la conexión en `config/database.php`: host, nombre de la base de datos (`bufete_ia`), usuario y contraseña de MySQL.
- El motor usa charset **utf8mb4**, necesario para soportar correctamente tildes, eñes y otros caracteres del español en nombres, resúmenes y texto extraído.

## 3.2 Almacenamiento de archivos

- Los archivos originales cargados por los usuarios se guardan en la carpeta `uploads/` en el sistema de archivos del servidor; la base de datos solo guarda la ruta y el nombre físico.
- La carpeta `uploads/` debe existir y tener permisos de escritura para el usuario con el que corre Apache/PHP (en XAMPP local normalmente no hay restricción; en un hosting Linux puede requerir `chmod 755` o `775` según la configuración del proveedor).
- El texto extraído y los resultados de IA (resumen, categoría, datos_clave) no se guardan en archivos aparte: quedan en la tabla `analisis_documento`, evitando duplicar información entre el sistema de archivos y la base de datos.
