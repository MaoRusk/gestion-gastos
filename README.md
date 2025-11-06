# Sistema de Gestión de Gastos Personales - FIME

Sistema web desarrollado en PHP para que los estudiantes de la Facultad de Ingeniería Mecánica y Eléctrica (FIME) gestionen de forma eficiente sus finanzas personales mediante el registro, control y análisis de ingresos y gastos.

## 🚀 Características Principales

### ✅ Módulos Implementados

1. **Dashboard Principal**
   - Resumen financiero en tiempo real
   - Gráficos de evolución temporal
   - Transacciones recientes
   - Acciones rápidas

2. **Gestión de Cuentas Bancarias**
   - Registro de múltiples cuentas
   - Tipos: Corriente, Ahorros, Crédito
   - Cálculo automático de balances
   - Estados de cuenta

3. **Sistema de Transacciones**
   - Ingresos, gastos y transferencias
   - Categorización automática
   - Filtros y búsquedas avanzadas
   - Transacciones recurrentes

4. **Categorías Personalizadas**
   - Categorías predefinidas y personalizadas
   - Colores e íconos personalizables
   - Organización por tipos (ingreso/gasto)

5. **Presupuestos**
   - Creación de presupuestos por categoría
   - Seguimiento en tiempo real
   - Alertas de sobrepaso
   - Períodos flexibles

6. **Reportes y Análisis**
   - Gráficos interactivos (Chart.js)
   - Análisis por período
   - Gastos por categoría
   - Tendencias financieras

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (sin frameworks)
- **Base de Datos**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Bootstrap 5
- **Gráficos**: Chart.js
- **Íconos**: Remix Icons

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 8.0 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: mysqli, session, json

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone [url-del-repositorio]
cd sistema-gastos-fime
```

### 2. Configurar la Base de Datos
```bash
# Editar el archivo de configuración
nano layouts/config.php

# Ejecutar las migraciones
php migrate_database.php
```

### 3. Configurar el Servidor Web
- Colocar los archivos en el directorio del servidor web
- Asegurar permisos de escritura en directorios necesarios
- Configurar virtual host si es necesario

### 4. Acceder al Sistema
- URL: `http://localhost/sistema-gastos-fime/`
- Usuario por defecto: `admin@fime.com`
- Contraseña: `admin123`

## 📁 Estructura del Proyecto

```
sistema-gastos-fime/
├── assets/                 # Recursos estáticos
├── includes/              # Funciones auxiliares
├── layouts/               # Plantillas de layout
│   ├── config.php        # Configuración de BD
│   ├── sidebar-gastos.php # Menú lateral
│   └── ...
├── auth-*.php            # Páginas de autenticación
├── dashboard-gastos.php  # Dashboard principal
├── cuentas-*.php         # Gestión de cuentas
├── transacciones-*.php   # Gestión de transacciones
├── categorias-*.php      # Gestión de categorías
├── presupuestos-*.php    # Gestión de presupuestos
├── reportes.php          # Reportes y análisis
├── database.sql          # Estructura de BD
├── migrate_database.php  # Script de migración
└── README.md            # Este archivo
```

## 🗄️ Base de Datos

### Tablas Principales

1. **usuarios** - Información de usuarios
2. **cuentas_bancarias** - Cuentas bancarias de usuarios
3. **categorias** - Categorías de transacciones
4. **transacciones** - Registro de transacciones
5. **transferencias** - Transferencias entre cuentas
6. **presupuestos** - Presupuestos por categoría
7. **metas_ahorro** - Metas de ahorro (futuro)
8. **configuraciones** - Configuraciones del sistema

## 🔐 Seguridad

- Autenticación basada en sesiones
- Validación de entrada de datos
- Prepared statements para consultas SQL
- Sanitización de datos de usuario
- Protección contra inyección SQL

## 📊 Funcionalidades del Dashboard

### Resumen Financiero
- Balance total de todas las cuentas
- Ingresos del mes actual
- Gastos del mes actual
- Ahorros del mes

### Gráficos Interactivos
- Evolución de ingresos vs gastos (6 meses)
- Distribución de gastos por categoría
- Tendencias temporales

### Acciones Rápidas
- Nueva transacción
- Nueva cuenta bancaria
- Nuevo presupuesto
- Ver reportes

## 🎯 Módulos de Gestión

### Cuentas Bancarias
- **Lista de Cuentas**: Vista general con balances
- **Agregar Cuenta**: Formulario de registro
- **Tipos Soportados**: Corriente, Ahorros, Crédito
- **Validaciones**: Números de cuenta únicos

### Transacciones
- **Lista de Transacciones**: Con filtros avanzados
- **Nueva Transacción**: Formulario completo
- **Tipos**: Ingreso, Gasto, Transferencia
- **Características**: Recurrentes, categorización automática

### Categorías
- **Gestión de Categorías**: CRUD completo
- **Personalización**: Colores e íconos
- **Predefinidas**: Categorías del sistema
- **Organización**: Por tipo (ingreso/gasto)

### Presupuestos
- **Lista de Presupuestos**: Con seguimiento en tiempo real
- **Nuevo Presupuesto**: Por categoría y período
- **Alertas**: Sobrepaso de límites
- **Períodos**: Mensual, semanal, anual, personalizado

### Reportes
- **Análisis Temporal**: Gráficos de evolución
- **Por Categorías**: Distribución de gastos
- **Filtros**: Por período personalizable
- **Exportación**: Datos en formato CSV/JSON

## 🔧 Configuración

### Base de Datos
```php
// layouts/config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '1234');
define('DB_NAME', 'fime_gastos_db');
```

### Conexión PostgreSQL (configuración local)

Si quieres ejecutar el proyecto con PostgreSQL en desarrollo local, sigue estos pasos rápidos:

1. Instala PostgreSQL y la extensión PHP para PostgreSQL (ajusta la versión de PHP si es necesario):

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
sudo apt install php-pgsql    # o php8.1-pgsql / php8.2-pgsql según tu versión
```

2. Crear el usuario y la base de datos (ejecuta como usuario del sistema `postgres`):

```bash
# Crear usuario 'root' con contraseña '1234'
sudo -u postgres psql -c "CREATE USER root WITH PASSWORD '1234';"

# Crear la base de datos 'fime_gastos_db' y asignarla a root
sudo -u postgres psql -c "CREATE DATABASE fime_gastos_db OWNER root;"
```

3. Configura las variables de entorno o edita `layouts/config.php` para usar PostgreSQL:

```bash
export DB_TYPE=postgresql
export DB_HOST=localhost
export DB_USER=root
export DB_PASSWORD=1234
export DB_NAME=fime_gastos_db
export DB_PORT=5432
```

El archivo `layouts/config.php` prioriza las variables de entorno. Para desarrollo local el proyecto viene con la contraseña por defecto `1234` si no se define `DB_PASSWORD` en el entorno.

4. Probar la conexión (opcional): existe un script de prueba `test_db_connection.php` en la raíz del proyecto que intenta conectarse usando la configuración del proyecto y muestra si la conexión fue exitosa.

```bash
php test_db_connection.php
```

Notas de seguridad: no dejes credenciales en claro en producción — utiliza un gestor de secretos o variables de entorno seguras.

### Usuario Administrador
- Email: `admin@fime.com`
- Contraseña: `admin123`
- Rol: Administrador del sistema

## 📱 Responsive Design

El sistema está optimizado para:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (320px - 767px)

## 🚀 Próximas Características

- [ ] Metas de ahorro
- [ ] Notificaciones push
- [ ] Exportación de reportes
- [ ] API REST
- [ ] Aplicación móvil
- [ ] Integración con bancos

## 🤝 Contribución

1. Fork el proyecto
2. Crear una rama para la característica (`git checkout -b feature/nueva-caracteristica`)
3. Commit los cambios (`git commit -am 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Crear un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores

- **Equipo de Desarrollo FIME** - *Desarrollo inicial* - [FIME-UANL](https://fime.uanl.mx)

## 📞 Soporte

Para soporte técnico o consultas:
- Email: soporte@fime.uanl.mx
- Documentación: [Wiki del Proyecto]
- Issues: [GitHub Issues]

---

**Sistema de Gestión de Gastos Personales - FIME**  
*Desarrollado para estudiantes de la Facultad de Ingeniería Mecánica y Eléctrica*