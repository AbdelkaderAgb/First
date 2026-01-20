<?php
/**
 * Helper Functions & Translations
 * Enhanced Delivery Pro System v2.0
 */

// ==========================================
// LANGUAGE SETTINGS
// ==========================================
// Support for Arabic and French languages

// Handle language switching
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Get current language from session or default to Arabic
$lang = $_SESSION['lang'] ?? 'ar';

// Validate language (only ar or fr allowed)
if (!in_array($lang, ['ar', 'fr'])) {
    $lang = 'ar';
    $_SESSION['lang'] = 'ar';
}

// Set text direction based on language
$dir = ($lang == 'ar') ? 'rtl' : 'ltr';

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Escape HTML entities for safe output
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date according to current language
 */
function fmtDate($date) {
    global $lang;
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;

    // Show relative time for recent dates
    if ($diff < 60) {
        return $lang == 'ar' ? 'الآن' : 'Maintenant';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $lang == 'ar' ? "منذ {$mins} دقيقة" : "Il y a {$mins} min";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $lang == 'ar' ? "منذ {$hours} ساعة" : "Il y a {$hours}h";
    }

    // Format as date
    if ($lang == 'ar') {
        $months_ar = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                      'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $day = date('d', $timestamp);
        $month = $months_ar[(int)date('m', $timestamp)];
        $year = date('Y', $timestamp);
        $time = date('h:i A', $timestamp);
        return "$day $month $year - $time";
    }
    
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Set flash message in session
 */
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/**
 * Get and display flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = ($f['type'] == 'success') ? 'check-circle' : (($f['type'] == 'warning') ? 'exclamation-circle' : 'exclamation-triangle');
        $cls = ($f['type'] == 'error') ? 'danger' : $f['type'];
        return "
        <div class='alert alert-{$cls} alert-dismissible fade show shadow-sm border-0 mb-4 animate__animated animate__fadeInDown' role='alert'>
            <div class='d-flex align-items-center'>
                <i class='fas fa-{$icon} fa-lg me-3'></i>
                <div>{$f['msg']}</div>
            </div>
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
    }
    return '';
}

/**
 * Get status badge class
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => 'badge-pending',
        'accepted' => 'badge-accepted',
        'picked_up' => 'badge-picked_up',
        'delivered' => 'badge-delivered',
        'cancelled' => 'badge-cancelled'
    ];
    return $badges[$status] ?? 'bg-secondary';
}

/**
 * Get status icon
 */
function getStatusIcon($status) {
    $icons = [
        'pending' => 'clock',
        'accepted' => 'truck',
        'picked_up' => 'box',
        'delivered' => 'check-double',
        'cancelled' => 'times-circle'
    ];
    return $icons[$status] ?? 'circle';
}

/**
 * Get user avatar URL or generate initials avatar
 */
function getAvatarUrl($user) {
    if (!empty($user['avatar_url'])) {
        $fullPath = __DIR__ . '/' . $user['avatar_url'];
        if (file_exists($fullPath)) {
            // Add cache-busting parameter based on file modification time
            $mtime = filemtime($fullPath);
            return $user['avatar_url'] . '?v=' . $mtime;
        }
    }
    // Return null to use initials avatar
    return null;
}

/**
 * Get user initials for avatar
 */
function getUserInitials($user) {
    $name = $user['full_name'] ?? $user['username'] ?? 'U';
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
    }
    return mb_strtoupper(mb_substr($name, 0, 2));
}

/**
 * Get avatar background color based on role
 */
function getAvatarColor($role) {
    $colors = [
        'admin' => '#dc2626',
        'driver' => '#0891b2',
        'customer' => '#059669'
    ];
    return $colors[$role] ?? '#6366f1';
}

/**
 * Handle avatar upload
 */
function uploadAvatar($file, $userId) {
    global $uploads_dir, $conn;

    // Fallback if $uploads_dir is not set
    if (empty($uploads_dir)) {
        $uploads_dir = dirname(__FILE__) . '/uploads';
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Create user directory
    $user_dir = $uploads_dir . '/avatars/' . $userId;
    if (!is_dir($user_dir)) {
        if (!mkdir($user_dir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }

    // Delete old avatar if exists
    try {
        $stmt = $conn->prepare("SELECT avatar_url FROM users1 WHERE id = ?");
        $stmt->execute([$userId]);
        $oldAvatar = $stmt->fetchColumn();
        if ($oldAvatar) {
            $oldPath = dirname(__FILE__) . '/' . $oldAvatar;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
    } catch (Exception $e) {
        // Continue even if old avatar deletion fails
    }

    // Generate filename - use mime type to determine extension for reliability
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];
    $ext = $mimeToExt[$mime] ?? 'jpg';
    $filename = 'avatar_' . time() . '.' . $ext;
    $filepath = $user_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'path' => 'uploads/avatars/' . $userId . '/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'Failed to save file'];
}

/**
 * Format rating stars
 */
function formatRating($rating, $showNumber = true) {
    $rating = floatval($rating);
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $html = '<span class="rating-stars">';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    if ($showNumber) {
        $html .= ' <small class="text-muted">(' . number_format($rating, 1) . ')</small>';
    }
    $html .= '</span>';

    return $html;
}

/**
 * Get driver stats
 */
function getDriverStats($conn, $driverId) {
    $stats = [
        'total_orders' => 0,
        'total_delivered' => 0,
        'total_earnings' => 0,
        'completed_today' => 0,
        'earnings_today' => 0,
        'earnings_week' => 0,
        'earnings_month' => 0,
        'this_month' => 0,
        'orders_this_month' => 0,
        'active_orders' => 0,
        'rating' => 5.0
    ];

    // Total completed orders (delivered)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE driver_id = ? AND status = 'delivered'");
    $stmt->execute([$driverId]);
    $stats['total_orders'] = $stmt->fetchColumn();
    // Alias for backward compatibility with index.php
    $stats['total_delivered'] = $stats['total_orders'];

    // Completed today
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE driver_id = ? AND status = 'delivered' AND DATE(delivered_at) = CURDATE()");
    $stmt->execute([$driverId]);
    $stats['completed_today'] = $stmt->fetchColumn();

    // Orders this month (count)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE driver_id = ? AND status = 'delivered' AND MONTH(delivered_at) = MONTH(NOW()) AND YEAR(delivered_at) = YEAR(NOW())");
    $stmt->execute([$driverId]);
    $stats['orders_this_month'] = $stmt->fetchColumn();

    // Earnings today (points spent by driver for orders)
    $stmt = $conn->prepare("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE driver_id = ? AND status = 'delivered' AND DATE(delivered_at) = CURDATE()");
    $stmt->execute([$driverId]);
    $stats['earnings_today'] = $stmt->fetchColumn();

    // Earnings this week
    $stmt = $conn->prepare("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE driver_id = ? AND status = 'delivered' AND YEARWEEK(delivered_at) = YEARWEEK(NOW())");
    $stmt->execute([$driverId]);
    $stats['earnings_week'] = $stmt->fetchColumn();

    // Earnings this month
    $stmt = $conn->prepare("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE driver_id = ? AND status = 'delivered' AND MONTH(delivered_at) = MONTH(NOW()) AND YEAR(delivered_at) = YEAR(NOW())");
    $stmt->execute([$driverId]);
    $stats['earnings_month'] = $stmt->fetchColumn();
    // Alias for backward compatibility with index.php
    $stats['this_month'] = $stats['earnings_month'];

    // Total earnings (all time)
    $stmt = $conn->prepare("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE driver_id = ? AND status = 'delivered'");
    $stmt->execute([$driverId]);
    $stats['total_earnings'] = $stmt->fetchColumn();

    // Active orders (accepted or picked_up)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE driver_id = ? AND status IN ('accepted', 'picked_up')");
    $stmt->execute([$driverId]);
    $stats['active_orders'] = $stmt->fetchColumn();

    // Average rating
    $stmt = $conn->prepare("SELECT AVG(score) FROM ratings WHERE ratee_id = ?");
    $stmt->execute([$driverId]);
    $avgRating = $stmt->fetchColumn();
    if ($avgRating) {
        $stats['rating'] = round($avgRating, 2);
    }

    return $stats;
}

/**
 * Get client stats
 */
function getClientStats($conn, $clientId, $username) {
    $stats = [
        'total_orders' => 0,
        'active' => 0,
        'delivered' => 0,
        'this_month' => 0
    ];

    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE client_id = ? OR customer_name = ?");
    $stmt->execute([$clientId, $username]);
    $stats['total_orders'] = $stmt->fetchColumn();

    // Active orders (pending, accepted, picked_up)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE (client_id = ? OR customer_name = ?) AND status IN ('pending', 'accepted', 'picked_up')");
    $stmt->execute([$clientId, $username]);
    $stats['active'] = $stmt->fetchColumn();

    // Delivered
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE (client_id = ? OR customer_name = ?) AND status = 'delivered'");
    $stmt->execute([$clientId, $username]);
    $stats['delivered'] = $stmt->fetchColumn();

    // This month
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE (client_id = ? OR customer_name = ?) AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $stmt->execute([$clientId, $username]);
    $stats['this_month'] = $stmt->fetchColumn();

    return $stats;
}

/**
 * Count active orders for a driver
 */
function countActiveOrders($conn, $driverId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders1 WHERE driver_id = ? AND status IN ('accepted', 'picked_up')");
    $stmt->execute([$driverId]);
    return $stmt->fetchColumn();
}

/**
 * Check if phone is verified
 */
function isPhoneVerified($user) {
    return !empty($user['phone']) && !empty($user['phone_verified']);
}

/**
 * Track a page visit (unique by IP per day)
 */
function trackVisitor($conn, $userId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    $ip = trim($ip);
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '';
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    $visitDate = date('Y-m-d');
    
    try {
        // Insert or update (unique per IP per day)
        $stmt = $conn->prepare("INSERT INTO site_visitors (ip_address, user_agent, page_url, referrer, user_id, visit_date) 
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE 
                                page_url = VALUES(page_url), 
                                user_id = COALESCE(VALUES(user_id), user_id)");
        $stmt->execute([$ip, $userAgent, $pageUrl, $referrer, $userId, $visitDate]);
    } catch (Exception $e) {
        // Silently fail - visitor tracking should not break the site
    }
}

/**
 * Get visitor statistics
 */
function getVisitorStats($conn) {
    $stats = [
        'total' => 0,
        'today' => 0,
        'this_week' => 0,
        'this_month' => 0
    ];
    
    try {
        // Total unique visitors (all time)
        $stmt = $conn->query("SELECT COUNT(DISTINCT ip_address) FROM site_visitors");
        $stats['total'] = $stmt->fetchColumn() ?: 0;
        
        // Today's visitors
        $stmt = $conn->prepare("SELECT COUNT(*) FROM site_visitors WHERE visit_date = CURDATE()");
        $stmt->execute();
        $stats['today'] = $stmt->fetchColumn() ?: 0;
        
        // This week's visitors
        $stmt = $conn->prepare("SELECT COUNT(*) FROM site_visitors WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute();
        $stats['this_week'] = $stmt->fetchColumn() ?: 0;
        
        // This month's visitors
        $stmt = $conn->prepare("SELECT COUNT(*) FROM site_visitors WHERE MONTH(visit_date) = MONTH(CURDATE()) AND YEAR(visit_date) = YEAR(CURDATE())");
        $stmt->execute();
        $stats['this_month'] = $stmt->fetchColumn() ?: 0;
    } catch (Exception $e) {
        // Return empty stats on error
    }
    
    return $stats;
}

// ==========================================
// TRANSLATIONS - Include from separate file
// ==========================================
// Load translations based on current language
$lang_file = __DIR__ . '/lang/' . $lang . '.php';
if (file_exists($lang_file)) {
    $t = require_once $lang_file;
} else {
    // Fallback to Arabic if language file not found
    $t = require_once __DIR__ . '/lang/ar.php';
}
?>
