# 1. Plan de pruebas

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

## 1.1 Objetivo general

Verificar que el sistema Bufete Restrepo & Asociados cumpla con los requisitos funcionales y de seguridad básica definidos para el proyecto, mediante la ejecución de casos de prueba diseñados sobre sus módulos principales.

## 1.2 Alcance

Se incluyen en el alcance:

- Autenticación y control de acceso por roles (administrador / abogado).
- Gestión de repositorios (crear, listar, eliminar).
- Carga, validación, descarga y eliminación de documentos (PDF, DOCX, TXT).
- Procesamiento de documentos con IA (extracción, clasificación, resumen) mediante la API de Gemini.
- Búsqueda documental y consultas en lenguaje natural sobre documentos procesados.
- Pruebas de seguridad básica: control de acceso, inyección SQL y XSS.
- Manejo de errores y casos límite en la carga y descarga de archivos.

Quedan fuera del alcance de este documento las pruebas de carga/rendimiento con alto volumen de usuarios concurrentes y las pruebas de penetración avanzadas, por no ser exigidas en el contexto académico del proyecto.

## 1.3 Recursos y herramientas

- Entorno local XAMPP (Apache, PHP 8, MySQL/MariaDB).
- Navegador web (Chrome/Edge) para pruebas manuales de interfaz.
- Cliente HTTP (Postman o similar) para pruebas de control de acceso a nivel de petición.
- Gestor de base de datos (phpMyAdmin) para verificar el estado de los registros antes y después de cada prueba.
- Archivos de ejemplo en PDF, DOCX y TXT, incluyendo variantes fuera de los formatos permitidos.

## 1.4 Responsables

La ejecución de las pruebas estuvo a cargo del estudiante desarrollador del proyecto, con el acompañamiento del docente de la materia para la validación de los criterios de aceptación.

## 1.5 Cronograma de pruebas

| Actividad | Inicio | Fin |
|---|---|---|
| Preparación del entorno de pruebas (XAMPP, base de datos, datos de ejemplo) | 15 sep 2026 | 16 sep 2026 |
| Ejecución de pruebas funcionales y de validación de archivos | 17 sep 2026 | 18 sep 2026 |
| Ejecución de pruebas de procesamiento de IA, clasificación y búsqueda | 19 sep 2026 | 20 sep 2026 |
| Ejecución de pruebas de seguridad básica y casos límite | 21 sep 2026 | 21 sep 2026 |
| Consolidación de resultados, registro de defectos y matriz de trazabilidad | 22 sep 2026 | 22 sep 2026 |
