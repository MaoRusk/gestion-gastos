# 🚀 Sistema de Gestión de Gastos Personales - FIME

## 📋 Instrucciones para Desarrollo

### 🔧 **Configuración Actual**
El sistema está configurado en **modo desarrollo** para funcionar sin base de datos.

### 🔐 **Credenciales de Prueba**
- **Usuario**: `admin`
- **Contraseña**: `123456`

### 🌐 **Cómo Acceder**
1. Inicia el servidor PHP:
   ```bash
   php -S localhost:8080
   ```

2. Abre tu navegador y ve a:
   ```
   http://localhost:8080
   ```

3. Usa las credenciales de prueba para iniciar sesión

### 📁 **Estructura del Proyecto**
```
PIA/
├── auth-signin-basic.php      # Página de login
├── auth-signup-basic.php      # Página de registro
├── dashboard-gastos.php       # Dashboard principal
├── cuentas-lista.php          # Lista de cuentas
├── cuentas-agregar.php        # Agregar cuenta
├── transacciones-lista.php    # Lista de transacciones
├── transacciones-agregar.php  # Agregar transacción
├── layouts/
│   ├── config.php             # Configuración (modo desarrollo)
│   ├── sidebar-gastos.php     # Menú personalizado
│   └── ...                    # Otros archivos de layout
└── assets/                    # CSS, JS, imágenes
```

### ⚙️ **Modo Desarrollo vs Producción**

#### 🔧 **Modo Desarrollo** (Actual)
- ✅ No requiere base de datos
- ✅ Autenticación simple (admin/123456)
- ✅ Datos de ejemplo en el dashboard
- ✅ Perfecto para desarrollo y pruebas

#### 🚀 **Modo Producción**
Para cambiar a modo producción:
1. Edita `layouts/config.php`
2. Cambia `$DEVELOPMENT_MODE = false;`
3. Configura la base de datos MySQL
4. Instala la extensión mysqli de PHP

### 🎯 **Funcionalidades Disponibles**
- ✅ **Autenticación**: Login y registro
- ✅ **Dashboard**: Resumen financiero con gráficos
- ✅ **Cuentas**: Lista y agregar cuentas bancarias
- ✅ **Transacciones**: Lista y agregar transacciones
- ✅ **Navegación**: Menú lateral personalizado
- ✅ **Responsive**: Diseño adaptable
- ✅ **Modo Oscuro/Claro**: Toggle funcional en la barra superior

### 🛠️ **Próximos Pasos**
1. **Sprint 3**: Completar gestión de cuentas
2. **Sprint 4**: Sistema completo de transacciones
3. **Sprint 5**: Categorías y etiquetas
4. **Sprint 6**: Reportes avanzados
5. **Sprint 7**: Sistema de presupuestos

### 🐛 **Solución de Problemas**

#### Error: `mysqli_connect()`
- ✅ **Solucionado**: El sistema usa modo desarrollo
- No se requiere base de datos para funcionar

#### Error: Página en blanco
- Verifica que el servidor PHP esté corriendo
- Revisa la consola del navegador para errores

#### Error: Estilos no cargan
- Verifica que la carpeta `assets/` esté presente
- Asegúrate de que los archivos CSS estén en `assets/css/`

### 📞 **Soporte**
Si encuentras algún problema:
1. Verifica que estés usando las credenciales correctas
2. Asegúrate de que el servidor PHP esté corriendo
3. Revisa la consola del navegador para errores

---
**Desarrollado para estudiantes de FIME** 🎓
