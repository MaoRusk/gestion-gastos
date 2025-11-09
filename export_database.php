<?php
/**
 * Script para exportar datos de la base de datos local
 * Exporta todas las tablas activas (excluyendo metas_ahorro)
 * 
 * Uso: php export_database.php > database_export.sql
 * O desde navegador: http://localhost/export_database.php
 */

require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');

// Tablas a exportar (excluyendo metas_ahorro que no se usa)
$tables_to_export = [
    'usuarios',
    'cuentas_bancarias',
    'categorias',
    'transacciones',
    'transferencias',
    'presupuestos',
    'configuraciones'
];

echo "-- ============================================================================\n";
echo "-- EXPORTACIÓN DE BASE DE DATOS - Sistema FIME\n";
echo "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "-- ============================================================================\n\n";

try {
    if (isset($link->pdo)) {
        $pdo = $link->pdo;
        $db_type = isset($link->type) ? $link->type : 'unknown';
        
        echo "-- Tipo de base de datos: " . $db_type . "\n\n";
        
        // Para cada tabla, exportar estructura y datos
        foreach ($tables_to_export as $table) {
            echo "-- ============================================================================\n";
            echo "-- TABLA: $table\n";
            echo "-- ============================================================================\n\n";
            
            // Verificar si la tabla existe
            if ($db_type === 'postgresql') {
                $check_sql = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?)";
            } else {
                $check_sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?";
            }
            
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$table]);
            $exists = $db_type === 'postgresql' ? $check_stmt->fetchColumn() : ($check_stmt->fetchColumn() > 0);
            
            if (!$exists) {
                echo "-- Tabla $table no existe, omitiendo...\n\n";
                continue;
            }
            
                    // Obtener estructura de la tabla
                    if ($db_type === 'postgresql') {
                        // PostgreSQL: usar pg_dump sería mejor, pero para simplicidad solo exportamos datos
                        echo "-- Estructura de tabla $table\n";
                        echo "-- NOTA: La estructura debe crearse primero usando database_completo_postgresql.sql\n";
                        echo "-- Este script solo exporta los DATOS\n\n";
                    } else {
                        // MySQL: obtener CREATE TABLE
                        $create_stmt = $pdo->query("SHOW CREATE TABLE `$table`");
                        $create_row = $create_stmt->fetch(PDO::FETCH_ASSOC);
                        if ($create_row) {
                            echo $create_row['Create Table'] . ";\n\n";
                        }
                    }
            
            // Exportar datos
            $data_stmt = $pdo->query("SELECT * FROM \"$table\"");
            $rows = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                echo "-- Tabla $table está vacía\n\n";
                continue;
            }
            
            // Obtener nombres de columnas
            $columns = array_keys($rows[0]);
            $columns_str = '"' . implode('", "', $columns) . '"';
            
            echo "-- Datos de $table (" . count($rows) . " registros)\n";
            echo "INSERT INTO \"$table\" ($columns_str) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $row_values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $row_values[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $row_values[] = $value;
                    } else {
                        // Escapar comillas simples
                        $escaped = str_replace("'", "''", $value);
                        $row_values[] = "'$escaped'";
                    }
                }
                $values[] = '(' . implode(', ', $row_values) . ')';
            }
            
            // Imprimir valores en lotes para mejor legibilidad
            $batch_size = 50;
            for ($i = 0; $i < count($values); $i += $batch_size) {
                $batch = array_slice($values, $i, $batch_size);
                $comma = ($i + $batch_size < count($values)) ? ',' : ';';
                echo implode(",\n", $batch) . $comma . "\n";
            }
            
            echo "\n";
        }
        
        echo "-- ============================================================================\n";
        echo "-- FIN DE EXPORTACIÓN\n";
        echo "-- ============================================================================\n";
        
    } else {
        die("ERROR: No se pudo conectar a la base de datos.\n");
    }
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}

?>

