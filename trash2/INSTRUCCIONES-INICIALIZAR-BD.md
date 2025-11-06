# 🔧 Inicializar Base de Datos en Render.com

## ⚠️ IMPORTANTE

Antes de usar la aplicación, **DEBES inicializar la base de datos** ejecutando el script de inicialización.

## 📋 Pasos para Inicializar la Base de Datos

### 1. Accede al Script de Inicialización

Una vez que tu aplicación esté desplegada en Render.com:

1. Obtén la URL de tu aplicación (ejemplo: `https://sistema-gastos-fime.onrender.com`)
2. Visita la siguiente URL en tu navegador:

```
https://tu-app.onrender.com/init_database.php
```

### 2. Ejecutar el Script

El script:
- ✅ Detectará automáticamente que estás usando PostgreSQL
- ✅ Convertirá el esquema SQL de MySQL a PostgreSQL
- ✅ Creará todas las tablas necesarias
- ✅ Mostrará un resumen de lo que se creó

### 3. Verificar que Funcionó

Deberías ver:
- ✅ Mensajes de éxito para cada tabla creada
- ✅ Un resumen al final diciendo "🎉 ¡Base de datos configurada completamente!"
- ✅ Un enlace para acceder al sistema

### 4. Acceder al Sistema

Después de inicializar:
1. Visita: `https://tu-app.onrender.com`
2. Usa las credenciales por defecto:
   - **Email**: `admin@fime.com`
   - **Contraseña**: `admin123`

---

## 🐛 Solución de Problemas

### Error: "relation does not exist"

**Causa**: La base de datos no ha sido inicializada.

**Solución**: Ejecuta `init_database.php` como se explica arriba.

### Error: "table already exists"

**Causa**: El script ya se ejecutó anteriormente.

**Solución**: Es normal, puedes ignorar estos errores. Las tablas ya existen.

### Error: "Cannot connect to database"

**Causa**: Las variables de entorno no están configuradas correctamente.

**Solución**: 
1. Ve a Render Dashboard → Tu Web Service → Environment
2. Verifica que estas variables estén configuradas:
   - `DB_TYPE=postgresql`
   - `DB_HOST=<tu-host>`
   - `DB_PORT=5432`
   - `DB_USER=<tu-usuario>`
   - `DB_PASSWORD=<tu-contraseña>`
   - `DB_NAME=<nombre-bd>`

### El Script no Carga

**Solución**:
1. Verifica que el archivo `init_database.php` esté en el repositorio
2. Verifica que los archivos SQL estén en el repositorio:
   - `database_completo_mariaDB.sql` (prioridad)
   - O `database_schema.sql`
   - O `fime_gastos_database.sql`

---

## 📝 Notas

- El script es **seguro ejecutarlo múltiples veces** (no duplicará datos)
- Los errores de "already exists" son normales y se pueden ignorar
- El script convierte automáticamente el SQL de MySQL a PostgreSQL
- No necesitas crear las tablas manualmente

---

## ✅ Checklist

- [ ] Aplicación desplegada en Render.com
- [ ] Variables de entorno configuradas
- [ ] Base de datos PostgreSQL creada en Render
- [ ] Script `init_database.php` ejecutado exitosamente
- [ ] Puedes acceder al sistema y hacer login

---

**¿Necesitas ayuda?** Revisa los logs en Render Dashboard para ver errores específicos.

