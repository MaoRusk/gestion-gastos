<?php
/**
 * Database compatibility functions
 * Provides mysqli-like functions using PDO when mysqli is not available
 */

if (!extension_loaded('mysqli') && extension_loaded('pdo_mysql')) {
    
    // Store original PDO connection
    global $pdo_connection;
    $pdo_connection = $link->pdo;
    
    // Create mysqli-like functions using PDO
    function mysqli_prepare($link, $query) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            return $pdo_connection->prepare($query);
        }
        return false;
    }
    
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
    
    function mysqli_stmt_execute($stmt) {
        if ($stmt instanceof PDOStatement) {
            return $stmt->execute();
        }
        return false;
    }
    
    function mysqli_stmt_get_result($stmt) {
        if ($stmt instanceof PDOStatement) {
            $stmt->execute();
            // Store the result in a way that mysqli_num_rows can work with it
            $result = new stdClass();
            $result->stmt = $stmt;
            $result->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result->current_row = 0;
            return $result;
        }
        return false;
    }
    
    function mysqli_fetch_all($result, $result_type = MYSQLI_ASSOC) {
        if (isset($result->rows)) {
            return $result->rows;
        }
        return false;
    }
    
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
    
    function mysqli_num_rows($result) {
        if (isset($result->rows)) {
            return count($result->rows);
        }
        return 0;
    }
    
    function mysqli_stmt_close($stmt) {
        if ($stmt instanceof PDOStatement) {
            $stmt->closeCursor();
            return true;
        }
        return false;
    }
    
    function mysqli_insert_id($link) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            return $pdo_connection->lastInsertId();
        }
        return 0;
    }
    
    function mysqli_error($link) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            $error = $pdo_connection->errorInfo();
            return $error[2];
        }
        return '';
    }
    
    function mysqli_begin_transaction($link) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            return $pdo_connection->beginTransaction();
        }
        return false;
    }
    
    function mysqli_commit($link) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            return $pdo_connection->commit();
        }
        return false;
    }
    
    function mysqli_rollback($link) {
        global $pdo_connection;
        if (isset($link->pdo)) {
            return $pdo_connection->rollBack();
        }
        return false;
    }
    
    function mysqli_query($link, $query) {
        if (isset($link->pdo)) {
            return $link->pdo->query($query);
        }
        return false;
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
