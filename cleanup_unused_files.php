<?php
/**
 * Script para eliminar archivos no utilizados del proyecto
 * 
 * Uso: php cleanup_unused_files.php
 * O desde navegador: http://localhost/cleanup_unused_files.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Limpiar Archivos No Utilizados</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 900px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #c82333; }
        ul { list-style-type: none; padding-left: 0; }
        li { padding: 5px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpiar Archivos No Utilizados</h1>
        <p class="warning">⚠️ ADVERTENCIA: Este script eliminará archivos y carpetas que no se utilizan en el proyecto.</p>
        
        <?php
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $deleted = [];
            $errors = [];
            $base_dir = __DIR__;
            
            echo "<h2>Iniciando limpieza...</h2>";
            
            // Archivos y carpetas a eliminar
            $items_to_delete = [
                // Carpeta completa trash2
                'trash2',
                
                // Archivos de autenticación no utilizados (solo se usa -basic.php)
                'auth-lockscreen-basic.php',
                'auth-pass-reset-cover.php',
                'auth-signin-cover.php',
                'auth-signup-cover.php',
                
                // Páginas de ejemplo del template
                'pages-faqs.php',
                'pages-gallery.php',
                'pages-pricing.php',
                'pages-profile-settings.php',
                'pages-profile.php',
                'pages-search-results.php',
                'pages-team.php',
                'pages-timeline.php',
                
                // Landing page de ejemplo
                'landing.php',
                
                // Archivos de documentación de deploy (ya están en README)
                'DEPLOY-RENDER-SIN-PHP-OPTION.md',
                'DEPLOY-RENDER.md',
                'INSTRUCCIONES-RENDER-POSTGRESQL.md',
                'QUICK-START-RENDER.md',
                
                // Dockerfile si no se usa
                'Dockerfile',
                
                // Scripts de debug no necesarios en producción
                'scripts/debug_register.php',
                'test_db_connection.php',
                
                // Archivos SQL de ejemplo (mantener solo los necesarios)
                'database_schema.sql', // SQLite, no se usa en producción
            ];
            
            foreach ($items_to_delete as $item) {
                $path = $base_dir . '/' . $item;
                
                if (file_exists($path)) {
                    try {
                        if (is_dir($path)) {
                            // Eliminar directorio recursivamente
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::CHILD_FIRST
                            );
                            
                            foreach ($iterator as $file) {
                                if ($file->isDir()) {
                                    rmdir($file->getRealPath());
                                } else {
                                    unlink($file->getRealPath());
                                }
                            }
                            rmdir($path);
                            $deleted[] = "📁 $item (directorio)";
                        } else {
                            unlink($path);
                            $deleted[] = "📄 $item";
                        }
                    } catch (Exception $e) {
                        $errors[] = "❌ Error eliminando $item: " . $e->getMessage();
                    }
                } else {
                    $deleted[] = "⏭️ $item (no existe, omitido)";
                }
            }
            
            echo "<hr>";
            echo "<h2 class='success'>✅ Limpieza completada!</h2>";
            
            if (!empty($deleted)) {
                echo "<h3>Archivos/Carpetas procesados:</h3>";
                echo "<ul>";
                foreach ($deleted as $item) {
                    echo "<li>$item</li>";
                }
                echo "</ul>";
            }
            
            if (!empty($errors)) {
                echo "<h3 class='error'>Errores:</h3>";
                echo "<ul>";
                foreach ($errors as $error) {
                    echo "<li class='error'>$error</li>";
                }
                echo "</ul>";
            }
            
            echo "<p><strong>Total procesado:</strong> " . count($deleted) . " items</p>";
            echo "<p><a href='dashboard-gastos.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>Ir al Dashboard</a></p>";
            
        } else {
            // Mostrar lista de archivos a eliminar
            ?>
            <form method="POST" onsubmit="return confirm('¿Estás SEGURO de que quieres eliminar estos archivos? Esta acción NO se puede deshacer.');">
                <p>Este script eliminará los siguientes archivos y carpetas:</p>
                
                <h3>📁 Carpetas completas:</h3>
                <ul>
                    <li><strong>trash2/</strong> - Carpeta con archivos de prueba y versiones antiguas</li>
                </ul>
                
                <h3>📄 Archivos individuales:</h3>
                <ul>
                    <li><strong>auth-lockscreen-basic.php</strong> - No utilizado</li>
                    <li><strong>auth-pass-reset-cover.php</strong> - Variante no utilizada</li>
                    <li><strong>auth-signin-cover.php</strong> - Variante no utilizada</li>
                    <li><strong>auth-signup-cover.php</strong> - Variante no utilizada</li>
                    <li><strong>pages-*.php</strong> - Páginas de ejemplo del template (8 archivos)</li>
                    <li><strong>landing.php</strong> - Página de ejemplo</li>
                    <li><strong>DEPLOY-*.md</strong> - Documentación de deploy (4 archivos)</li>
                    <li><strong>Dockerfile</strong> - Si no se usa Docker</li>
                    <li><strong>scripts/debug_register.php</strong> - Script de debug</li>
                    <li><strong>test_db_connection.php</strong> - Script de prueba</li>
                    <li><strong>database_schema.sql</strong> - Schema SQLite (no usado en producción)</li>
                </ul>
                
                <p class="info">
                    <strong>Archivos que se MANTIENEN:</strong><br>
                    - Todos los archivos de gestión (cuentas-*, transacciones-*, categorias-*, presupuestos-*)<br>
                    - Archivos de autenticación básicos (auth-signin-basic.php, auth-signup-basic.php, auth-pass-reset-basic.php)<br>
                    - Dashboard y reportes<br>
                    - Layouts y includes<br>
                    - Scripts de base de datos (export_database.php, import_database.php, clean-database.php, etc.)<br>
                    - Archivos SQL de producción (database_completo_postgresql.sql, database_completo_mariaDB.sql)
                </p>
                
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="danger">⚠️ CONFIRMAR ELIMINACIÓN</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>

