# 🗄️ Guía de Copias de Seguridad - NexaCore

Esta guía detalla cómo gestionar los respaldos de la base de datos, optimizados para el entorno de **Hostinger**.

## 🚀 Comandos de Producción (Recomendados)

Estos comandos funcionan en PHP puro y son compatibles con servidores que tienen deshabilitado `exec()` o `system()`.

### 1. Generar Backup
Crea una copia comprimida de toda la base de datos (estructura y datos).
```bash
php artisan db:backup-php
```
- **Ubicación:** `storage/app/backups/backup_xxxx.sql.gz`
- **Nota:** El sistema elimina automáticamente copias con más de 15 días de antigüedad.

### 2. Restaurar Backup
Recupera la base de datos desde un archivo comprimido.
```bash
php artisan db:restore-php
```
- El comando te mostrará una lista de los backups disponibles para elegir.
- **Advertencia:** Esto borrará los datos actuales y los reemplazará por los del backup.

---

## 🛠️ Comandos Alternativos (Solo si `exec` está permitido)

Estos comandos usan `mysqldump` y son más rápidos, pero requieren permisos de sistema.

- **Backup:** `php artisan db:backup`
- **Restore:** `php artisan db:restore`

---

## 📅 Automatización (Scheduler)

El sistema ya está configurado para realizar un backup automático **cada medianoche**. 

Para asegurar que funcione en el servidor, el **Cron Job** debe estar apuntando a:
```bash
php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```

---

## ⚠️ Notas de Seguridad
1. Los archivos se guardan en `storage/app/backups`. Asegúrate de que esta carpeta no sea accesible públicamente.
2. Antes de realizar un cambio masivo en la base de datos, siempre corre manualemente `php artisan db:backup-php`.
