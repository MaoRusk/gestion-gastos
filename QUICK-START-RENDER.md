# ⚡ Inicio Rápido - Deploy en Render.com

## 🎯 Resumen de Pasos

### 1️⃣ Preparar Repositorio
```bash
git add .
git commit -m "Configuración para Render.com"
git push origin main
```

### 2️⃣ Crear Base de Datos en Render
1. Dashboard → **New +** → **PostgreSQL** (o MySQL si disponible)
2. Name: `fime-gastos-db`
3. Plan: **Free**
4. **Anota las credenciales** (Internal Database URL, User, Password)

### 3️⃣ Crear Web Service
1. Dashboard → **New +** → **Web Service**
2. Conecta tu repositorio
3. Configuración:
   - **Environment**: PHP
   - **Build Command**: (vacío)
   - **Start Command**: `php -S 0.0.0.0:$PORT -t .`
   - **Plan**: Free

### 4️⃣ Variables de Entorno
Agrega en **Advanced → Environment Variables**:
```
DB_TYPE=mysql
DB_HOST=<tu-host-de-render>
DB_USER=<tu-usuario>
DB_PASSWORD=<tu-contraseña>
DB_NAME=fime_gastos_db
```

### 5️⃣ Inicializar Base de Datos
Visita: `https://tu-app.onrender.com/init_database.php`

### 6️⃣ Acceder
- URL: `https://tu-app.onrender.com`
- Usuario: `admin@fime.com`
- Contraseña: `admin123`

---

📖 **Guía completa**: Ver `DEPLOY-RENDER.md`

