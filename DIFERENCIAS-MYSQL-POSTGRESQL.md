# 📊 Diferencias entre MySQL/MariaDB y PostgreSQL

## 🔄 Principales Diferencias en el SQL

### 1. **Tipos de Datos**

| MySQL/MariaDB | PostgreSQL |
|---------------|------------|
| `INT AUTO_INCREMENT` | `SERIAL` |
| `DATETIME` | `TIMESTAMP` |
| `TINYINT(1)` | `BOOLEAN` |
| `BOOLEAN DEFAULT 1` | `BOOLEAN DEFAULT TRUE` |
| `BOOLEAN DEFAULT 0` | `BOOLEAN DEFAULT FALSE` |

### 2. **Comandos Específicos**

| MySQL/MariaDB | PostgreSQL |
|---------------|------------|
| `USE database_name;` | No existe (se conecta directamente a la BD) |
| `ON UPDATE CURRENT_TIMESTAMP` | No existe (se usa trigger) |
| `ENGINE=InnoDB` | No existe |
| `DEFAULT CHARSET=utf8mb4` | No existe |

### 3. **Auto-incremento**

**MySQL/MariaDB:**
```sql
id INT AUTO_INCREMENT PRIMARY KEY
```

**PostgreSQL:**
```sql
id SERIAL PRIMARY KEY
```

### 4. **Actualización Automática de Timestamps**

**MySQL/MariaDB:**
```sql
fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**PostgreSQL:**
```sql
fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- Y se crea un trigger para actualizar automáticamente
CREATE TRIGGER update_tabla_updated_at BEFORE UPDATE ON tabla
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
```

### 5. **Manejo de Conflictos en INSERT**

**MySQL/MariaDB:**
```sql
INSERT INTO ... VALUES ...
-- Si hay duplicado, falla o se ignora según configuración
```

**PostgreSQL:**
```sql
INSERT INTO ... VALUES ...
ON CONFLICT (campo_unico) DO NOTHING;
-- O
ON CONFLICT (campo_unico) DO UPDATE SET campo = EXCLUDED.campo;
```

### 6. **Consultas de Verificación**

**MySQL/MariaDB:**
```sql
SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'tabla';
```

**PostgreSQL:**
```sql
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = 'public' AND table_name = 'tabla';
```

## 📁 Archivos Disponibles

1. **`database_completo_mariaDB.sql`** - Versión para MySQL/MariaDB
2. **`database_completo_postgresql.sql`** - Versión nativa para PostgreSQL (recomendado para Render.com)

## ✅ Recomendación

Para Render.com con PostgreSQL, usa:
- **`database_completo_postgresql.sql`** - No requiere conversión, es más eficiente y confiable

El script `init_database.php` detectará automáticamente si usas PostgreSQL y cargará el archivo correcto.

