# 🚀 Deploy en Render.com - Cuando NO aparece PHP como opción

Esta guía es para cuando **NO ves la opción "PHP" en el selector de Language** al crear un Web Service en Render.com.

## 📋 Opción 1: Usar Docker (Recomendado)

Si no aparece PHP como opción, Render puede detectar automáticamente el `Dockerfile` y usarlo.

### Pasos:

1. **Asegúrate de tener el Dockerfile en tu repositorio**
   - Ya está creado: `Dockerfile`
   - Este archivo define cómo construir la imagen PHP

2. **En Render.com, al crear el Web Service**:
   - **New +** → **Web Service**
   - Conecta tu repositorio
   - **Environment**: Selecciona **"Docker"** (debería aparecer automáticamente si detecta el Dockerfile)
   - Si no aparece Docker, ve a la **Opción 2** más abajo

3. **Configuración**:
   - **Name**: `sistema-gastos-fime`
   - **Region**: Elige la más cercana
   - **Branch**: `main`
   - **Root Directory**: (vacío)
   - **Build Command**: (dejar vacío - Docker lo maneja)
   - **Start Command**: (dejar vacío - Docker lo maneja)
   - **Plan**: Free

4. **Variables de Entorno** (en Advanced):
   ```
   DB_TYPE=postgresql
   DB_HOST=<tu-host-postgresql>
   DB_PORT=5432
   DB_USER=<tu-usuario>
   DB_PASSWORD=<tu-contraseña>
   DB_NAME=<nombre-de-tu-bd>
   ```

5. **Conectar Base de Datos**:
   - En la sección **"Services"**, haz clic en **"Add Service"**
   - Selecciona tu base de datos PostgreSQL
   - Esto sincronizará automáticamente las variables de entorno

---

## 📋 Opción 2: Configuración Manual (Sin Docker)

Si Docker tampoco está disponible, puedes configurar manualmente:

### Pasos:

1. **En Render.com**:
   - **New +** → **Web Service**
   - Conecta tu repositorio
   - **Environment**: Selecciona **"Node"** o **"Python"** (no importa, lo cambiaremos)
   - O simplemente deja cualquier opción

2. **Configuración Manual**:
   - **Name**: `sistema-gastos-fime`
   - **Region**: Elige la más cercana
   - **Branch**: `main`
   - **Root Directory**: (vacío)
   
   **IMPORTANTE - Build Command**:
   ```
   echo "No build needed for PHP"
   ```
   
   **IMPORTANTE - Start Command**:
   ```
   php -S 0.0.0.0:$PORT -t .
   ```

3. **Variables de Entorno** (en Advanced):
   ```
   PHP_VERSION=8.1
   DB_TYPE=postgresql
   DB_HOST=<tu-host-postgresql>
   DB_PORT=5432
   DB_USER=<tu-usuario>
   DB_PASSWORD=<tu-contraseña>
   DB_NAME=<nombre-de-tu-bd>
   ```

4. **Nota**: Render puede no tener PHP preinstalado en este caso. Si falla, usa la **Opción 3**.

---

## 📋 Opción 3: Usar Render Blueprint (render.yaml)

Puedes usar el archivo `render.yaml` que ya está creado:

### Pasos:

1. **Asegúrate de tener `render.yaml` en la raíz del proyecto**
   - Ya está creado en tu repositorio

2. **En Render.com**:
   - Dashboard → **New +** → **"Blueprint"**
   - Conecta tu repositorio
   - Render detectará automáticamente el `render.yaml`
   - Esto creará tanto el Web Service como la Base de Datos

3. **Después de crear el Blueprint**:
   - Ve a tu Web Service
   - Actualiza las variables de entorno con las credenciales reales de tu BD
   - El `render.yaml` tiene placeholders que necesitas reemplazar

---

## 🔧 Configuración de Variables de Entorno para PostgreSQL

### Cómo obtener las credenciales de PostgreSQL en Render:

1. Ve a tu base de datos PostgreSQL en Render
2. En la sección **"Connections"**, verás:
   - **Internal Database URL**: `postgresql://usuario:password@host:5432/database`
   - **External Database URL**: Similar pero para conexiones externas

3. **Extrae los valores**:
   - Si la URL es: `postgresql://fime_user:abc123@dpg-xxxxx-a.oregon-postgres.render.com:5432/fime_gastos_db`
   - Entonces:
     ```
     DB_HOST=dpg-xxxxx-a.oregon-postgres.render.com
     DB_PORT=5432
     DB_USER=fime_user
     DB_PASSWORD=abc123
     DB_NAME=fime_gastos_db
     ```

4. **Variables de Entorno a configurar**:
   ```
   DB_TYPE=postgresql
   DB_HOST=<extrae-el-host>
   DB_PORT=5432
   DB_USER=<extrae-el-usuario>
   DB_PASSWORD=<extrae-la-contraseña>
   DB_NAME=<extrae-el-nombre>
   ```

---

## 🗄️ Inicializar Base de Datos PostgreSQL

Una vez que el servicio esté desplegado:

1. **Visita**: `https://tu-app.onrender.com/init_database.php`
2. El script convertirá automáticamente el SQL de MySQL a PostgreSQL
3. Creará todas las tablas necesarias

---

## ✅ Verificar que Funciona

1. **Accede a tu aplicación**: `https://tu-app.onrender.com`
2. Deberías ver la página de inicio de sesión
3. Usa las credenciales por defecto:
   - Email: `admin@fime.com`
   - Contraseña: `admin123`

---

## 🐛 Solución de Problemas

### Error: "PHP not found" o "Command not found"

**Solución**: Usa la **Opción 1 (Docker)** que incluye PHP en la imagen.

### Error: "Could not connect to PostgreSQL"

**Solución**:
1. Verifica que uses el **Internal Database URL** (no el External)
2. Asegúrate de que las variables de entorno estén correctamente configuradas
3. Verifica que el puerto sea `5432`

### Error: "Extension pdo_pgsql not found"

**Solución**: El Dockerfile ya incluye la extensión. Si no usas Docker, Render puede no tenerla. Usa Docker (Opción 1).

### El servicio no inicia

**Solución**:
1. Revisa los logs en Render Dashboard
2. Verifica que el Start Command sea correcto
3. Si usas Docker, verifica que el Dockerfile esté correcto

---

## 📝 Notas Importantes

1. **Docker es la mejor opción** porque:
   - Garantiza que PHP y todas las extensiones estén instaladas
   - Funciona independientemente de las opciones disponibles en Render
   - Es más confiable para producción

2. **PostgreSQL vs MySQL**:
   - El código ya está adaptado para PostgreSQL
   - El script `init_database.php` convierte automáticamente el SQL
   - Funciona igual que con MySQL

3. **Variables de Entorno**:
   - Nunca commitees las credenciales
   - Usa siempre variables de entorno en Render

---

## 🎯 Checklist

- [ ] Dockerfile está en el repositorio
- [ ] render.yaml está en el repositorio
- [ ] Base de datos PostgreSQL creada en Render
- [ ] Variables de entorno configuradas correctamente
- [ ] Web Service creado (usando Docker o manual)
- [ ] Base de datos inicializada (`/init_database.php`)
- [ ] Aplicación accesible y funcionando

---

**¡Listo! Tu aplicación debería estar funcionando en Render.com** 🚀

