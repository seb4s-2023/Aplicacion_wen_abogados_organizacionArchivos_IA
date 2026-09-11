# 6. Diagrama de despliegue

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

![Diagrama de despliegue](./media/diagrama-despliegue.png)

*Figura 4. Diagrama de despliegue: navegador, servidor de aplicación, base de datos y servicio en la nube.*

El despliegue contempla cuatro nodos:

- **Equipo del usuario:** el navegador web se conecta al servidor mediante HTTPS/HTTP (puerto 80/443).
- **Servidor de aplicación (XAMPP / LAMP, Apache + PHP 8):** ejecuta la aplicación PHP (`bufete-ia/`), que lee y escribe archivos en `/uploads` (PDF/DOCX/TXT).
- **Servidor de base de datos:** MySQL 8 (`bufete_ia`), accedido desde la aplicación vía PDO / TCP 3306.
- **Nube (servicio externo):** Google Gemini API, accedida desde la aplicación vía HTTPS (cURL) / TCP 443.
