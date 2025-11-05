# 🚀 Guía de Deploy en Render.com

Esta guía te ayudará a desplegar tu Sistema de Gestión de Gastos Personales en Render.com paso a paso.

## 📋 Requisitos Previos

1. **Cuenta en Render.com**: Regístrate en [render.com](https://render.com) (gratis)
2. **Repositorio Git**: Tu proyecto debe estar en GitHub, GitLab o Bitbucket
3. **Base de Datos MySQL**: Render proporcionará una base de datos MySQL gratuita

---

## 📝 Paso 1: Preparar el Repositorio

### 1.1 Verificar archivos de configuración

Asegúrate de que estos archivos estén en tu repositorio:
- ✅ `render.yaml` - Configuración de Render
- ✅ `layouts/config.php` - Ya configurado para usar variables de entorno
- ✅ `init_database.php` - Script de inicialización de BD
- ✅ `database_completo_mariaDB.sql` o `database_schema.sql` - Esquema de base de datos

### 1.2 Hacer commit y push

```bash
git add .
git commit -m "Configuración para deploy en Render.com"
git push origin main
```

---

## 🗄️ Paso 2: Crear Base de Datos MySQL en Render

1. **Inicia sesión en Render.com** y ve al Dashboard
2. Haz clic en **"New +"** → **"PostgreSQL"** (o busca "MySQL" si está disponible)
3. **Nota**: Si solo aparece PostgreSQL, puedes usar PostgreSQL cambiando la configuración, o crear un servicio MySQL externo
4. Si MySQL está disponible:
   - **Name**: `fime-gastos-db`
   - **Database**: `fime_gastos`
   - **User**: `fime_gastos_user`
   - **Plan**: Selecciona **Free** (gratis)
   - Haz clic en **"Create Database"**

5. **Anota las credenciales** que Render te proporciona:
   - Host (Internal Database URL)
   - Port
   - Database Name
   - User
   - Password

---

## 🌐 Paso 3: Crear Servicio Web

1. En el Dashboard de Render, haz clic en **"New +"** → **"Web Service"**
2. Conecta tu repositorio:
   - Selecciona **"Connect a repository"**
   - Autoriza Render para acceder a tu repositorio
   - Selecciona el repositorio de tu proyecto
   - Selecciona la rama `main` (o `master`)

3. **Configuración del servicio**:
   - **Name**: `sistema-gastos-fime` (o el nombre que prefieras)
   - **Environment**: Selecciona **PHP**
   - **Region**: Elige la región más cercana (ej: Oregon)
   - **Branch**: `main`
   - **Root Directory**: Deja vacío (o usa `/` si es necesario)
   - **Build Command**: Deja vacío
   - **Start Command**: `php -S 0.0.0.0:$PORT -t .`
   - **Plan**: Selecciona **Free**

4. **Variables de Entorno**:
   Haz clic en **"Advanced"** y agrega estas variables:

   ```
   DB_TYPE=mysql
   DB_HOST=<tu-host-de-base-de-datos>
   DB_USER=<tu-usuario-de-base-de-datos>
   DB_PASSWORD=<tu-contraseña-de-base-de-datos>
   DB_NAME=fime_gastos
   ```

   **Cómo obtener los valores**:
   - Ve a tu base de datos en Render
   - Copia los valores de:
     - **Internal Database URL** → Usa el host (sin el prefijo `mysql://`)
     - **User** → Usuario
     - **Password** → Contraseña
     - **Database** → Nombre de la base de datos

   **Ejemplo de configuración**:
   ```
   DB_TYPE=mysql
   DB_HOST=dpg-xxxxx-a.oregon-postgres.render.com
   DB_USER=fime_gastos_user
   DB_PASSWORD=tu_contraseña_aqui
   DB_NAME=fime_gastos
   ```

5. **Conectar la Base de Datos**:
   - En la sección **"Services"**, haz clic en **"Add Service"**
   - Selecciona tu base de datos creada anteriormente
   - Esto sincronizará automáticamente las variables de entorno

6. Haz clic en **"Create Web Service"**

---

## 🔧 Paso 4: Inicializar la Base de Datos

Una vez que tu servicio web esté desplegado:

1. **Obtén la URL de tu aplicación**:
   - Render te dará una URL como: `https://sistema-gastos-fime.onrender.com`

2. **Inicializa la base de datos**:
   - Visita: `https://tu-app.onrender.com/init_database.php`
   - Este script creará todas las tablas necesarias
   - Deberías ver mensajes de éxito para cada tabla creada

3. **Verifica la inicialización**:
   - Si ves errores de "table already exists", es normal (ignóralos)
   - Si todo está correcto, verás: "🎉 ¡Base de datos configurada completamente!"

---

## ✅ Paso 5: Verificar el Deploy

1. **Accede a tu aplicación**:
   - Visita: `https://tu-app.onrender.com`
   - Deberías ser redirigido a la página de inicio de sesión

2. **Credenciales por defecto**:
   - Email: `admin@fime.com`
   - Contraseña: `admin123`

3. **Si no tienes usuarios**, puedes:
   - Crear uno nuevo desde la página de registro
   - O ejecutar el script de inicialización con datos de prueba

---

## 🔒 Paso 6: Configuración de Seguridad

### 6.1 Cambiar credenciales por defecto

1. Inicia sesión con el usuario admin
2. Ve a la configuración de perfil
3. Cambia la contraseña del administrador

### 6.2 Variables de entorno sensibles

Render ya está protegiendo tus credenciales de base de datos mediante variables de entorno. No están expuestas en el código.

---

## 🐛 Solución de Problemas Comunes

### Problema: "Could not connect to MySQL"

**Solución**:
1. Verifica que las variables de entorno estén correctamente configuradas
2. Asegúrate de usar el **Internal Database URL** (no el External)
3. Verifica que el nombre de la base de datos sea correcto
4. Revisa los logs en Render: Dashboard → Tu servicio → Logs

### Problema: "404 Not Found"

**Solución**:
1. Verifica que `index.php` esté en la raíz del proyecto
2. Asegúrate de que el Start Command sea: `php -S 0.0.0.0:$PORT -t .`
3. Verifica que no haya un archivo `.htaccess` que interfiera

### Problema: "Base de datos no inicializada"

**Solución**:
1. Visita `/init_database.php` manualmente
2. Revisa los logs del script
3. Verifica que el archivo SQL esté en el repositorio

### Problema: La aplicación se "duerme" (Free tier)

**Solución**:
- En el plan gratuito, Render pone a dormir los servicios después de 15 minutos de inactividad
- La primera solicitud después de dormir puede tardar ~30 segundos
- Considera actualizar a un plan de pago si necesitas que esté siempre activo

---

## 📊 Monitoreo y Logs

1. **Ver logs en tiempo real**:
   - Dashboard → Tu servicio web → Logs
   - Puedes ver todos los errores y mensajes de PHP aquí

2. **Métricas**:
   - Render proporciona métricas básicas en el dashboard
   - CPU, memoria, y tiempo de respuesta

---

## 🔄 Actualizar el Deploy

Cada vez que hagas cambios:

```bash
git add .
git commit -m "Descripción de cambios"
git push origin main
```

Render detectará automáticamente los cambios y desplegará la nueva versión.

---

## 📝 Notas Importantes

1. **Plan Gratuito**:
   - Los servicios gratuitos se "duermen" después de 15 minutos de inactividad
   - El primer acceso después de dormir puede tardar ~30 segundos
   - La base de datos gratuita puede tener limitaciones de tamaño

2. **Variables de Entorno**:
   - Nunca commitees credenciales en el código
   - Usa siempre variables de entorno para información sensible

3. **Base de Datos**:
   - En el plan gratuito, las bases de datos pueden tener limitaciones
   - Considera hacer backups regulares
   - Render proporciona backups automáticos en planes superiores

---

## 🎯 Checklist Final

- [ ] Repositorio en Git con todos los archivos
- [ ] Base de datos MySQL creada en Render
- [ ] Servicio web creado y configurado
- [ ] Variables de entorno configuradas correctamente
- [ ] Base de datos inicializada (`/init_database.php`)
- [ ] Aplicación accesible y funcionando
- [ ] Credenciales por defecto cambiadas

---

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs en Render Dashboard
2. Verifica la configuración de variables de entorno
3. Asegúrate de que todos los archivos estén en el repositorio
4. Consulta la documentación de Render: [docs.render.com](https://docs.render.com)

---

**¡Feliz Deploy! 🚀**

---

*Última actualización: 2024*

