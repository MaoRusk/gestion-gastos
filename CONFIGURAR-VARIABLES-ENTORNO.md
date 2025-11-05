# 🔧 Configurar Variables de Entorno en Render.com

## 📋 Tu URL de Conexión

Basado en tu URL:
```
postgresql://fime_gastos_db_user:XwQkjDmX8JZP27hLdPFxKbvSjqlKESvB@dpg-d45svnvdiees738gdg90-a/fime_gastos_db
```

## ✅ Variables de Entorno Necesarias

Ve a **Render Dashboard → Tu Web Service → Environment** y configura estas variables:

### Opción 1: Usar Internal Database URL (Recomendado)

En Render, cuando conectas tu base de datos al servicio web, automáticamente se crean estas variables. Pero si no están, puedes configurarlas manualmente:

```
DB_TYPE=postgresql
DB_HOST=dpg-d45svnvdiees738gdg90-a
DB_PORT=5432
DB_USER=fime_gastos_db_user
DB_PASSWORD=XwQkjDmX8JZP27hLdPFxKbvSjqlKESvB
DB_NAME=fime_gastos_db
```

### Opción 2: Conectar la Base de Datos al Servicio Web

**La forma más fácil:**

1. Ve a tu **Web Service** en Render
2. En la sección **"Services"** o **"Connections"**
3. Haz clic en **"Add Service"** o **"Connect Database"**
4. Selecciona tu base de datos PostgreSQL
5. Render automáticamente creará las variables de entorno

---

## 🔍 Verificar Configuración

Después de configurar las variables:

1. **Visita**: `https://tu-app.onrender.com/verify_database.php`
2. Este script te mostrará:
   - ✅ Qué variables de entorno están configuradas
   - ✅ Qué tablas existen
   - ✅ Si el usuario admin está creado

---

## 📝 Pasos Completos

### 1. Configurar Variables de Entorno

En Render Dashboard:
- Web Service → Environment → Add Environment Variable

Agregar:
- `DB_TYPE` = `postgresql`
- `DB_HOST` = `dpg-d45svnvdiees738gdg90-a` (o el host completo si Render lo requiere)
- `DB_PORT` = `5432`
- `DB_USER` = `fime_gastos_db_user`
- `DB_PASSWORD` = `XwQkjDmX8JZP27hLdPFxKbvSjqlKESvB`
- `DB_NAME` = `fime_gastos_db`

**Nota**: Si Render usa un host interno diferente, ve a tu base de datos PostgreSQL y copia el **"Internal Database URL"** o **"Internal Host"**.

### 2. Reiniciar el Servicio

Después de configurar las variables:
- Render debería reiniciar automáticamente
- O haz un "Manual Deploy" desde el dashboard

### 3. Verificar Conexión

Visita: `https://tu-app.onrender.com/verify_database.php`

### 4. Inicializar Base de Datos (si no lo has hecho)

Visita: `https://tu-app.onrender.com/init_database.php`

### 5. Crear Usuario Admin (si no existe)

Visita: `https://tu-app.onrender.com/create_admin.php`

### 6. Probar Login

Visita: `https://tu-app.onrender.com`
- Email: `admin@fime.com`
- Contraseña: `admin123`

---

## ⚠️ Notas Importantes

1. **Internal vs External URL**: 
   - Render tiene dos URLs: Internal y External
   - Para servicios web en Render, usa la **Internal URL**
   - El host interno puede ser diferente al que ves en la URL externa

2. **Cómo encontrar el Internal Host**:
   - Ve a tu base de datos PostgreSQL en Render
   - Busca "Internal Database URL" o "Internal Host"
   - Usa ese host en `DB_HOST`

3. **Puerto**: 
   - PostgreSQL usa puerto `5432` por defecto
   - Si tu base de datos usa otro puerto, ajústalo en `DB_PORT`

---

## 🐛 Solución de Problemas

### Error: "Could not connect to PostgreSQL"

**Solución**:
1. Verifica que uses el **Internal Host**, no el External
2. Asegúrate de que el puerto sea correcto
3. Verifica que las credenciales sean correctas

### Error: "relation does not exist"

**Solución**: Ejecuta `init_database.php` primero

### Error: "Usuario no encontrado"

**Solución**: Ejecuta `create_admin.php` para crear el usuario admin

---

## ✅ Checklist

- [ ] Variables de entorno configuradas en Render
- [ ] Servicio reiniciado
- [ ] Script `verify_database.php` muestra tablas existentes
- [ ] Script `init_database.php` ejecutado exitosamente
- [ ] Script `create_admin.php` ejecutado (si es necesario)
- [ ] Puedo hacer login correctamente

