<?php
/**
 * Populate Database with Sample Data
 * Script to fill the database with realistic sample data for testing
 */

require_once 'layouts/config.php';

try {
    echo "🔄 Populating database with sample data...\n\n";
    
    // Get admin user ID
    $sql = "SELECT id FROM usuarios WHERE email = 'admin@fime.com'";
    $stmt = $link->pdo->query($sql);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ Admin user not found!\n";
        exit;
    }
    
    $user_id = $admin['id'];
    echo "✅ Found admin user (ID: $user_id)\n";
    
    // Get category IDs
    $categories = [];
    $sql = "SELECT id, nombre, tipo FROM categorias WHERE es_predefinida = 1";
    $stmt = $link->pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[$row['tipo']][$row['nombre']] = $row['id'];
    }
    
    echo "✅ Found " . count($categories['ingreso']) . " income and " . count($categories['gasto']) . " expense categories\n";
    
    // Get account IDs
    $sql = "SELECT id, nombre FROM cuentas_bancarias WHERE usuario_id = $user_id";
    $stmt = $link->pdo->query($sql);
    $accounts = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accounts[$row['id']] = $row['nombre'];
    }
    
    echo "✅ Found " . count($accounts) . " bank accounts\n";
    
    if (count($accounts) == 0) {
        echo "❌ No bank accounts found. Creating sample accounts...\n";
        
        // Create sample accounts
        $accounts_data = [
            ['Cuenta Principal BBVA', 'corriente', 'BBVA', '1234567890', 5000.00],
            ['Cuenta de Ahorros', 'ahorros', 'Santander', '0987654321', 10000.00],
            ['Tarjeta de Crédito', 'credito', 'HSBC', '555544443333', -2000.00]
        ];
        
        $stmt = $link->pdo->prepare("INSERT INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($accounts_data as $account) {
            $stmt->execute([$user_id, $account[0], $account[1], $account[2], $account[3], $account[4]]);
            $account_id = $link->pdo->lastInsertId();
            $accounts[$account_id] = $account[0];
        }
        
        echo "✅ Created " . count($accounts_data) . " sample accounts\n";
    }
    
    // Sample transactions (last 3 months)
    echo "\n📊 Adding sample transactions...\n";
    
    $transactions = [
        // Income transactions
        ['Salario mensual', 5000.00, 'ingreso', '2025-01-15', 1, $categories['ingreso']['Salario']],
        ['Salario mensual', 5000.00, 'ingreso', '2025-02-15', 1, $categories['ingreso']['Salario']],
        ['Salario mensual', 5000.00, 'ingreso', '2025-03-15', 1, $categories['ingreso']['Salario']],
        ['Pago freelance', 1500.00, 'ingreso', '2025-01-20', 1, $categories['ingreso']['Freelance']],
        ['Rendimiento inversiones', 800.00, 'ingreso', '2025-02-10', 2, $categories['ingreso']['Inversiones']],
        
        // Expense transactions
        ['Renta departamento', 3500.00, 'gasto', '2025-01-01', 1, $categories['gasto']['Vivienda']],
        ['Renta departamento', 3500.00, 'gasto', '2025-02-01', 1, $categories['gasto']['Vivienda']],
        ['Renta departamento', 3500.00, 'gasto', '2025-03-01', 1, $categories['gasto']['Vivienda']],
        ['Supermercado', 1500.00, 'gasto', '2025-01-05', 1, $categories['gasto']['Alimentación']],
        ['Supermercado', 1800.00, 'gasto', '2025-02-05', 1, $categories['gasto']['Alimentación']],
        ['Supermercado', 1600.00, 'gasto', '2025-03-05', 1, $categories['gasto']['Alimentación']],
        ['Combustible', 800.00, 'gasto', '2025-01-10', 1, $categories['gasto']['Transporte']],
        ['Combustible', 750.00, 'gasto', '2025-02-12', 1, $categories['gasto']['Transporte']],
        ['Combustible', 820.00, 'gasto', '2025-03-08', 1, $categories['gasto']['Transporte']],
        ['Cine', 250.00, 'gasto', '2025-01-15', 1, $categories['gasto']['Entretenimiento']],
        ['Netflix', 200.00, 'gasto', '2025-02-01', 1, $categories['gasto']['Entretenimiento']],
        ['Spotify', 150.00, 'gasto', '2025-03-01', 1, $categories['gasto']['Entretenimiento']],
        ['Consulta médica', 500.00, 'gasto', '2025-02-20', 1, $categories['gasto']['Salud']],
        ['Medicamentos', 300.00, 'gasto', '2025-02-25', 1, $categories['gasto']['Salud']],
        ['Luz', 350.00, 'gasto', '2025-01-10', 1, $categories['gasto']['Servicios']],
        ['Agua', 250.00, 'gasto', '2025-01-12', 1, $categories['gasto']['Servicios']],
        ['Internet', 400.00, 'gasto', '2025-02-01', 1, $categories['gasto']['Servicios']],
        ['Ropa nueva', 1200.00, 'gasto', '2025-01-25', 1, $categories['gasto']['Ropa']],
        ['Libros de estudio', 800.00, 'gasto', '2025-02-05', 1, $categories['gasto']['Educación']],
    ];
    
    $stmt = $link->pdo->prepare("INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $added = 0;
    foreach ($transactions as $transaction) {
        $stmt->execute([
            $user_id,
            $transaction[4], // account_id
            $transaction[5], // category_id
            $transaction[0], // description
            $transaction[1], // amount
            $transaction[2], // type
            $transaction[3]  // date
        ]);
        $added++;
    }
    
    echo "✅ Added $added sample transactions\n";
    
    // Update account balances based on transactions
    echo "\n💰 Updating account balances...\n";
    
    $sql = "SELECT cuenta_id, SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END) as net_balance
            FROM transacciones WHERE usuario_id = $user_id
            GROUP BY cuenta_id";
    $stmt = $link->pdo->query($sql);
    
    $balances = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $balances[$row['cuenta_id']] = $row['net_balance'];
    }
    
    foreach ($balances as $account_id => $net_balance) {
        $update_sql = "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?";
        $update_stmt = $link->pdo->prepare($update_sql);
        $update_stmt->execute([$net_balance, $account_id]);
    }
    
    echo "✅ Account balances updated\n";
    
    // Add sample budgets
    echo "\n📊 Adding sample budgets...\n";
    
    if (isset($categories['gasto']['Alimentación'])) {
        $stmt = $link->pdo->prepare("INSERT INTO presupuestos (usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?, ?)");
        
        $budgets = [
            ['Presupuesto Alimentación', 2000.00, $categories['gasto']['Alimentación'], '2025-03-01', '2025-03-31'],
            ['Presupuesto Transporte', 1000.00, $categories['gasto']['Transporte'], '2025-03-01', '2025-03-31'],
            ['Presupuesto Entretenimiento', 500.00, $categories['gasto']['Entretenimiento'], '2025-03-01', '2025-03-31'],
        ];
        
        foreach ($budgets as $budget) {
            $stmt->execute([
                $user_id,
                $budget[0],
                $budget[1],
                $budget[2],
                $budget[3],
                $budget[4]
            ]);
        }
        
        echo "✅ Added " . count($budgets) . " sample budgets\n";
    }
    
    // Add savings goals
    echo "\n🎯 Adding savings goals...\n";
    
    $stmt = $link->pdo->prepare("INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $goals = [
        ['Vacaciones Cancún', 5000.00, 2800.00, '2025-01-01', '2025-06-01', 'Ahorrar para vacaciones de verano'],
        ['Laptop nueva', 8000.00, 5200.00, '2025-01-01', '2025-08-01', 'Comprar una MacBook para la universidad'],
        ['Fondo de emergencia', 10000.00, 1500.00, '2025-03-01', '2025-12-31', 'Fondo de emergencia personal']
    ];
    
    foreach ($goals as $goal) {
        $stmt->execute([
            $user_id,
            $goal[0],
            $goal[1],
            $goal[2],
            $goal[3],
            $goal[4],
            $goal[5]
        ]);
    }
    
    echo "✅ Added " . count($goals) . " savings goals\n";
    
    echo "\n🎉 Database populated successfully!\n";
    echo "\n📊 Summary:\n";
    echo "   - Transactions: $added\n";
    echo "   - Bank accounts: " . count($accounts) . "\n";
    echo "   - Budgets: " . (isset($budgets) ? count($budgets) : 0) . "\n";
    echo "   - Savings goals: " . count($goals) . "\n";
    echo "\n🌐 You can now access: http://localhost:8000\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>

