# 📥 Instrucciones para Restaurar Base de Datos Local a Producción

## Análisis de Base de Datos

### Tablas Utilizadas ✅
- `usuarios` - Gestión de usuarios y autenticación
- `cuentas_bancarias` - Gestión de cuentas bancarias
- `categorias` - Categorías de transacciones
- `transacciones` - Registro de transacciones
- `presupuestos` - Presupuestos por categoría
- `transferencias` - Transferencias entre cuentas
- `configuraciones` - Configuraciones del sistema (no usada activamente pero se mantiene)

### Tablas NO Utilizadas ❌
- `metas_ahorro` - **ELIMINADA del código**, no se usa en el proyecto

## Proceso de Restauración

### Paso 1: Exportar Base de Datos Local

**Opción A: Desde el navegador**
1. Abre: `http://localhost/export_database.php`
2. El script generará el SQL con todos los datos
3. Copia el contenido y guárdalo en un archivo `database_export.sql`

**Opción B: Desde línea de comandos**
```bash
php export_database.php > database_export.sql
```

### Paso 2: Preparar Base de Datos en Producción

1. Asegúrate de que la estructura de la base de datos esté creada en producción
2. Si no está creada, ejecuta `database_completo_postgresql.sql` primero
3. O usa `init_database.php` para crear la estructura

### Paso 3: Importar Datos a Producción

**Opción A: Desde el navegador (recomendado)**
1. Sube el archivo `database_export.sql` a tu servidor de producción
2. Abre: `https://tu-dominio.com/import_database.php`
3. Selecciona el archivo SQL o pega el contenido
4. Marca "Limpiar base de datos antes de importar" (recomendado)
5. Haz clic en "IMPORTAR BASE DE DATOS"

**Opción B: Desde línea de comandos (PostgreSQL)**
```bash
# Conectar a la base de datos de producción
psql -h tu-host -U tu-usuario -d tu-base-de-datos < database_export.sql
```

**Opción C: Usando pg_dump/pg_restore (PostgreSQL - más eficiente)**
```bash
# Exportar desde local
pg_dump -h localhost -U usuario -d base_datos_local > database_export.dump

# Importar a producción
pg_restore -h tu-host-produccion -U usuario -d base_datos_produccion database_export.dump
```

## Notas Importantes

1. **Backup primero**: Siempre haz un backup de la base de datos de producción antes de importar
2. **Estructura primero**: Asegúrate de que la estructura de tablas esté creada antes de importar datos
3. **Metas de ahorro**: La tabla `metas_ahorro` NO se exporta (no se usa en el proyecto)
4. **Secuencias**: En PostgreSQL, las secuencias se reinician automáticamente al importar

## Verificación Post-Importación

1. Verifica que todas las tablas tengan datos: `verify_database.php`
2. Inicia sesión con un usuario de prueba
3. Verifica que el dashboard muestre datos correctamente
4. Revisa transacciones, cuentas y presupuestos

## Solución de Problemas

### Error: "relation does not exist"
- La estructura de la base de datos no está creada
- Ejecuta `database_completo_postgresql.sql` primero

### Error: "duplicate key value"
- Ya existen datos en la base de datos
- Usa la opción "Limpiar base de datos antes de importar"

### Error: "permission denied"
- Verifica permisos del usuario de la base de datos
- Asegúrate de tener permisos de INSERT, UPDATE, DELETE

