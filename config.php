<?php
/**
 * Configuration & Database Connection
 * Enhanced Delivery Pro System v2.0
 */

// Force UTF-8 encoding from the start
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

session_start();
date_default_timezone_set('Africa/Nouakchott');

// ==========================================
// DATABASE CONFIGURATION
// ==========================================
$db_config = [
    'host' => 'localhost',
    'name' => 'barqvkxs_barq_delivery',
    'user' => 'barqvkxs_barqvkxs_barq_delivery',
    'pass' => 'barqvkxs_barq_delivery',
    'charset' => 'utf8mb4'
];

// ==========================================
// APPLICATION SETTINGS
// ==========================================
$country_code = "222";                    // Mauritania country code
$whatsapp_number = "22241312931";
$help_email = "help@barqmr.com";
$help_phone = "+222 41 31 29 31";
$points_cost_per_order = 20;
$driver_max_active_orders = 2;        // Max concurrent orders per driver
$order_expiry_hours = 3;              // Orders expire after 3 hours without acceptance
$uploads_dir = __DIR__ . '/uploads';  // Profile pictures directory

// ==========================================
// NOUAKCHOTT ZONES (Moughataas)
// ==========================================
$zones = [
    'Tevragh Zeina' => 'تفرغ زينة',
    'Ksar' => 'لكصر',
    'Sebkha' => 'السبخة',
    'Teyarett' => 'تيارت',
    'Dar Naïm' => 'دار النعيم',
    'Toujounine' => 'توجنين',
    'Arafat' => 'عرفات',
    'El Mina' => 'الميناء',
    'Riyad' => 'الرياض'
];

// ==========================================
// ZONE PRICE MATRIX (MRU)
// ==========================================
$zone_prices = [
    // --- تفرغ زينة (Tevragh Zeina) ---
    ['from' => 'Tevragh Zeina', 'to' => 'Tevragh Zeina', 'price' => 100],
    ['from' => 'Tevragh Zeina', 'to' => 'Ksar', 'price' => 100],
    ['from' => 'Tevragh Zeina', 'to' => 'Sebkha', 'price' => 100],
    ['from' => 'Tevragh Zeina', 'to' => 'El Mina', 'price' => 150],
    ['from' => 'Tevragh Zeina', 'to' => 'Arafat', 'price' => 150],
    ['from' => 'Tevragh Zeina', 'to' => 'Teyarett', 'price' => 150],
    ['from' => 'Tevragh Zeina', 'to' => 'Dar Naïm', 'price' => 200],
    ['from' => 'Tevragh Zeina', 'to' => 'Toujounine', 'price' => 200],
    ['from' => 'Tevragh Zeina', 'to' => 'Riyad', 'price' => 200],

    // --- لكصر (Ksar) ---
    ['from' => 'Ksar', 'to' => 'Ksar', 'price' => 100],
    ['from' => 'Ksar', 'to' => 'Tevragh Zeina', 'price' => 100],
    ['from' => 'Ksar', 'to' => 'Teyarett', 'price' => 100],
    ['from' => 'Ksar', 'to' => 'Dar Naïm', 'price' => 100],
    ['from' => 'Ksar', 'to' => 'Sebkha', 'price' => 100],
    ['from' => 'Ksar', 'to' => 'Toujounine', 'price' => 150],
    ['from' => 'Ksar', 'to' => 'Arafat', 'price' => 150],
    ['from' => 'Ksar', 'to' => 'El Mina', 'price' => 150],
    ['from' => 'Ksar', 'to' => 'Riyad', 'price' => 200],

    // --- السبخة (Sebkha) ---
    ['from' => 'Sebkha', 'to' => 'Sebkha', 'price' => 100],
    ['from' => 'Sebkha', 'to' => 'El Mina', 'price' => 100],
    ['from' => 'Sebkha', 'to' => 'Tevragh Zeina', 'price' => 100],
    ['from' => 'Sebkha', 'to' => 'Ksar', 'price' => 100],
    ['from' => 'Sebkha', 'to' => 'Arafat', 'price' => 150],
    ['from' => 'Sebkha', 'to' => 'Riyad', 'price' => 150],
    ['from' => 'Sebkha', 'to' => 'Teyarett', 'price' => 200],
    ['from' => 'Sebkha', 'to' => 'Dar Naïm', 'price' => 200],
    ['from' => 'Sebkha', 'to' => 'Toujounine', 'price' => 200],

    // --- تيارت (Teyarett) ---
    ['from' => 'Teyarett', 'to' => 'Teyarett', 'price' => 100],
    ['from' => 'Teyarett', 'to' => 'Dar Naïm', 'price' => 100],
    ['from' => 'Teyarett', 'to' => 'Ksar', 'price' => 100],
    ['from' => 'Teyarett', 'to' => 'Tevragh Zeina', 'price' => 150],
    ['from' => 'Teyarett', 'to' => 'Toujounine', 'price' => 150],
    ['from' => 'Teyarett', 'to' => 'Sebkha', 'price' => 200],
    ['from' => 'Teyarett', 'to' => 'Arafat', 'price' => 200],
    ['from' => 'Teyarett', 'to' => 'El Mina', 'price' => 200],
    ['from' => 'Teyarett', 'to' => 'Riyad', 'price' => 200],

    // --- دار النعيم (Dar Naïm) ---
    ['from' => 'Dar Naïm', 'to' => 'Dar Naïm', 'price' => 100],
    ['from' => 'Dar Naïm', 'to' => 'Teyarett', 'price' => 100],
    ['from' => 'Dar Naïm', 'to' => 'Toujounine', 'price' => 100],
    ['from' => 'Dar Naïm', 'to' => 'Ksar', 'price' => 100],
    ['from' => 'Dar Naïm', 'to' => 'Arafat', 'price' => 150],
    ['from' => 'Dar Naïm', 'to' => 'Tevragh Zeina', 'price' => 200],
    ['from' => 'Dar Naïm', 'to' => 'Sebkha', 'price' => 200],
    ['from' => 'Dar Naïm', 'to' => 'El Mina', 'price' => 200],
    ['from' => 'Dar Naïm', 'to' => 'Riyad', 'price' => 200],

    // --- توجنين (Toujounine) ---
    ['from' => 'Toujounine', 'to' => 'Toujounine', 'price' => 100],
    ['from' => 'Toujounine', 'to' => 'Dar Naïm', 'price' => 100],
    ['from' => 'Toujounine', 'to' => 'Arafat', 'price' => 100],
    ['from' => 'Toujounine', 'to' => 'Ksar', 'price' => 150],
    ['from' => 'Toujounine', 'to' => 'Teyarett', 'price' => 150],
    ['from' => 'Toujounine', 'to' => 'Riyad', 'price' => 150],
    ['from' => 'Toujounine', 'to' => 'Tevragh Zeina', 'price' => 200],
    ['from' => 'Toujounine', 'to' => 'Sebkha', 'price' => 200],
    ['from' => 'Toujounine', 'to' => 'El Mina', 'price' => 200],

    // --- عرفات (Arafat) ---
    ['from' => 'Arafat', 'to' => 'Arafat', 'price' => 100],
    ['from' => 'Arafat', 'to' => 'Toujounine', 'price' => 100],
    ['from' => 'Arafat', 'to' => 'El Mina', 'price' => 100],
    ['from' => 'Arafat', 'to' => 'Riyad', 'price' => 100],
    ['from' => 'Arafat', 'to' => 'Ksar', 'price' => 150],
    ['from' => 'Arafat', 'to' => 'Tevragh Zeina', 'price' => 150],
    ['from' => 'Arafat', 'to' => 'Sebkha', 'price' => 150],
    ['from' => 'Arafat', 'to' => 'Dar Naïm', 'price' => 150],
    ['from' => 'Arafat', 'to' => 'Teyarett', 'price' => 200],

    // --- الميناء (El Mina) ---
    ['from' => 'El Mina', 'to' => 'El Mina', 'price' => 100],
    ['from' => 'El Mina', 'to' => 'Sebkha', 'price' => 100],
    ['from' => 'El Mina', 'to' => 'Arafat', 'price' => 100],
    ['from' => 'El Mina', 'to' => 'Riyad', 'price' => 100],
    ['from' => 'El Mina', 'to' => 'Tevragh Zeina', 'price' => 150],
    ['from' => 'El Mina', 'to' => 'Ksar', 'price' => 150],
    ['from' => 'El Mina', 'to' => 'Toujounine', 'price' => 200],
    ['from' => 'El Mina', 'to' => 'Dar Naïm', 'price' => 200],
    ['from' => 'El Mina', 'to' => 'Teyarett', 'price' => 200],

    // --- الرياض (Riyad) ---
    ['from' => 'Riyad', 'to' => 'Riyad', 'price' => 100],
    ['from' => 'Riyad', 'to' => 'Arafat', 'price' => 100],
    ['from' => 'Riyad', 'to' => 'El Mina', 'price' => 100],
    ['from' => 'Riyad', 'to' => 'Sebkha', 'price' => 150],
    ['from' => 'Riyad', 'to' => 'Toujounine', 'price' => 150],
    ['from' => 'Riyad', 'to' => 'Tevragh Zeina', 'price' => 200],
    ['from' => 'Riyad', 'to' => 'Ksar', 'price' => 200],
    ['from' => 'Riyad', 'to' => 'Dar Naïm', 'price' => 200],
    ['from' => 'Riyad', 'to' => 'Teyarett', 'price' => 200]
];

/**
 * Get price for zone-to-zone delivery
 */
function getZonePrice($from, $to) {
    global $zone_prices;
    foreach ($zone_prices as $route) {
        if ($route['from'] === $from && $route['to'] === $to) {
            return $route['price'];
        }
    }
    return 150; // Default price if not found
}

// ==========================================
// DATABASE CONNECTION
// ==========================================
try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $conn = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);

    // Additional UTF-8 enforcement
    $conn->exec("SET NAMES utf8mb4");
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->exec("SET character_set_connection=utf8mb4");
    $conn->exec("SET character_set_client=utf8mb4");
    $conn->exec("SET character_set_results=utf8mb4");

    // Set MySQL timezone to match PHP timezone (Africa/Nouakchott = GMT+0)
    // This ensures timestamps are stored and retrieved consistently
    $conn->exec("SET time_zone = '+00:00'");

    // ==========================================
    // DATABASE SCHEMA v2.0
    // ==========================================

    // 1. Create Enhanced Users Table
    $conn->exec("CREATE TABLE IF NOT EXISTS users1 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial_no VARCHAR(15) UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','driver','customer') NOT NULL,
        points INT DEFAULT 0,
        status ENUM('active','banned') DEFAULT 'active',
        full_name VARCHAR(100) DEFAULT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        phone_verified TINYINT(1) DEFAULT 0,
        email VARCHAR(100) DEFAULT NULL,
        address VARCHAR(255) DEFAULT NULL,
        avatar_url VARCHAR(255) DEFAULT NULL,
        rating DECIMAL(3,2) DEFAULT 5.00,
        total_orders INT DEFAULT 0,
        total_earnings INT DEFAULT 0,
        is_online TINYINT(1) DEFAULT 0,
        is_verified TINYINT(1) DEFAULT 0,
        working_zones TEXT DEFAULT NULL COMMENT 'Comma-separated list of zones driver works in',
        last_lat DECIMAL(10,8) DEFAULT NULL,
        last_lng DECIMAL(11,8) DEFAULT NULL,
        location_updated_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_role_status (role, status),
        INDEX idx_serial (serial_no),
        INDEX idx_online (is_online, role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Create Enhanced Orders Table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders1 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT DEFAULT NULL,
        customer_name VARCHAR(50) NOT NULL,
        details TEXT NOT NULL,
        address VARCHAR(255) NOT NULL,
        client_phone VARCHAR(20) DEFAULT NULL,
        pickup_zone VARCHAR(50) DEFAULT NULL,
        dropoff_zone VARCHAR(50) DEFAULT NULL,
        delivery_price INT DEFAULT 0,
        status ENUM('pending','accepted','picked_up','delivered','cancelled') DEFAULT 'pending',
        driver_id INT DEFAULT NULL,
        delivery_code VARCHAR(10) DEFAULT NULL,
        points_cost INT DEFAULT 0,
        accepted_at TIMESTAMP NULL,
        picked_at TIMESTAMP NULL,
        delivered_at TIMESTAMP NULL,
        cancelled_at TIMESTAMP NULL,
        cancel_reason TEXT DEFAULT NULL,
        promo_code VARCHAR(50) DEFAULT NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_driver (driver_id),
        INDEX idx_client (client_id),
        INDEX idx_customer (customer_name),
        INDEX idx_created (created_at),
        INDEX idx_pickup_zone (pickup_zone),
        INDEX idx_dropoff_zone (dropoff_zone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Create Serial Number Counters Table
    $conn->exec("CREATE TABLE IF NOT EXISTS serial_counters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        prefix CHAR(2) NOT NULL,
        `year_month` CHAR(4) NOT NULL,
        current_count INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_prefix_month (prefix, `year_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Create Ratings Table
    $conn->exec("CREATE TABLE IF NOT EXISTS ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        rater_id INT NOT NULL,
        ratee_id INT NOT NULL,
        score TINYINT NOT NULL CHECK (score >= 1 AND score <= 5),
        comment TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ratee (ratee_id),
        INDEX idx_order (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Create Driver Tracking Table (for real-time location)
    $conn->exec("CREATE TABLE IF NOT EXISTS order_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        driver_id INT NOT NULL,
        latitude DECIMAL(10,8) NOT NULL,
        longitude DECIMAL(11,8) NOT NULL,
        accuracy FLOAT DEFAULT NULL,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order_time (order_id, recorded_at DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 6. Create Promo Codes Table
    $conn->exec("CREATE TABLE IF NOT EXISTS promo_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        discount_type ENUM('percentage', 'fixed') NOT NULL,
        discount_value DECIMAL(10,2) NOT NULL,
        max_uses INT DEFAULT NULL COMMENT 'NULL = unlimited',
        used_count INT DEFAULT 0,
        valid_from TIMESTAMP NULL,
        valid_until TIMESTAMP NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_code (code),
        INDEX idx_active (is_active),
        INDEX idx_valid (valid_from, valid_until)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 7. Create Promo Code Usage Tracking Table
    $conn->exec("CREATE TABLE IF NOT EXISTS promo_code_uses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_code_id INT NOT NULL,
        user_id INT NOT NULL,
        order_id INT NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL,
        used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_promo_code (promo_code_id),
        INDEX idx_user (user_id),
        INDEX idx_order (order_id),
        UNIQUE KEY unique_user_promo (user_id, promo_code_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 8. Create Recharge History Table
    $conn->exec("CREATE TABLE IF NOT EXISTS recharge_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT NOT NULL COMMENT 'Driver who received points',
        admin_id INT NOT NULL COMMENT 'Admin who performed recharge',
        amount INT NOT NULL COMMENT 'Points added',
        previous_balance INT NOT NULL COMMENT 'Balance before recharge',
        new_balance INT NOT NULL COMMENT 'Balance after recharge',
        recharge_type ENUM('single', 'bulk') DEFAULT 'single' COMMENT 'Type of recharge',
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_driver (driver_id),
        INDEX idx_admin (admin_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 9. Create Visitor Tracking Table
    $conn->exec("CREATE TABLE IF NOT EXISTS site_visitors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        page_url VARCHAR(500),
        referrer VARCHAR(500) DEFAULT NULL,
        user_id INT DEFAULT NULL,
        visit_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_date (visit_date),
        INDEX idx_ip_date (ip_address, visit_date),
        UNIQUE KEY unique_daily_visit (ip_address, visit_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ==========================================
    // SCHEMA MIGRATIONS (Add missing columns)
    // ==========================================
    $migrations = [
        'users1' => [
            'serial_no' => "ALTER TABLE users1 ADD COLUMN serial_no VARCHAR(15) UNIQUE AFTER id",
            'phone_verified' => "ALTER TABLE users1 ADD COLUMN phone_verified TINYINT(1) DEFAULT 0 AFTER phone",
            'avatar_url' => "ALTER TABLE users1 ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL AFTER address",
            'rating' => "ALTER TABLE users1 ADD COLUMN rating DECIMAL(3,2) DEFAULT 5.00 AFTER avatar_url",
            'total_orders' => "ALTER TABLE users1 ADD COLUMN total_orders INT DEFAULT 0 AFTER rating",
            'total_earnings' => "ALTER TABLE users1 ADD COLUMN total_earnings INT DEFAULT 0 AFTER total_orders",
            'is_online' => "ALTER TABLE users1 ADD COLUMN is_online TINYINT(1) DEFAULT 0 AFTER total_earnings",
            'last_lat' => "ALTER TABLE users1 ADD COLUMN last_lat DECIMAL(10,8) DEFAULT NULL AFTER is_online",
            'last_lng' => "ALTER TABLE users1 ADD COLUMN last_lng DECIMAL(11,8) DEFAULT NULL AFTER last_lat",
            'location_updated_at' => "ALTER TABLE users1 ADD COLUMN location_updated_at TIMESTAMP NULL AFTER last_lng",
            'is_verified' => "ALTER TABLE users1 ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_online",
            'working_zones' => "ALTER TABLE users1 ADD COLUMN working_zones TEXT DEFAULT NULL COMMENT 'Comma-separated list of zones driver works in' AFTER is_verified",
            'status' => "ALTER TABLE users1 ADD COLUMN status ENUM('active','banned') DEFAULT 'active' AFTER points",
            'full_name' => "ALTER TABLE users1 ADD COLUMN full_name VARCHAR(100) DEFAULT NULL AFTER status",
            'phone' => "ALTER TABLE users1 ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER full_name",
            'email' => "ALTER TABLE users1 ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER phone_verified",
            'address' => "ALTER TABLE users1 ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER email",
            'updated_at' => "ALTER TABLE users1 ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ],
        'orders1' => [
            'client_id' => "ALTER TABLE orders1 ADD COLUMN client_id INT DEFAULT NULL AFTER id",
            'pickup_zone' => "ALTER TABLE orders1 ADD COLUMN pickup_zone VARCHAR(50) DEFAULT NULL AFTER client_phone",
            'dropoff_zone' => "ALTER TABLE orders1 ADD COLUMN dropoff_zone VARCHAR(50) DEFAULT NULL AFTER pickup_zone",
            'delivery_price' => "ALTER TABLE orders1 ADD COLUMN delivery_price INT DEFAULT 0 AFTER dropoff_zone",
            'points_cost' => "ALTER TABLE orders1 ADD COLUMN points_cost INT DEFAULT 0 AFTER delivery_code",
            'accepted_at' => "ALTER TABLE orders1 ADD COLUMN accepted_at TIMESTAMP NULL AFTER points_cost",
            'picked_at' => "ALTER TABLE orders1 ADD COLUMN picked_at TIMESTAMP NULL AFTER accepted_at",
            'delivered_at' => "ALTER TABLE orders1 ADD COLUMN delivered_at TIMESTAMP NULL AFTER picked_at",
            'cancelled_at' => "ALTER TABLE orders1 ADD COLUMN cancelled_at TIMESTAMP NULL AFTER delivered_at",
            'cancel_reason' => "ALTER TABLE orders1 ADD COLUMN cancel_reason TEXT DEFAULT NULL AFTER cancelled_at",
            'updated_at' => "ALTER TABLE orders1 ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
            'promo_code' => "ALTER TABLE orders1 ADD COLUMN promo_code VARCHAR(50) DEFAULT NULL AFTER cancel_reason",
            'discount_amount' => "ALTER TABLE orders1 ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER promo_code"
        ]
    ];

    foreach ($migrations as $table => $columns) {
        foreach ($columns as $column => $alter_sql) {
            try {
                $check = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'")->rowCount();
                if ($check == 0) {
                    $conn->exec($alter_sql);
                }
            } catch (Exception $e) {
                // Column might already exist or syntax issue - continue silently
            }
        }
    }

    // Add 'picked_up' status if not exists
    try {
        $conn->exec("ALTER TABLE orders1 MODIFY COLUMN status ENUM('pending','accepted','picked_up','delivered','cancelled') DEFAULT 'pending'");
    } catch (Exception $e) {
        // Already has the correct enum - continue
    }

    // Create uploads directory if not exists
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    if (!is_dir($uploads_dir . '/avatars')) {
        mkdir($uploads_dir . '/avatars', 0755, true);
    }

    // ==========================================
    // DEFAULT USERS (with phone + password)
    // Mauritanian phone: 8 digits starting with 2, 3, or 4
    // ==========================================
    $check_users = $conn->query("SELECT count(*) FROM users1")->fetchColumn();
    if ($check_users == 0) {
        $hashed_password = password_hash('123', PASSWORD_DEFAULT);

        // Admin: phone 20000001, password 123
        $conn->prepare("INSERT INTO users1 (username, password, role, points, status, full_name, phone, phone_verified, is_verified) VALUES (?, ?, ?, ?, 'active', ?, ?, 1, 1)")
             ->execute(['admin', $hashed_password, 'admin', 0, 'Administrator', '20000001']);

        // Driver: phone 30000002, password 123 (verified)
        $conn->prepare("INSERT INTO users1 (username, password, role, points, status, full_name, phone, phone_verified, is_verified) VALUES (?, ?, ?, ?, 'active', ?, ?, 1, 1)")
             ->execute(['driver', $hashed_password, 'driver', 50, 'Demo Driver', '30000002']);

        // Client: phone 40000003, password 123
        $conn->prepare("INSERT INTO users1 (username, password, role, points, status, full_name, phone, phone_verified) VALUES (?, ?, ?, ?, 'active', ?, ?, 1)")
             ->execute(['client', $hashed_password, 'customer', 0, 'Demo Client', '40000003']);
    }

    // Migrate old plain-text passwords to hashed
    $users_to_migrate = $conn->query("SELECT id, password FROM users1 WHERE LENGTH(password) < 60");
    while ($user = $users_to_migrate->fetch()) {
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users1 SET password = ? WHERE id = ?")->execute([$hashed, $user['id']]);
    }

    // Generate serial numbers for existing users without one
    $users_without_serial = $conn->query("SELECT id, role, created_at FROM users1 WHERE serial_no IS NULL OR serial_no = ''");
    while ($user = $users_without_serial->fetch()) {
        $serial = generateSerialNumber($conn, $user['role'], $user['created_at']);
        $conn->prepare("UPDATE users1 SET serial_no = ? WHERE id = ?")->execute([$serial, $user['id']]);
    }

} catch(PDOException $e) {
    die("
    <div style='font-family:sans-serif; text-align:center; padding:50px; color:#721c24; background:#f8d7da;'>
        <h3>Database Connection Failed</h3>
        <p>Please check your config variables at the top of the file.</p>
        <small>Error: ".$e->getMessage()."</small>
    </div>");
}

// ==========================================
// SERIAL NUMBER GENERATOR
// ==========================================
function generateSerialNumber($conn, $role, $date = null) {
    $prefixes = [
        'admin' => 'AD',
        'driver' => 'DR',
        'customer' => 'CL'
    ];

    $prefix = $prefixes[$role] ?? 'US';
    $date = $date ? new DateTime($date) : new DateTime();
    $yearMonth = $date->format('ym'); // e.g., 2501 for January 2025

    // Lock and get/increment counter
    $conn->beginTransaction();
    try {
        // Try to get existing counter
        $stmt = $conn->prepare("SELECT current_count FROM serial_counters WHERE prefix = ? AND `year_month` = ? FOR UPDATE");
        $stmt->execute([$prefix, $yearMonth]);
        $row = $stmt->fetch();

        if ($row) {
            $newCount = $row['current_count'] + 1;
            $conn->prepare("UPDATE serial_counters SET current_count = ? WHERE prefix = ? AND `year_month` = ?")
                 ->execute([$newCount, $prefix, $yearMonth]);
        } else {
            $newCount = 1;
            $conn->prepare("INSERT INTO serial_counters (prefix, `year_month`, current_count) VALUES (?, ?, ?)")
                 ->execute([$prefix, $yearMonth, $newCount]);
        }

        $conn->commit();

        // Format: DR-2501-00001
        return sprintf("%s-%s-%05d", $prefix, $yearMonth, $newCount);

    } catch (Exception $e) {
        $conn->rollBack();
        // Fallback: generate random serial
        return sprintf("%s-%s-%05d", $prefix, $yearMonth, rand(10000, 99999));
    }
}
?>
