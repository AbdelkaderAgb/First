<?php
/**
 * API Endpoints for AJAX calls
 * Handles location tracking, order updates, and real-time notifications
 */

header('Content-Type: application/json; charset=utf-8');

// Include configuration (starts session)
require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Refresh user data from database to ensure we have the latest data (especially points)
$stmt = $conn->prepare("SELECT * FROM users1 WHERE id=?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch();

// Check if user was found
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Session expired']);
    exit();
}

// Check if user is banned
if ($user['status'] == 'banned') {
    echo json_encode(['success' => false, 'error' => 'User banned']);
    exit();
}

// Update session with fresh data
$_SESSION['user'] = $user;

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ==========================================
    // CHECK FOR NEW ORDERS (Real-time polling)
    // ==========================================
    case 'check_orders':
        $last_check = isset($_GET['last_check']) ? (int)$_GET['last_check'] : 0;

        if ($user['role'] == 'driver') {
            // For drivers: check for new pending orders
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders1 WHERE status = 'pending' AND UNIX_TIMESTAMP(created_at) > ?");
            $stmt->execute([$last_check]);
            $result = $stmt->fetch();

            // Get pending orders count
            $pending = $conn->query("SELECT COUNT(*) FROM orders1 WHERE status = 'pending'")->fetchColumn();

            echo json_encode([
                'success' => true,
                'new_orders' => (int)$result['count'],
                'pending_count' => (int)$pending,
                'timestamp' => time(),
                'should_notify' => $result['count'] > 0 && $last_check > 0
            ]);

        } elseif ($user['role'] == 'customer') {
            // For customers: check for order status changes
            $stmt = $conn->prepare("SELECT id, status, driver_id, UNIX_TIMESTAMP(updated_at) as updated_ts FROM orders1 WHERE (customer_name = ? OR client_id = ?) AND UNIX_TIMESTAMP(updated_at) > ? ORDER BY updated_at DESC LIMIT 5");
            $stmt->execute([$user['username'], $user['id'], $last_check]);
            $changed_orders = $stmt->fetchAll();

            $notifications = [];
            foreach ($changed_orders as $order) {
                $notifications[] = [
                    'order_id' => $order['id'],
                    'status' => $order['status'],
                    'driver_id' => $order['driver_id']
                ];
            }

            echo json_encode([
                'success' => true,
                'changed_orders' => $notifications,
                'timestamp' => time(),
                'should_notify' => count($notifications) > 0 && $last_check > 0
            ]);

        } else {
            echo json_encode([
                'success' => true,
                'timestamp' => time()
            ]);
        }
        break;

    // ==========================================
    // UPDATE DRIVER LOCATION (Deprecated - kept for compatibility)
    // ==========================================
    case 'update_location':
        echo json_encode(['success' => true, 'message' => 'GPS location no longer required']);
        break;

    // ==========================================
    // GET PENDING ORDERS (Zone-based)
    // ==========================================
    case 'get_pending_orders':
        if ($user['role'] !== 'driver') {
            echo json_encode(['success' => false, 'error' => 'Not a driver']);
            exit();
        }

        // Check if driver is online - if not, don't return any pending orders
        if (empty($user['is_online'])) {
            echo json_encode([
                'success' => true,
                'orders' => [],
                'message' => 'Driver is offline'
            ]);
            exit();
        }

        try {
            // Get driver's working zones and validate against valid zones from config
            $driverWorkingZones = !empty($user['working_zones']) ? explode(',', $user['working_zones']) : [];
            
            // Validate working zones against valid zone list
            $validZones = array_keys($zones);
            $driverWorkingZones = array_filter($driverWorkingZones, function($zone) use ($validZones) {
                return in_array($zone, $validZones);
            });
            
            if (!empty($driverWorkingZones)) {
                // Filter orders by driver's working zones (pickup OR dropoff zone must match)
                $zonePlaceholders = implode(',', array_fill(0, count($driverWorkingZones), '?'));
                $sql = "SELECT id, details, address, client_phone, pickup_zone, dropoff_zone, delivery_price, created_at 
                        FROM orders1 
                        WHERE status = 'pending' AND (pickup_zone IN ($zonePlaceholders) OR dropoff_zone IN ($zonePlaceholders))
                        ORDER BY id DESC LIMIT 20";
                $stmt = $conn->prepare($sql);
                $params = array_merge($driverWorkingZones, $driverWorkingZones);
                $stmt->execute($params);
            } else {
                // No working zones set - show all pending orders
                $stmt = $conn->prepare("SELECT id, details, address, client_phone, pickup_zone, dropoff_zone, delivery_price, created_at FROM orders1 WHERE status = 'pending' ORDER BY id DESC LIMIT 20");
                $stmt->execute();
            }
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    // ==========================================
    // GET DRIVER INFO (Zone-based - no GPS tracking)
    // ==========================================
    case 'get_driver_location':
        $driverId = (int)($_GET['driver_id'] ?? 0);

        if (!$driverId) {
            echo json_encode(['success' => false, 'error' => 'Driver ID required']);
            exit();
        }

        try {
            $stmt = $conn->prepare("SELECT full_name, phone, is_verified FROM users1 WHERE id = ? AND role = 'driver'");
            $stmt->execute([$driverId]);
            $driver = $stmt->fetch();

            if ($driver) {
                echo json_encode([
                    'success' => true,
                    'driver_name' => $driver['full_name'],
                    'driver_phone' => $driver['phone'],
                    'is_verified' => $driver['is_verified']
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Driver not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;

    // ==========================================
    // GET ORDER DETAILS
    // ==========================================
    case 'get_order':
        $orderId = (int)($_GET['order_id'] ?? 0);

        if (!$orderId) {
            echo json_encode(['success' => false, 'error' => 'Order ID required']);
            exit();
        }

        try {
            $stmt = $conn->prepare("
                SELECT o.*, u.full_name as driver_name, u.phone as driver_phone, u.avatar_url as driver_avatar,
                       u.last_lat as driver_lat, u.last_lng as driver_lng, u.is_verified as driver_verified
                FROM orders1 o
                LEFT JOIN users1 u ON o.driver_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                // Security check: only owner, driver, or admin can view
                if ($user['role'] === 'admin' ||
                    $order['driver_id'] == $user['id'] ||
                    $order['client_id'] == $user['id'] ||
                    $order['customer_name'] === $user['username']) {

                    echo json_encode(['success' => true, 'order' => $order]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Access denied']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Order not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;

    // ==========================================
    // GET ZONE PRICE
    // ==========================================
    case 'get_zone_price':
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');

        if (empty($from) || empty($to)) {
            echo json_encode(['success' => false, 'error' => 'Zones required']);
            exit();
        }

        // Include config to get zone prices
        require_once 'config.php';
        $price = getZonePrice($from, $to);

        echo json_encode([
            'success' => true,
            'from' => $from,
            'to' => $to,
            'price' => $price
        ]);
        break;

    // ==========================================
    // GET USER POINTS
    // ==========================================
    case 'get_user_points':
        $stmt = $conn->prepare("SELECT points FROM users1 WHERE id = ?");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'points' => (int)$result['points']
        ]);
        break;

    // ==========================================
    // GET USER STATS
    // ==========================================
    case 'get_stats':
        try {
            if ($user['role'] === 'driver') {
                $stats = getDriverStats($conn, $user['id']);
            } elseif ($user['role'] === 'customer') {
                $stats = getClientStats($conn, $user['id'], $user['username']);
            } else {
                // Admin stats
                $stats = [
                    'total_orders' => $conn->query("SELECT COUNT(*) FROM orders1")->fetchColumn(),
                    'pending_orders' => $conn->query("SELECT COUNT(*) FROM orders1 WHERE status='pending'")->fetchColumn(),
                    'total_drivers' => $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver'")->fetchColumn(),
                    'total_customers' => $conn->query("SELECT COUNT(*) FROM users1 WHERE role='customer'")->fetchColumn()
                ];
            }
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;

    // ==========================================
    // VALIDATE PROMO CODE
    // ==========================================
    case 'validate_promo':
        $code = strtoupper(trim($_GET['code'] ?? ''));

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a promo code']);
            exit();
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
            $stmt->execute([$code]);
            $promo = $stmt->fetch();

            if (!$promo) {
                echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
                exit();
            }

            // Check if expired
            $now = time();
            if ($promo['valid_from'] && strtotime($promo['valid_from']) > $now) {
                echo json_encode(['success' => false, 'message' => 'Promo code not yet valid']);
                exit();
            }

            if ($promo['valid_until'] && strtotime($promo['valid_until']) < $now) {
                echo json_encode(['success' => false, 'message' => 'Promo code has expired']);
                exit();
            }

            // Check if max uses reached
            if ($promo['max_uses'] && $promo['used_count'] >= $promo['max_uses']) {
                echo json_encode(['success' => false, 'message' => 'Promo code has reached maximum uses']);
                exit();
            }

            // Check if user already used this code
            if ($user['role'] == 'customer') {
                $check = $conn->prepare("SELECT id FROM promo_code_uses WHERE promo_code_id = ? AND user_id = ?");
                $check->execute([$promo['id'], $user['id']]);
                if ($check->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'You have already used this promo code']);
                    exit();
                }
            }

            // Valid promo code
            $discount_text = $promo['discount_type'] == 'percentage'
                ? $promo['discount_value'] . '% off'
                : $promo['discount_value'] . ' MRU off';

            echo json_encode([
                'success' => true,
                'message' => 'Valid! You get ' . $discount_text,
                'promo' => [
                    'id' => $promo['id'],
                    'code' => $promo['code'],
                    'discount_type' => $promo['discount_type'],
                    'discount_value' => $promo['discount_value']
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error validating promo code']);
        }
        break;

    // ==========================================
    // ACCEPT ORDER (For AJAX bubble acceptance)
    // ==========================================
    case 'accept_order':
        if ($user['role'] !== 'driver') {
            echo json_encode(['success' => false, 'error' => 'Not a driver']);
            exit();
        }

        $oid = (int)($_POST['oid'] ?? 0);
        if (!$oid) {
            echo json_encode(['success' => false, 'error' => 'Order ID required']);
            exit();
        }

        // Check driver verification by admin
        if (empty($user['is_verified'])) {
            echo json_encode(['success' => false, 'error' => $t['driver_not_verified'] ?? 'Your account must be verified by admin before accepting orders']);
            exit();
        }

        // Check phone verification
        if (!isPhoneVerified($user)) {
            echo json_encode(['success' => false, 'error' => $t['add_phone_first'] ?? 'Please add your phone number first']);
            exit();
        }

        // Check if driver has too many active orders
        $activeOrders = countActiveOrders($conn, $user['id']);
        if ($activeOrders >= $driver_max_active_orders) {
            echo json_encode(['success' => false, 'error' => $t['max_orders_reached'] ?? 'You have reached the maximum number of active orders']);
            exit();
        }

        // Check points balance
        if ($user['points'] < $points_cost_per_order) {
            echo json_encode(['success' => false, 'error' => $t['err_low_bal'] ?? 'Insufficient points']);
            exit();
        }

        try {
            $conn->beginTransaction();

            // Lock the order row for update (race condition prevention)
            $chk = $conn->prepare("SELECT id, status FROM orders1 WHERE id=? AND status='pending' FOR UPDATE");
            $chk->execute([$oid]);
            $order = $chk->fetch();

            if ($order) {
                // Update order
                $upd = $conn->prepare("UPDATE orders1 SET status='accepted', driver_id=?, accepted_at=NOW(), points_cost=? WHERE id=?");
                $upd->execute([$user['id'], $points_cost_per_order, $oid]);

                // Deduct points from driver
                $deduct = $conn->prepare("UPDATE users1 SET points = points - ?, total_orders = total_orders + 1 WHERE id=?");
                $deduct->execute([$points_cost_per_order, $user['id']]);

                $conn->commit();

                // Refresh session data from database to avoid race conditions
                $refreshStmt = $conn->prepare("SELECT * FROM users1 WHERE id=?");
                $refreshStmt->execute([$user['id']]);
                $_SESSION['user'] = $refreshStmt->fetch();

                echo json_encode(['success' => true, 'message' => $t['success_acc'] ?? 'Order accepted successfully', 'new_points' => $_SESSION['user']['points']]);
            } else {
                $conn->rollBack();
                echo json_encode(['success' => false, 'error' => $t['err_order_taken'] ?? 'Order already taken by another driver']);
            }
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => $t['err_general'] ?? 'System Error']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
