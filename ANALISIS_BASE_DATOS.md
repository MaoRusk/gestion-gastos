# 📊 Análisis de Base de Datos - Sistema FIME

## Tablas en la Base de Datos

### ✅ Tablas UTILIZADAS en el código:

1. **usuarios** ✅
   - Usada en: autenticación, gestión de usuarios
   - Archivos: `includes/auth_functions.php`, `usuarios-lista.php`, `create_admin.php`

2. **cuentas_bancarias** ✅
   - Usada en: gestión de cuentas, dashboard
   - Archivos: `cuentas-*.php`, `dashboard-gastos.php`, `transacciones-agregar.php`

3. **categorias** ✅
   - Usada en: gestión de categorías, transacciones, presupuestos
   - Archivos: `categorias-*.php`, `transacciones-*.php`, `presupuestos-*.php`, `dashboard-gastos.php`

4. **transacciones** ✅
   - Usada en: gestión de transacciones, dashboard, reportes
   - Archivos: `transacciones-*.php`, `dashboard-gastos.php`, `reportes.php`

5. **presupuestos** ✅
   - Usada en: gestión de presupuestos
   - Archivos: `presupuestos-*.php`, `reportes.php`

6. **transferencias** ✅
   - Usada en: creación de transferencias entre cuentas
   - Archivos: `transacciones-agregar.php`

7. **configuraciones** ⚠️
   - Usada en: solo en scripts de trash2 (no en código activo)
   - Estado: Tabla existe pero no se usa en la aplicación actual
   - Recomendación: Mantener por si se necesita en el futuro

### ❌ Tablas NO UTILIZADAS:

1. **metas_ahorro** ❌
   - Estado: Tabla existe en la base de datos pero NO se usa en el código
   - Eliminada de: `dashboard-gastos.php`, `clean-database.php`
   - Recomendación: **ELIMINAR** de la base de datos o dejarla sin usar

## Resumen de Uso

| Tabla | Estado | Uso en Código |
|-------|--------|---------------|
| usuarios | ✅ Activa | Autenticación, gestión usuarios |
| cuentas_bancarias | ✅ Activa | Gestión de cuentas, dashboard |
| categorias | ✅ Activa | Gestión categorías, transacciones |
| transacciones | ✅ Activa | Gestión transacciones, reportes |
| presupuestos | ✅ Activa | Gestión presupuestos |
| transferencias | ✅ Activa | Creación de transferencias |
| configuraciones | ⚠️ Parcial | Solo en scripts antiguos |
| **metas_ahorro** | ❌ **No usada** | **Eliminada del código** |

## Recomendaciones

1. **Eliminar tabla `metas_ahorro`** de la base de datos si no se planea usar
2. **Mantener `configuraciones`** por si se necesita en el futuro
3. **Mantener `transferencias`** - se usa en transacciones-agregar.php

## Scripts de Exportación/Importación

- **export_database.php**: Exporta datos de la base de datos local (excluye metas_ahorro)
- **import_database.php**: Importa/restaura datos a producción

## Archivos No Utilizados Identificados

- Carpeta `trash2/` (8.2MB) - Archivos de prueba y versiones antiguas
- Archivos `pages-*.php` - Páginas de ejemplo del template
- Archivos `auth-*-cover.php` - Variantes de autenticación no utilizadas
- `landing.php` - Página de ejemplo
- Documentación de deploy duplicada

Usar `cleanup_unused_files.php` para eliminar estos archivos.

