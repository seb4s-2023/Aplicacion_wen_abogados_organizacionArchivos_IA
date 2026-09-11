# 13. Control de versiones mediante Git

**Proyecto:** Sistema Inteligente de Gestión y Análisis Documental
**Cliente / caso de uso:** Bufete Restrepo & Asociados

---

**Estado actual:** el código fuente se ha desarrollado en local (XAMPP) y todavía no se encuentra en un repositorio Git. Se deja a continuación el procedimiento y la convención que se seguirá al subirlo, para dar cumplimiento a este punto del alcance del proyecto.

## 13.1 Procedimiento de inicialización

```bash
git init
echo "config/database.php" >> .gitignore
echo "config/gemini.php" >> .gitignore
echo "vendor/" >> .gitignore
echo "uploads/*" >> .gitignore
git add .
git commit -m "Commit inicial: estructura base del sistema"
git branch -M main
git remote add origin <url-del-repositorio>
git push -u origin main
```

## 13.2 Convención de commits a seguir

- `feat:` nueva funcionalidad (ej. `feat: agregar búsqueda documental`)
- `fix:` corrección de errores (ej. `fix: validar categoría antes de guardar`)
- `docs:` cambios de documentación
- `refactor:` cambios internos sin alterar el comportamiento

Se recomienda un commit por hito funcional (autenticación, repositorios, documentos, extracción, integración IA, búsqueda, consulta en lenguaje natural, dashboard) en vez de commits masivos, de forma que el historial de Git sirva también como evidencia de avance para la sustentación.
