# ⚠️ URGENTE: Inicializar Base de Datos

## 🔴 Error Actual

Estás viendo este error:
```
ERROR: relation "usuarios" does not exist
```

**Esto significa que la base de datos NO ha sido inicializada todavía.**

---

## ✅ SOLUCIÓN INMEDIATA

### Paso 1: Abre tu navegador

Ve a la siguiente URL (reemplaza `tu-app` con el nombre real de tu aplicación):

```
https://tu-app.onrender.com/init_database.php
```

### Paso 2: Ejecuta el Script

El script:
1. Creará todas las tablas necesarias
2. Convertirá el SQL de MySQL a PostgreSQL automáticamente
3. Mostrará un resumen de lo que se creó

### Paso 3: Verifica el Resultado

Deberías ver:
- ✅ Mensajes de éxito para cada tabla
- ✅ "🎉 ¡Base de datos configurada completamente!"
- ✅ Un enlace para acceder al sistema

### Paso 4: Intenta el Login Nuevamente

Después de inicializar:
- Ve a: `https://tu-app.onrender.com`
- Email: `admin@fime.com`
- Contraseña: `admin123`

---

## 📋 Checklist

- [ ] Abrí la URL: `https://tu-app.onrender.com/init_database.php`
- [ ] El script se ejecutó sin errores críticos
- [ ] Vi el mensaje "Base de datos configurada completamente"
- [ ] Puedo hacer login ahora

---

## 🐛 Si el Script No Funciona

### Error: "No se encontró ningún archivo SQL"

**Solución**: Verifica que estos archivos estén en tu repositorio:
- `database_completo_mariaDB.sql` (prioridad)
- O `database_schema.sql`
- O `fime_gastos_database.sql`

### Error: "Could not connect to database"

**Solución**: Verifica las variables de entorno en Render:
1. Dashboard → Tu Web Service → Environment
2. Verifica:
   - `DB_TYPE=postgresql`
   - `DB_HOST=<correcto>`
   - `DB_USER=<correcto>`
   - `DB_PASSWORD=<correcto>`
   - `DB_NAME=<correcto>`

### El Script se Carga pero No Crea Tablas

**Solución**: 
1. Revisa los logs en Render Dashboard
2. Verifica que los archivos SQL estén en el repositorio
3. Asegúrate de hacer commit y push de todos los archivos

---

## 💡 Nota Importante

**Este script DEBE ejecutarse ANTES de usar la aplicación por primera vez.**

Es seguro ejecutarlo múltiples veces - no duplicará datos.

---

**¿Necesitas ayuda?** Si después de ejecutar el script sigues teniendo problemas, comparte:
1. La URL completa que usaste
2. El mensaje exacto que aparece
3. Los logs de Render Dashboard

