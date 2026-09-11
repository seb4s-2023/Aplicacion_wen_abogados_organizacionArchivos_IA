-- =====================================================
-- Bufete Restrepo & Asociados - Sistema Inteligente de
-- Gestión y Análisis Documental
-- =====================================================

CREATE DATABASE IF NOT EXISTS bufete_ia
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bufete_ia;

-- ---------------------------------------------------
-- Tabla: usuarios (Administradores y Abogados)
-- ---------------------------------------------------
CREATE TABLE usuarios (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('admin', 'abogado') NOT NULL DEFAULT 'abogado',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------
-- Tabla: repositorios (carpetas/casos)
-- ---------------------------------------------------
CREATE TABLE repositorios (
    id_repositorio      INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(150) NOT NULL,
    descripcion         VARCHAR(255) NULL,
    id_usuario_creador  INT NOT NULL,
    fecha_creacion      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_creador) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- ---------------------------------------------------
-- Tabla: documentos
-- ---------------------------------------------------
CREATE TABLE documentos (
    id_documento         INT AUTO_INCREMENT PRIMARY KEY,
    id_repositorio       INT NOT NULL,
    id_usuario           INT NOT NULL,
    nombre_original      VARCHAR(255) NOT NULL,
    nombre_archivo        VARCHAR(255) NOT NULL,   -- nombre físico guardado en /uploads
    ruta_archivo          VARCHAR(500) NOT NULL,
    formato               ENUM('pdf', 'docx', 'txt') NOT NULL,
    categoria              ENUM('contrato', 'acta', 'concepto_juridico', 'sin_clasificar')
                            NOT NULL DEFAULT 'sin_clasificar',
    estado_procesamiento   ENUM('pendiente', 'procesado', 'error') NOT NULL DEFAULT 'pendiente',
    fecha_carga             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_repositorio) REFERENCES repositorios(id_repositorio)
        ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- ---------------------------------------------------
-- Tabla: analisis_documento (resultado del procesamiento IA)
-- ---------------------------------------------------
CREATE TABLE analisis_documento (
    id_analisis       INT AUTO_INCREMENT PRIMARY KEY,
    id_documento       INT NOT NULL,
    resumen             TEXT NULL,
    texto_extraido      LONGTEXT NULL,      -- texto plano extraído del archivo
    datos_clave         JSON NULL,           -- partes, fechas, montos, vigencia, etc.
    fecha_analisis        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON DELETE CASCADE
);

-- ---------------------------------------------------
-- Tabla: consultas_ia (historial de preguntas en lenguaje natural)
-- ---------------------------------------------------
CREATE TABLE consultas_ia (
    id_consulta      INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario        INT NOT NULL,
    pregunta            TEXT NOT NULL,
    respuesta           TEXT NULL,
    fecha                 DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
);

-- ---------------------------------------------------
-- Tabla: logs_procesamiento (registro de errores)
-- ---------------------------------------------------
CREATE TABLE logs_procesamiento (
    id_log         INT AUTO_INCREMENT PRIMARY KEY,
    id_documento     INT NOT NULL,
    tipo_error         VARCHAR(100) NOT NULL,   -- ej: 'extraccion', 'clasificacion_ia', 'formato_no_soportado'
    mensaje            TEXT NOT NULL,
    fecha                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_documento) REFERENCES documentos(id_documento)
        ON DELETE CASCADE
);

-- =====================================================
-- Datos iniciales
-- =====================================================

-- Usuario administrador de prueba
-- Contraseña: Admin123!  (hash bcrypt real, verificable con password_verify() en PHP)
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Administrador General', 'admin@bufete.com',
 '$2b$12$.YboMRuEAp1ldYvQobZri..pReA5SdI8MbxNBpL6LoizO6nBsqCMa', 'admin');

-- Un abogado de prueba
-- Contraseña: Abogado123!
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Carlos Restrepo', 'carlos.restrepo@bufete.com',
 '$2b$12$Ms8/g.R2BYvQkMYk2ZTiUOgMPqdjjEhmc2aQ9/onAQIyDL/tss60W', 'abogado');

-- Un repositorio inicial
INSERT INTO repositorios (nombre, descripcion, id_usuario_creador) VALUES
('Casos activos 2026', 'Repositorio general de casos en curso', 2);
