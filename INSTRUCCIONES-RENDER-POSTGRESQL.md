# 🚀 Instrucciones Rápidas - Deploy con PostgreSQL en Render.com

## ⚡ Pasos Rápidos

### 1. Crear Web Service en Render

**Si NO ves la opción PHP:**

1. **New +** → **Web Service**
2. Conecta tu repositorio
3. **Environment**: Selecciona **"Docker"** (Render detectará el Dockerfile automáticamente)
   - Si no aparece Docker, selecciona cualquier opción y luego configura manualmente
4. Configuración:
   - **Name**: `sistema-gastos-fime`
   - **Branch**: `main`
   - **Build Command**: (vacío)
   - **Start Command**: (vacío - Docker lo maneja)
   - **Plan**: Free

### 2. Variables de Entorno

En **Advanced → Environment Variables**, agrega:

```
DB_TYPE=postgresql
DB_HOST=<host-de-tu-postgresql>
DB_PORT=5432
DB_USER=<usuario>
DB_PASSWORD=<contraseña>
DB_NAME=<nombre-bd>
```

**Cómo obtener los valores:**
- Ve a tu base de datos PostgreSQL en Render
- Copia los valores de **Internal Database URL**
- Ejemplo: `postgresql://user:pass@host:5432/dbname`
  - Extrae: host, port (5432), user, password, dbname

### 3. Conectar Base de Datos

- En la sección **"Services"** del Web Service
- Haz clic en **"Add Service"**
- Selecciona tu base de datos PostgreSQL
- Esto sincronizará automáticamente las variables

### 4. Inicializar Base de Datos

Una vez desplegado, visita:
```
https://tu-app.onrender.com/init_database.php
```

### 5. Acceder

- URL: `https://tu-app.onrender.com`
- Usuario: `admin@fime.com`
- Contraseña: `admin123`

---

## 📋 Si Docker No Funciona

Si no puedes usar Docker, configura manualmente:

**Start Command**:
```
php -S 0.0.0.0:$PORT -t .
```

**Build Command**:
```
echo "No build needed"
```

**Variables de Entorno adicionales**:
```
PHP_VERSION=8.1
```

---

## 🔍 Verificar

1. Los logs en Render Dashboard muestran que el servicio está corriendo
2. La URL de tu app responde
3. `/init_database.php` crea las tablas correctamente

---

**Documentación completa**: Ver `DEPLOY-RENDER-SIN-PHP-OPTION.md`

