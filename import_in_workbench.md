# Importar en MySQL Workbench

## 📋 Pasos para importar los datos:

### Opción 1: Usando el archivo SQL directamente

1. **Abrir MySQL Workbench**
   - Abre MySQL Workbench

2. **Conectar al servidor**
   - Clic en la conexión local
   - Ingresa tu contraseña si se requiere

3. **Crear la base de datos**
   - En la consola SQL, ejecuta:
   ```sql
   CREATE DATABASE IF NOT EXISTS fime_gastos_db;
   USE fime_gastos_db;
   ```

4. **Importar el archivo**
   - Ve a `Server` → `Data Import`
   - Selecciona "Import from Self-Contained File"
   - Busca: `/home/market/Documents/FIME/PROYECTO INTEGRADOR II/PIA/fime_gastos_backup.sql`
   - Selecciona "fime_gastos_db" como base de datos
   - Marca "Add DROP DATABASE / DROP TABLE"
   - Clic en "Start Import"

### Opción 2: Copiar y pegar directamente

1. Abre el archivo `fime_gastos_backup.sql` en un editor
2. Copia TODO el contenido
3. En MySQL Workbench, abre una nueva query tab
4. Pega el contenido completo
5. Ejecuta la query (⚡ icon o F9)

### Opción 3: Crear usuario MySQL específico

Si tienes problemas de permisos, crea un usuario:

```sql
CREATE USER 'fime_user'@'localhost' IDENTIFIED BY 'fime_password';
GRANT ALL PRIVILEGES ON fime_gastos_db.* TO 'fime_user'@'localhost';
FLUSH PRIVILEGES;
```

Luego usa estas credenciales:
- Usuario: fime_user
- Contraseña: fime_password

## 🔍 Verificar que se importó correctamente

Ejecuta estas queries en Workbench:

```sql
USE fime_gastos_db;

SELECT 'usuarios' as tabla, COUNT(*) as total FROM usuarios
UNION ALL
SELECT 'cuentas_bancarias', COUNT(*) FROM cuentas_bancarias
UNION ALL
SELECT 'categorias', COUNT(*) FROM categorias
UNION ALL
SELECT 'transacciones', COUNT(*) FROM transacciones
UNION ALL
SELECT 'presupuestos', COUNT(*) FROM presupuestos
UNION ALL
SELECT 'metas_ahorro', COUNT(*) FROM metas_ahorro;
```

Deberías ver:
- usuarios: 1
- cuentas_bancarias: 3
- categorias: 28
- transacciones: 24
- presupuestos: 3
- metas_ahorro: 3

## ⚠️ Problemas comunes

1. **Error de permisos**: Necesitas acceso root o crear usuario
2. **Tabla ya existe**: Marca "Drop Database" en la importación
3. **Foreign key errors**: Ejecuta el script completo, no por partes

## 📞 ¿Necesitas ayuda?

Si las tablas siguen vacías después de importar, verifica:

1. ✅ ¿Se creó la base de datos `fime_gastos_db`?
2. ✅ ¿Estás en el esquema correcto (fime_gastos_db)?
3. ✅ ¿Hay errores en el log de importación?


