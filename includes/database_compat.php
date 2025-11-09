<?php
/**
 * Database compatibility functions
 * Provides mysqli-like functions using PDO when using PDO connections
 * This file is loaded when using PDO (PostgreSQL, SQLite, or MySQL via PDO)
 */

global $link;

// Only define compatibility functions if we're using PDO
if (isset($link) && isset($link->pdo)) {
    
    // Store original PDO connection
    global $pdo_connection;
    $pdo_connection = $link->pdo;
    
    // Create mysqli-like functions using PDO
    // When using PDO with stdClass $link, we MUST use these compatibility functions
    // The native mysqli functions won't work with stdClass, so we define our own
    
    // When using PDO (link->pdo exists), we MUST define compatibility functions
    // The native mysqli functions won't work with stdClass $link
    // 
    // IMPORTANT: When using PDO, these functions MUST be defined, even if mysqli extension is loaded
    // If mysqli is loaded, the native mysqli_prepare will fail with stdClass, so we need our version
    
    // Define mysqli_prepare - When using PDO, we MUST have this function
    // Since we're using PDO (link->pdo exists), the native mysqli_prepare won't work with stdClass $link
    // 
    // CRITICAL: When using PDO, we need to define this function even if mysqli extension is loaded
    // If mysqli is loaded, we can't redefine, so we need a workaround
    
    $mysqli_loaded = function_exists('mysqli_prepare');
    
    if (!$mysqli_loaded) {
        // mysqli not loaded - define our function normally
        function mysqli_prepare($link, $query) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                return $pdo_connection->prepare($query);
            }
            return false;
        }
    } else {
        // mysqli is loaded - we can't redefine mysqli_prepare
        // Solution: Create wrapper functions with different names that check link type
        // Then we'll need to modify code to use these wrappers when PDO is detected
        // OR: Use a global flag to route calls correctly
        
        // Create a wrapper function that checks link type at runtime
        // This will be used by code that detects PDO connections
        if (!function_exists('_pdo_compat_mysqli_prepare')) {
            function _pdo_compat_mysqli_prepare($link, $query) {
                global $pdo_connection;
                // If link has pdo property (PDO connection), use PDO
                if (isset($link->pdo)) {
                    return $pdo_connection->prepare($query);
                }
                // Otherwise, try native mysqli (shouldn't happen when using PDO)
                if ($link instanceof mysqli) {
                    return mysqli_prepare($link, $query);
                }
                return false;
            }
        }
        
        // Since we can't override mysqli_prepare, we need to intercept calls
        // We'll use output buffering and function name aliasing, OR
        // we'll modify the calling code to check link type first
        
        // Better solution: Create a helper that routes correctly based on link type
        // The code should check if link->pdo exists before calling mysqli functions
    }
    
    if (!function_exists('mysqli_stmt_bind_param')) {
        function mysqli_stmt_bind_param($stmt, $types, ...$params) {
            if ($stmt instanceof PDOStatement) {
                $param_count = count($params);
                for ($i = 0; $i < $param_count; $i++) {
                    $stmt->bindValue($i + 1, $params[$i]);
                }
                return true;
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_stmt_execute')) {
        function mysqli_stmt_execute($stmt) {
            if ($stmt instanceof PDOStatement) {
                return $stmt->execute();
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_stmt_get_result')) {
        function mysqli_stmt_get_result($stmt) {
            if ($stmt instanceof PDOStatement) {
                // Execute if not already executed
                try {
                    if ($stmt->errorCode() == '00000' || $stmt->errorCode() == '') {
                        $stmt->execute();
                    }
                } catch (Exception $e) {
                    // Already executed or error
                }
                // Store the result in a way that mysqli_num_rows can work with it
                $result = new stdClass();
                $result->stmt = $stmt;
                $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result->current_row = 0;
                return $result;
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_fetch_all')) {
        function mysqli_fetch_all($result, $result_type = MYSQLI_ASSOC) {
            if (isset($result->rows)) {
                return $result->rows;
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_fetch_assoc')) {
        function mysqli_fetch_assoc($result) {
            if (isset($result->rows) && isset($result->current_row)) {
                if ($result->current_row < count($result->rows)) {
                    $row = $result->rows[$result->current_row];
                    $result->current_row++;
                    return $row;
                }
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_num_rows')) {
        function mysqli_num_rows($result) {
            if (isset($result->rows)) {
                return count($result->rows);
            }
            return 0;
        }
    }
    
    if (!function_exists('mysqli_stmt_close')) {
        function mysqli_stmt_close($stmt) {
            if ($stmt instanceof PDOStatement) {
                $stmt->closeCursor();
                return true;
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_insert_id')) {
        function mysqli_insert_id($link) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                return $pdo_connection->lastInsertId();
            }
            return 0;
        }
    }
    
    if (!function_exists('mysqli_error')) {
        function mysqli_error($link) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                $error = $pdo_connection->errorInfo();
                return isset($error[2]) ? $error[2] : '';
            }
            return '';
        }
    }
    
    if (!function_exists('mysqli_begin_transaction')) {
        function mysqli_begin_transaction($link) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                return $pdo_connection->beginTransaction();
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_commit')) {
        function mysqli_commit($link) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                return $pdo_connection->commit();
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_rollback')) {
        function mysqli_rollback($link) {
            global $pdo_connection;
            if (isset($link->pdo)) {
                return $pdo_connection->rollBack();
            }
            return false;
        }
    }
    
    if (!function_exists('mysqli_query')) {
        function mysqli_query($link, $query) {
            // For compatibility, execute the query and return an object with rows
            if (isset($link->pdo)) {
                try {
                    $stmt = $link->pdo->query($query);
                    if ($stmt instanceof PDOStatement) {
                        $result = new stdClass();
                        $result->stmt = $stmt;
                        $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $result->current_row = 0;
                        return $result;
                    }
                } catch (Exception $e) {
                    return false;
                }
            }
            return false;
        }
    }

    if (!function_exists('mysqli_real_escape_string')) {
        function mysqli_real_escape_string($link, $string) {
            if (isset($link->pdo)) {
                // PDO::quote adds surrounding quotes, strip them to mimic mysqli_real_escape_string
                $q = $link->pdo->quote($string);
                if ($q !== false && strlen($q) >= 2 && $q[0] === "'" && substr($q, -1) === "'") {
                    return substr($q, 1, -1);
                }
                return $q !== false ? $q : $string;
            }
            // Fallback: simple addslashes
            return addslashes($string);
        }
    }
    
    // Constants for compatibility
    if (!defined('MYSQLI_ASSOC')) {
        define('MYSQLI_ASSOC', 1);
    }
    if (!defined('MYSQLI_NUM')) {
        define('MYSQLI_NUM', 2);
    }
}

?>
