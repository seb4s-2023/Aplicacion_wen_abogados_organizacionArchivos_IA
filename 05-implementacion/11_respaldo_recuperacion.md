# 11. Estrategia básica de respaldo y recuperación

**Estado actual:** el proyecto no cuenta todavía con un mecanismo de respaldo automatizado; se deja documentada la estrategia mínima recomendada para adoptar antes de un uso real por parte del bufete.

| Elemento a respaldar | Método recomendado | Frecuencia sugerida |
|---|---|---|
| Base de datos (`bufete_ia`) | `mysqldump bufete_ia > backup_fecha.sql`, o exportación desde phpMyAdmin | Diaria mientras haya cambios activos; semanal en uso estable |
| Archivos originales (`uploads/`) | Copia del directorio completo a un disco o almacenamiento externo | Igual frecuencia que el backup de base de datos, para mantener ambos sincronizados |
| Configuración (`config/`) | Copia segura y cifrada, separada del backup general (contiene credenciales) | Cada vez que cambie una credencial |

## 11.1 Recuperación ante fallo

- Restaurar la base de datos desde el último backup con `mysql bufete_ia < backup_fecha.sql`.
- Restaurar la carpeta `uploads/` desde su copia más reciente.
- Verificar con un caso de prueba funcional (login + apertura de un repositorio existente) que la restauración quedó consistente, antes de reanudar el uso normal.
