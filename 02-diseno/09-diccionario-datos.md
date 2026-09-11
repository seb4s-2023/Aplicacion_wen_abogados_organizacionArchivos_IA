# 9. Diccionario de datos

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 9.1 Tabla `usuarios`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_usuario | INT | PK, AUTO_INCREMENT | Identificador único del usuario |
| nombre | VARCHAR(100) | NOT NULL | Nombre completo del usuario |
| email | VARCHAR(150) | NOT NULL, UNIQUE | Correo electrónico, usado para iniciar sesión |
| password_hash | VARCHAR(255) | NOT NULL | Hash bcrypt de la contraseña (`password_hash` de PHP) |
| rol | ENUM('admin','abogado') | NOT NULL, DEFAULT 'abogado' | Rol funcional del usuario en el sistema |
| activo | TINYINT(1) | NOT NULL, DEFAULT 1 | Habilita o deshabilita el acceso del usuario |
| fecha_creacion | DATETIME | NOT NULL, DEFAULT NOW() | Fecha de creación del registro |

## 9.2 Tabla `repositorios`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_repositorio | INT | PK, AUTO_INCREMENT | Identificador único del repositorio |
| nombre | VARCHAR(150) | NOT NULL | Nombre del repositorio/caso |
| descripcion | VARCHAR(255) | NULL | Descripción breve y opcional |
| id_usuario_creador | INT | FK -> usuarios.id_usuario, ON DELETE CASCADE | Usuario que creó el repositorio |
| fecha_creacion | DATETIME | NOT NULL, DEFAULT NOW() | Fecha de creación del repositorio |

## 9.3 Tabla `documentos`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_documento | INT | PK, AUTO_INCREMENT | Identificador único del documento |
| id_repositorio | INT | FK -> repositorios, ON DELETE CASCADE | Repositorio al que pertenece |
| id_usuario | INT | FK -> usuarios, ON DELETE CASCADE | Usuario que cargó el documento |
| nombre_original | VARCHAR(255) | NOT NULL | Nombre del archivo tal como lo subió el usuario |
| nombre_archivo | VARCHAR(255) | NOT NULL | Nombre físico único con el que se guarda en `/uploads` |
| ruta_archivo | VARCHAR(500) | NOT NULL | Ruta física del archivo en el servidor |
| formato | ENUM('pdf','docx','txt') | NOT NULL | Formato del archivo cargado |
| categoria | ENUM('contrato','acta','concepto_juridico','sin_clasificar') | NOT NULL, DEFAULT 'sin_clasificar' | Categoría asignada por la IA |
| estado_procesamiento | ENUM('pendiente','procesado','error') | NOT NULL, DEFAULT 'pendiente' | Estado del ciclo de procesamiento IA |
| fecha_carga | DATETIME | NOT NULL, DEFAULT NOW() | Fecha en que se cargó el documento |

## 9.4 Tabla `analisis_documento`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_analisis | INT | PK, AUTO_INCREMENT | Identificador único del análisis |
| id_documento | INT | FK -> documentos, ON DELETE CASCADE | Documento analizado |
| resumen | TEXT | NULL | Resumen generado por la IA (máx. 5 líneas) |
| texto_extraido | LONGTEXT | NULL | Texto plano extraído del archivo original |
| datos_clave | JSON | NULL | Partes, fechas, montos y vigencia/obligaciones extraídos |
| fecha_analisis | DATETIME | NOT NULL, DEFAULT NOW() | Fecha del último análisis (se actualiza al reprocesar) |

## 9.5 Tabla `consultas_ia`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_consulta | INT | PK, AUTO_INCREMENT | Identificador único de la consulta |
| id_usuario | INT | FK -> usuarios, ON DELETE CASCADE | Usuario que realizó la consulta |
| pregunta | TEXT | NOT NULL | Pregunta o término de búsqueda (prefijo `[Búsqueda]` si aplica) |
| respuesta | TEXT | NULL | Respuesta generada por la IA o resultado de la búsqueda |
| fecha | DATETIME | NOT NULL, DEFAULT NOW() | Fecha de la consulta |

## 9.6 Tabla `logs_procesamiento`

| Campo | Tipo | Restricciones | Descripción |
|---|---|---|---|
| id_log | INT | PK, AUTO_INCREMENT | Identificador único del log |
| id_documento | INT | FK -> documentos, ON DELETE CASCADE | Documento donde ocurrió el error |
| tipo_error | VARCHAR(100) | NOT NULL | `extraccion` \| `clasificacion_ia` \| `almacenamiento` |
| mensaje | TEXT | NOT NULL | Detalle del error ocurrido |
| fecha | DATETIME | NOT NULL, DEFAULT NOW() | Fecha en que se registró el error |
