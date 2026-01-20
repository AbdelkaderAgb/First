<?php
/**
 * Helper Functions & Translations
 * Enhanced Delivery Pro System v2.0
 */

// ==========================================
// LANGUAGE SETTINGS
// ==========================================
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'ar';
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
// COMPLETE TRANSLATIONS
// ==========================================
$text = [
    'ar' => [
        // App
        'app_name' => 'نظام التوصيل برق',
        'app_desc' => 'خدمة توصيل سريعة وموثوقة',
        'welcome' => 'مرحباً',
        'welcome_back' => 'مرحباً بعودتك',

        // Auth
        'login_title' => 'تسجيل الدخول',
        'register_title' => 'إنشاء حساب جديد',
        'user_ph' => 'اسم المستخدم',
        'pass_ph' => 'كلمة المرور',
        'confirm_pass_ph' => 'تأكيد كلمة المرور',
        'full_name_ph' => 'الاسم الكامل',
        'phone_ph' => 'رقم الهاتف',
        'email_ph' => 'البريد الإلكتروني',
        'btn_login' => 'تسجيل الدخول',
        'btn_register' => 'إنشاء حساب',
        'have_account' => 'لديك حساب بالفعل؟',
        'no_account' => 'ليس لديك حساب؟',
        'login_here' => 'سجل دخولك',
        'register_here' => 'أنشئ حساباً',
        'logout' => 'تسجيل الخروج',
        'logout_confirm' => 'هل تريد تسجيل الخروج؟',

        // Dashboard
        'dashboard' => 'لوحة التحكم',
        'home' => 'الرئيسية',
        'settings' => 'الإعدادات',
        'profile' => 'الملف الشخصي',
        'my_account' => 'حسابي',

        // Balance & Points
        'balance' => 'رصيدي',
        'points' => 'نقطة',
        'pts' => 'نقطة',
        'current_balance' => 'الرصيد الحالي',
        'recharge_wa' => 'شحن عبر واتساب',
        'recharge_now' => 'اشحن الآن',
        'low_balance' => 'رصيد منخفض',

        // Orders
        'new_order' => 'طلب جديد',
        'create_order' => 'إنشاء طلب',
        'order_details' => 'تفاصيل الطلب',
        'order_info' => 'معلومات الطلب',
        'address' => 'العنوان',
        'delivery_address' => 'عنوان التوصيل',
        'btn_publish' => 'نشر الطلب',
        'recent_orders' => 'الطلبات',
        'my_orders' => 'طلباتي',
        'all_orders' => 'جميع الطلبات',
        'available_orders' => 'الطلبات المتاحة',
        'order_number' => 'رقم الطلب',
        'order_date' => 'تاريخ الطلب',

        // Status
        'status' => 'الحالة',
        'action' => 'الإجراءات',
        'actions' => 'الإجراءات',
        'st_pending' => 'بانتظار سائق',
        'st_accepted' => 'قيد التوصيل',
        'st_delivered' => 'تم التسليم',
        'st_cancelled' => 'ملغي',

        // PIN
        'pin_label' => 'كود التسليم',
        'pin_code' => 'رمز PIN',
        'pin_note' => 'أعطِ هذا الكود للسائق عند الاستلام فقط',
        'pin_warning' => 'لا تشارك هذا الكود إلا عند استلام طلبك',
        'enter_pin' => 'أدخل كود التسليم',

        // Driver
        'driver_accept' => 'قبول',
        'driver_pickup' => 'تم الاستلام',
        'accept_order' => 'قبول الطلب',
        'driver_cost' => 'التكلفة',
        'cost_per_order' => 'تكلفة الطلب',
        'verify_fin' => 'إتمام التسليم',
        'verify_ph' => 'PIN',
        'finish_delivery' => 'إنهاء التوصيل',
        'my_deliveries' => 'توصيلاتي',
        'accepted_orders' => 'الطلبات المقبولة',

        // Errors
        'err_low_bal' => 'رصيدك غير كافٍ لقبول طلبات جديدة',
        'err_auth' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
        'err_banned' => 'تم إيقاف حسابك. تواصل مع الإدارة',
        'err_pin' => 'كود التسليم غير صحيح',
        'err_pin_format' => 'كود التسليم يجب أن يكون 4 أرقام',
        'err_username_short' => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل',
        'err_password_short' => 'كلمة المرور يجب أن تكون 4 أحرف على الأقل',
        'err_password_mismatch' => 'كلمتا المرور غير متطابقتين',
        'err_username_exists' => 'اسم المستخدم مستخدم بالفعل',
        'err_register' => 'فشل إنشاء الحساب. حاول مرة أخرى',
        'err_order_taken' => 'هذا الطلب تم قبوله من سائق آخر',
        'err_general' => 'حدث خطأ. حاول مرة أخرى',
        'err_invalid_order' => 'طلب غير صالح',
        'err_order_not_found' => 'الطلب غير موجود',
        'err_not_your_order' => 'هذا الطلب غير مخصص لك',
        'err_order_not_accepted' => 'يجب أن يكون الطلب في حالة مقبول لاستلامه',
        'err_pickup_failed' => 'فشل تحديث حالة الطلب. حاول مرة أخرى',
        'err_order_status' => 'لا يمكن إكمال الطلب في حالته الحالية',

        // Success
        'success_add' => 'تم نشر طلبك بنجاح',
        'success_acc' => 'تم قبول الطلب بنجاح',
        'success_fin' => 'تم تسليم الطلب بنجاح. أحسنت!',
        'success_register' => 'تم إنشاء حسابك بنجاح. يمكنك الآن تسجيل الدخول',
        'success_profile' => 'تم تحديث ملفك الشخصي بنجاح',
        'success_order_cancelled' => 'تم إلغاء الطلب بنجاح',
        'success_user_added' => 'تمت إضافة المستخدم بنجاح',
        'success_user_updated' => 'تم تحديث المستخدم بنجاح',
        'success_user_deleted' => 'تم حذف المستخدم بنجاح',
        'success_points_added' => 'تمت إضافة النقاط بنجاح',

        // Empty states
        'empty_list' => 'لا توجد طلبات حالياً',
        'no_orders' => 'لا توجد طلبات',
        'no_pending_orders' => 'لا توجد طلبات متاحة حالياً',
        'no_users' => 'لا يوجد مستخدمين',
        'check_back_later' => 'تحقق لاحقاً',

        // Admin
        'admin_panel' => 'لوحة الإدارة',
        'manage_users' => 'إدارة العملاء',
        'manage_drivers' => 'إدارة السائقين',
        'manage_orders' => 'إدارة الطلبات',
        'add_user' => 'إضافة مستخدم',
        'edit_user' => 'تعديل المستخدم',
        'add_order' => 'إضافة طلب',
        'edit_order' => 'تعديل الطلب',
        'add_points' => 'إضافة نقاط',
        'recharge_points' => 'شحن النقاط',

        // User fields
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'new_password' => 'كلمة مرور جديدة',
        'current_password' => 'كلمة المرور الحالية',
        'confirm_new_password' => 'تأكيد كلمة المرور الجديدة',
        'role' => 'الدور',
        'user_type' => 'نوع المستخدم',

        // Roles
        'admin' => 'مدير',
        'driver' => 'سائق',
        'customer' => 'عميل',
        'drivers' => 'السائقين',
        'customers' => 'العملاء',

        // Status labels
        'active' => 'نشط',
        'banned' => 'محظور',
        'online' => 'متصل',
        'offline' => 'غير متصل',
        'offline_warning' => 'أنت غير متصل',
        'offline_warning_desc' => 'اذهب للوضع المتصل لاستلام طلبات جديدة',

        // Actions
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'confirm' => 'تأكيد',
        'close' => 'إغلاق',
        'back' => 'رجوع',
        'submit' => 'إرسال',
        'ban' => 'حظر',
        'unban' => 'إلغاء الحظر',
        'cancel_order' => 'إلغاء الطلب',
        'delete_order' => 'حذف الطلب',
        'view_details' => 'عرض التفاصيل',

        // Statistics
        'total_users' => 'إجمالي المستخدمين',
        'total_orders' => 'إجمالي الطلبات',
        'total_drivers' => 'إجمالي السائقين',
        'total_customers' => 'إجمالي العملاء',
        'active_drivers' => 'السائقين النشطين',
        'pending_orders' => 'الطلبات المعلقة',
        'completed_orders' => 'الطلبات المكتملة',
        'statistics' => 'الإحصائيات',

        // Order fields
        'customer_name' => 'اسم العميل',
        'assign_driver' => 'تعيين سائق',
        'no_driver' => 'بدون سائق',
        'select_driver' => 'اختر سائق',
        'select_status' => 'اختر الحالة',

        // Settings
        'save_changes' => 'حفظ التغييرات',
        'leave_empty_password' => 'اتركها فارغة للإبقاء على كلمة المرور الحالية',
        'profile_updated' => 'تم تحديث الملف الشخصي',
        'change_password' => 'تغيير كلمة المرور',
        'account_settings' => 'إعدادات الحساب',
        'personal_info' => 'المعلومات الشخصية',
        'security' => 'الأمان',

        // Notifications
        'new_order_alert' => 'طلب جديد!',
        'order_status_changed' => 'تم تحديث حالة طلبك',
        'notification' => 'إشعار',
        'notifications' => 'الإشعارات',
        'new_notification' => 'إشعار جديد',

        // Confirmations
        'confirm_delete' => 'هل أنت متأكد من الحذف؟',
        'confirm_cancel' => 'هل تريد إلغاء هذا الطلب؟',
        'confirm_ban' => 'هل تريد حظر هذا المستخدم؟',
        'confirm_accept' => 'هل تريد قبول هذا الطلب؟',
        'action_irreversible' => 'لا يمكن التراجع عن هذا الإجراء',

        // Misc
        'loading' => 'جاري التحميل...',
        'please_wait' => 'يرجى الانتظار...',
        'search' => 'بحث',
        'filter' => 'تصفية',
        'refresh' => 'تحديث',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'id' => 'الرقم',
        'details' => 'التفاصيل',
        'amount' => 'المبلغ',
        'select' => 'اختر',
        'optional' => 'اختياري',
        'required' => 'مطلوب',
        'demo_accounts' => 'حسابات تجريبية',
        'all_rights' => 'جميع الحقوق محفوظة',
        'copyright' => 'حقوق النشر',

        // Time
        'now' => 'الآن',
        'today' => 'اليوم',
        'yesterday' => 'أمس',
        'minutes_ago' => 'منذ دقائق',
        'hours_ago' => 'منذ ساعات',
        'days_ago' => 'منذ أيام',

        // Enhanced v2.0
        'serial_no' => 'الرقم التسلسلي',
        'user_id' => 'معرف المستخدم',
        'profile_picture' => 'صورة الملف الشخصي',
        'upload_photo' => 'رفع صورة',
        'change_photo' => 'تغيير الصورة',
        'remove_photo' => 'إزالة الصورة',
        'phone_required' => 'رقم الهاتف مطلوب للمتابعة',
        'phone_not_verified' => 'يرجى إضافة رقم هاتفك للمتابعة',
        'verify_phone' => 'تأكيد رقم الهاتف',
        'phone_verified' => 'رقم الهاتف مؤكد',
        'add_phone_first' => 'أضف رقم هاتفك أولاً',

        // Driver Verification
        'driver_verified' => 'سائق موثق',
        'driver_not_verified' => 'يجب أن يتم التحقق من حسابك من قبل المدير قبل قبول الطلبات',
        'pending_verification' => 'في انتظار التحقق',
        'verified' => 'موثق',
        'verification' => 'التحقق',
        'verify_driver' => 'توثيق السائق',
        'unverify' => 'إلغاء التوثيق',
        'confirm_verify' => 'هل تريد توثيق هذا السائق؟',
        'confirm_unverify' => 'هل تريد إلغاء التوثيق؟',
        'driver_verified_success' => 'تم توثيق السائق بنجاح! يمكنه الآن قبول الطلبات.',
        'driver_unverified' => 'تم إلغاء توثيق السائق.',

        // Phone Registration (Mauritania: 8 digits starting with 2, 3, or 4)
        'phone_example' => '2XXXXXXX',
        'phone_format_hint' => '8 أرقام تبدأ بـ 2 أو 3 أو 4',
        'err_phone_invalid' => 'الهاتف يجب أن يكون 8 أرقام تبدأ بـ 2 أو 3 أو 4',
        'err_phone_exists' => 'رقم الهاتف مسجل بالفعل. الرجاء تسجيل الدخول.',
        'demo_phone_login' => 'حسابات تجريبية (الهاتف / كلمة المرور):',
        'profile_completed' => 'تم تحديث الملف الشخصي بنجاح!',

        // Driver Enhanced
        'go_online' => 'أصبح متصلاً',
        'go_offline' => 'أصبح غير متصل',
        'you_are_online' => 'أنت متصل الآن وستستلم طلبات جديدة',
        'you_are_offline' => 'أنت غير متصل',
        'you_are_offline_no_orders' => 'أنت غير متصل ولن تستلم طلبات جديدة',
        'earnings' => 'الأرباح',
        'my_earnings' => 'أرباحي',
        'today_earnings' => 'أرباح اليوم',
        'week_earnings' => 'أرباح الأسبوع',
        'month_earnings' => 'أرباح الشهر',
        'completed_deliveries' => 'التوصيلات المكتملة',
        'active_deliveries' => 'التوصيلات النشطة',
        'my_rating' => 'تقييمي',
        'max_orders_reached' => 'لقد وصلت للحد الأقصى من الطلبات النشطة',

        // Client Enhanced
        'active_orders' => 'الطلبات النشطة',
        'track_order' => 'تتبع الطلب',
        'order_history' => 'سجل الطلبات',
        'no_history' => 'لا يوجد سجل طلبات',
        'history_empty' => 'الطلبات المكتملة ستظهر هنا',
        'rate_driver' => 'قيّم السائق',
        'rate_delivery' => 'قيّم التوصيل',
        'your_rating' => 'تقييمك',
        'leave_comment' => 'اترك تعليقاً',
        'submit_rating' => 'إرسال التقييم',
        'thanks_for_rating' => 'شكراً لتقييمك!',

        // Order Status Enhanced
        'st_picked_up' => 'تم الاستلام',
        'finding_driver' => 'جاري البحث عن سائق...',
        'driver_assigned' => 'تم تعيين سائق',
        'driver_on_way' => 'السائق في الطريق',
        'driver_arrived' => 'السائق وصل',
        'package_picked' => 'تم استلام الطرد! أدخل كود PIN لإكمال التوصيل',
        'on_the_way' => 'في الطريق إليك',

        // Tracking
        'live_tracking' => 'التتبع المباشر',
        'distance' => 'المسافة',
        'eta' => 'الوقت المتوقع',
        'km' => 'كم',
        'min' => 'دقيقة',
        'call_driver' => 'اتصل بالسائق',
        'message_driver' => 'راسل السائق',

        // Stats
        'this_week' => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'orders_count' => 'عدد الطلبات',
        'delivery_count' => 'عدد التوصيلات',

        // Help & Footer
        'need_help' => 'تحتاج مساعدة؟',
        'contact_us' => 'اتصل بنا',
        'connect_with_us' => 'تواصل معنا',
        'call_us' => 'اتصل بنا',
        'fast_delivery' => 'خدمة توصيل سريعة وموثوقة لجميع احتياجاتك.',
        'available_24_7' => 'متاح على مدار الساعة',
        'location_mauritania' => 'نواكشوط، موريتانيا',
        'secure_service' => 'خدمة آمنة',

        // GPS & Location
        'gps_status' => 'حالة GPS',
        'gps_enabled' => 'GPS مفعّل',
        'gps_disabled' => 'GPS معطّل',
        'enable_gps' => 'تفعيل GPS',
        'disable_gps' => 'إيقاف GPS',
        'turn_on_gps' => 'تشغيل GPS',
        'gps_required' => 'موقع GPS مطلوب ليجدك السائقون',
        'gps_driver_note' => 'فعّل GPS لرؤية الطلبات القريبة منك (7 كم)',
        'enable_gps_first' => 'فعّل GPS أولاً',
        'no_nearby_orders' => 'لا توجد طلبات قريبة (7 كم)',
        'geolocation_not_supported' => 'متصفحك لا يدعم تحديد الموقع',
        'location_error' => 'خطأ في تحديد الموقع',
        'location_denied' => 'تم رفض الوصول للموقع. يرجى تفعيل GPS.',
        'click_gps' => 'انقر على GPS لتحديد موقعك',
        'pickup_location' => 'موقع الاستلام',
        'updating_location' => 'جاري تحديث الموقع...',
        'location_updated' => 'تم تحديث الموقع',
        'gps_accuracy' => 'دقة الموقع',
        'last_update' => 'آخر تحديث',
        'new_order_nearby' => 'طلب جديد قريب!',
        'accept' => 'قبول',
        'decline' => 'رفض',
        'no_phone' => 'لا يوجد هاتف',
        'update_order' => 'تحديث الطلب',

        // Zone System
        'pickup_zone' => 'منطقة الاستلام',
        'dropoff_zone' => 'منطقة التوصيل',
        'select_zone' => 'اختر المنطقة',
        'delivery_price' => 'سعر التوصيل',
        'price' => 'السعر',
        'mru' => 'أوقية',
        'from_zone' => 'من',
        'to_zone' => 'إلى',
        'zone_required' => 'يرجى اختيار منطقة الاستلام والتوصيل',
        'address_placeholder' => 'العنوان التفصيلي (اختياري)',
        
        // Order Summary
        'order_summary' => 'ملخص الطلب',
        'delivery_price' => 'سعر التوصيل',
        'discount' => 'الخصم',
        'total_price' => 'الإجمالي',
        'subtotal' => 'المجموع الفرعي',

        // Working Zones (Driver)
        'working_zones' => 'مناطق العمل',
        'working_zones_note' => 'اختر المناطق التي تريد استلام الطلبات منها. اتركها فارغة لاستلام الطلبات من جميع المناطق.',
        'select_working_zones' => 'اختر مناطق العمل',
        'no_working_zones' => 'لم تحدد مناطق العمل بعد',
        'no_working_zones_tip' => 'نصيحة: حدد مناطق عملك في الإعدادات لاستلام الطلبات من المناطق المفضلة فقط.',

        // Enhanced Admin Statistics
        'new_this_month' => 'جديد هذا الشهر',
        'in_progress' => 'قيد التنفيذ',
        'orders' => 'الطلبات',
        'revenue' => 'الإيرادات',
        'delivery_value' => 'قيمة التوصيل',
        'performance' => 'الأداء',
        'cancelled' => 'ملغي',
        'avg_delivery_time' => 'متوسط وقت التوصيل',
        'total_delivery_value' => 'القيمة الإجمالية',
        'new_users_today' => 'مستخدمون جدد اليوم',
        'today_delivery_value' => 'قيمة اليوم',
        'top_drivers' => 'أفضل السائقين',
        'popular_zones' => 'المناطق الأكثر نشاطاً',
        'no_data' => 'لا توجد بيانات',
        'pending' => 'معلق',
        
        // Driver Info Display
        'driver_info' => 'معلومات السائق',
        'driver_phone' => 'هاتف السائق',
        'contact_driver' => 'التواصل مع السائق',
        'driver_rating' => 'تقييم السائق',
        'order_driver' => 'السائق المكلف',
        
        // Missing Admin UI Translations
        'select_all' => 'تحديد الكل',
        'bulk_recharge' => 'شحن جماعي',
        'promo_codes' => 'أكواد الخصم',
        'create_promo' => 'إنشاء كود خصم',
        'edit_promo' => 'تعديل كود الخصم',
        'no_promo_codes' => 'لا توجد أكواد خصم. أنشئ واحداً للبدء!',
        'confirm_delete' => 'هل أنت متأكد من الحذف؟',
        'delete_permanently' => 'حذف نهائي',
        'confirm_action' => 'تأكيد',
        'success_rate' => 'نسبة النجاح',
        'today_orders' => 'طلبات اليوم',
        'customers' => 'العملاء',
        'confirm_recharge' => 'تأكيد الشحن',
        'verify' => 'توثيق',
        'receiving_orders' => 'تستقبل الطلبات',
        'tap_to_go_online' => 'اضغط للاتصال',
        'your_balance' => 'رصيدك',
        'whatsapp_recharge' => 'شحن عبر واتساب',
        'recharge_note' => 'تواصل مع الدعم لإضافة رصيد إلى حسابك',
        'rating' => 'التقييم',
        'hello' => 'مرحباً',
        'start_your_day' => 'ابدأ يومك بنشاط!',
        'what_need_today' => 'ماذا تحتاج اليوم؟',
        'phone_auto_verify' => 'يتم التحقق من الهاتف تلقائياً عند إضافته',
        'order_details_placeholder' => 'صف ما تريد توصيله...',
        'enter_promo_code' => 'أدخل كود الخصم',
        'apply' => 'تطبيق',
        'total_revenue' => 'إجمالي الإيرادات',
        'today_revenue' => 'إيرادات اليوم',
        'delivered' => 'تم التسليم',
        'driver_finish' => 'إنهاء',
        'order_released' => 'تم إعادة الطلب. تم استرداد النقاط.',
        'confirm_release' => 'إعادة الطلب؟ سيتم استرداد النقاط.',
        
        // Driver Tiers
        'your_tier' => 'مستواك',
        'new_driver' => 'سائق جديد',
        'regular_driver' => 'سائق عادي',
        'pro_driver' => 'سائق محترف',
        'vip_driver' => 'سائق VIP',
        
        // Recharge History
        'recharge_history' => 'سجل الشحن',
        'no_recharge_history' => 'لا يوجد سجل شحن بعد.',
        'previous_balance' => 'الرصيد السابق',
        'new_balance' => 'الرصيد الجديد',
        'bulk' => 'جماعي',
        'single' => 'فردي',
        'call' => 'اتصال',
        
        // Visitor Statistics
        'site_visitors' => 'زوار الموقع',
        'visitors_today' => 'زوار اليوم',
        'visitors_this_week' => 'زوار الأسبوع',
        'visitors_this_month' => 'زوار الشهر',
        'total_visitors' => 'إجمالي الزوار'
    ],

    'fr' => [
        // App
        'app_name' => 'Système de Livraison Barq',
        'app_desc' => 'Service de livraison rapide et fiable',
        'welcome' => 'Bienvenue',
        'welcome_back' => 'Bon retour',

        // Auth
        'login_title' => 'Connexion',
        'register_title' => 'Créer un nouveau compte',
        'user_ph' => 'Nom d\'utilisateur',
        'pass_ph' => 'Mot de passe',
        'confirm_pass_ph' => 'Confirmer le mot de passe',
        'full_name_ph' => 'Nom complet',
        'phone_ph' => 'Numéro de téléphone',
        'email_ph' => 'Email',
        'btn_login' => 'Se connecter',
        'btn_register' => 'Créer un compte',
        'have_account' => 'Vous avez déjà un compte?',
        'no_account' => 'Vous n\'avez pas de compte?',
        'login_here' => 'Connectez-vous',
        'register_here' => 'Créer un compte',
        'logout' => 'Déconnexion',
        'logout_confirm' => 'Voulez-vous vous déconnecter?',

        // Dashboard
        'dashboard' => 'Tableau de bord',
        'home' => 'Accueil',
        'settings' => 'Paramètres',
        'profile' => 'Profil',
        'my_account' => 'Mon compte',

        // Balance & Points
        'balance' => 'Mon solde',
        'points' => 'points',
        'pts' => 'pts',
        'current_balance' => 'Solde actuel',
        'recharge_wa' => 'Recharger via WhatsApp',
        'recharge_now' => 'Recharger maintenant',
        'low_balance' => 'Solde faible',

        // Orders
        'new_order' => 'Nouvelle commande',
        'create_order' => 'Créer une commande',
        'order_details' => 'Détails de la commande',
        'order_info' => 'Informations de commande',
        'address' => 'Adresse',
        'delivery_address' => 'Adresse de livraison',
        'btn_publish' => 'Publier la commande',
        'recent_orders' => 'Commandes',
        'my_orders' => 'Mes commandes',
        'all_orders' => 'Toutes les commandes',
        'available_orders' => 'Commandes disponibles',
        'order_number' => 'Numéro de commande',
        'order_date' => 'Date de commande',

        // Status
        'status' => 'Statut',
        'action' => 'Actions',
        'actions' => 'Actions',
        'st_pending' => 'En attente d\'un chauffeur',
        'st_accepted' => 'En cours de livraison',
        'st_delivered' => 'Livré',
        'st_cancelled' => 'Annulé',

        // PIN
        'pin_label' => 'Code de livraison',
        'pin_code' => 'Code PIN',
        'pin_note' => 'Donnez ce code au chauffeur uniquement à la réception',
        'pin_warning' => 'Ne partagez ce code qu\'à la réception de votre commande',
        'enter_pin' => 'Entrez le code de livraison',

        // Driver
        'driver_accept' => 'Accepter',
        'driver_pickup' => 'Récupéré',
        'accept_order' => 'Accepter la commande',
        'driver_cost' => 'Coût',
        'cost_per_order' => 'Coût de la commande',
        'verify_fin' => 'Terminer la livraison',
        'verify_ph' => 'PIN',
        'finish_delivery' => 'Terminer la livraison',
        'my_deliveries' => 'Mes livraisons',
        'accepted_orders' => 'Commandes acceptées',

        // Errors
        'err_low_bal' => 'Votre solde est insuffisant pour accepter de nouvelles commandes',
        'err_auth' => 'Nom d\'utilisateur ou mot de passe incorrect',
        'err_banned' => 'Votre compte a été suspendu. Contactez l\'administration',
        'err_pin' => 'Code de livraison incorrect',
        'err_pin_format' => 'Le code de livraison doit être composé de 4 chiffres',
        'err_username_short' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères',
        'err_password_short' => 'Le mot de passe doit contenir au moins 4 caractères',
        'err_password_mismatch' => 'Les mots de passe ne correspondent pas',
        'err_username_exists' => 'Ce nom d\'utilisateur est déjà utilisé',
        'err_register' => 'Échec de la création du compte. Réessayez',
        'err_order_taken' => 'Cette commande a été acceptée par un autre chauffeur',
        'err_general' => 'Une erreur s\'est produite. Réessayez',
        'err_invalid_order' => 'Commande invalide',
        'err_order_not_found' => 'Commande introuvable',
        'err_not_your_order' => 'Cette commande ne vous est pas assignée',
        'err_order_not_accepted' => 'La commande doit être acceptée pour la récupérer',
        'err_pickup_failed' => 'Échec de la mise à jour du statut. Réessayez',
        'err_order_status' => 'Impossible de terminer la commande dans son état actuel',

        // Success
        'success_add' => 'Votre commande a été publiée avec succès',
        'success_acc' => 'Commande acceptée avec succès',
        'success_fin' => 'Commande livrée avec succès. Excellent travail!',
        'success_register' => 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter',
        'success_profile' => 'Votre profil a été mis à jour avec succès',
        'success_order_cancelled' => 'Commande annulée avec succès',
        'success_user_added' => 'Utilisateur ajouté avec succès',
        'success_user_updated' => 'Utilisateur mis à jour avec succès',
        'success_user_deleted' => 'Utilisateur supprimé avec succès',
        'success_points_added' => 'Points ajoutés avec succès',

        // Empty states
        'empty_list' => 'Aucune commande pour le moment',
        'no_orders' => 'Aucune commande',
        'no_pending_orders' => 'Aucune commande disponible pour le moment',
        'no_users' => 'Aucun utilisateur',
        'check_back_later' => 'Revenez plus tard',

        // Admin
        'admin_panel' => 'Panneau d\'administration',
        'manage_users' => 'Gérer les clients',
        'manage_drivers' => 'Gérer les chauffeurs',
        'manage_orders' => 'Gérer les commandes',
        'add_user' => 'Ajouter un utilisateur',
        'edit_user' => 'Modifier l\'utilisateur',
        'add_order' => 'Ajouter une commande',
        'edit_order' => 'Modifier la commande',
        'add_points' => 'Ajouter des points',
        'recharge_points' => 'Recharger les points',

        // User fields
        'username' => 'Nom d\'utilisateur',
        'password' => 'Mot de passe',
        'new_password' => 'Nouveau mot de passe',
        'current_password' => 'Mot de passe actuel',
        'confirm_new_password' => 'Confirmer le nouveau mot de passe',
        'role' => 'Rôle',
        'user_type' => 'Type d\'utilisateur',

        // Roles
        'admin' => 'Administrateur',
        'driver' => 'Chauffeur',
        'customer' => 'Client',
        'drivers' => 'Chauffeurs',
        'customers' => 'Clients',

        // Status labels
        'active' => 'Actif',
        'banned' => 'Banni',
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'offline_warning' => 'Vous êtes hors ligne',
        'offline_warning_desc' => 'Passez en ligne pour recevoir de nouvelles commandes',

        // Actions
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'confirm' => 'Confirmer',
        'close' => 'Fermer',
        'back' => 'Retour',
        'submit' => 'Soumettre',
        'ban' => 'Bannir',
        'unban' => 'Débannir',
        'cancel_order' => 'Annuler la commande',
        'delete_order' => 'Supprimer la commande',
        'view_details' => 'Voir les détails',

        // Statistics
        'total_users' => 'Total des utilisateurs',
        'total_orders' => 'Total des commandes',
        'total_drivers' => 'Total des chauffeurs',
        'total_customers' => 'Total des clients',
        'active_drivers' => 'Chauffeurs actifs',
        'pending_orders' => 'Commandes en attente',
        'completed_orders' => 'Commandes terminées',
        'statistics' => 'Statistiques',

        // Order fields
        'customer_name' => 'Nom du client',
        'assign_driver' => 'Assigner un chauffeur',
        'no_driver' => 'Sans chauffeur',
        'select_driver' => 'Sélectionner un chauffeur',
        'select_status' => 'Sélectionner le statut',

        // Settings
        'save_changes' => 'Enregistrer les modifications',
        'leave_empty_password' => 'Laissez vide pour conserver le mot de passe actuel',
        'profile_updated' => 'Profil mis à jour',
        'change_password' => 'Changer le mot de passe',
        'account_settings' => 'Paramètres du compte',
        'personal_info' => 'Informations personnelles',
        'security' => 'Sécurité',

        // Notifications
        'new_order_alert' => 'Nouvelle commande!',
        'order_status_changed' => 'Le statut de votre commande a été mis à jour',
        'notification' => 'Notification',
        'notifications' => 'Notifications',
        'new_notification' => 'Nouvelle notification',

        // Confirmations
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer?',
        'confirm_cancel' => 'Voulez-vous annuler cette commande?',
        'confirm_ban' => 'Voulez-vous bannir cet utilisateur?',
        'confirm_accept' => 'Voulez-vous accepter cette commande?',
        'action_irreversible' => 'Cette action est irréversible',

        // Misc
        'loading' => 'Chargement...',
        'please_wait' => 'Veuillez patienter...',
        'search' => 'Rechercher',
        'filter' => 'Filtrer',
        'refresh' => 'Actualiser',
        'date' => 'Date',
        'time' => 'Heure',
        'created_at' => 'Date de création',
        'updated_at' => 'Date de mise à jour',
        'id' => 'ID',
        'details' => 'Détails',
        'amount' => 'Montant',
        'select' => 'Sélectionner',
        'optional' => 'Optionnel',
        'required' => 'Requis',
        'demo_accounts' => 'Comptes de démonstration',
        'all_rights' => 'Tous droits réservés',
        'copyright' => 'Droits d\'auteur',

        // Time
        'now' => 'Maintenant',
        'today' => 'Aujourd\'hui',
        'yesterday' => 'Hier',
        'minutes_ago' => 'Il y a quelques minutes',
        'hours_ago' => 'Il y a quelques heures',
        'days_ago' => 'Il y a quelques jours',

        // Enhanced v2.0
        'serial_no' => 'Numéro de série',
        'user_id' => 'ID utilisateur',
        'profile_picture' => 'Photo de profil',
        'upload_photo' => 'Télécharger une photo',
        'change_photo' => 'Changer la photo',
        'remove_photo' => 'Supprimer la photo',
        'phone_required' => 'Numéro de téléphone requis pour continuer',
        'phone_not_verified' => 'Veuillez ajouter votre numéro de téléphone pour continuer',
        'verify_phone' => 'Vérifier le téléphone',
        'phone_verified' => 'Téléphone vérifié',
        'add_phone_first' => 'Ajoutez d\'abord votre numéro de téléphone',

        // Driver Verification
        'driver_verified' => 'Chauffeur vérifié',
        'driver_not_verified' => 'Votre compte doit être vérifié par l\'administrateur avant d\'accepter des commandes',
        'pending_verification' => 'En attente de vérification',
        'verified' => 'Vérifié',
        'verification' => 'Vérification',
        'verify_driver' => 'Vérifier le chauffeur',
        'unverify' => 'Annuler la vérification',
        'confirm_verify' => 'Voulez-vous vérifier ce chauffeur?',
        'confirm_unverify' => 'Voulez-vous annuler la vérification?',
        'driver_verified_success' => 'Chauffeur vérifié avec succès! Il peut maintenant accepter des commandes.',
        'driver_unverified' => 'Vérification du chauffeur annulée.',

        // Phone Registration (Mauritania: 8 digits starting with 2, 3, or 4)
        'phone_example' => '2XXXXXXX',
        'phone_format_hint' => '8 chiffres commençant par 2, 3 ou 4',
        'err_phone_invalid' => 'Le téléphone doit être composé de 8 chiffres commençant par 2, 3 ou 4',
        'err_phone_exists' => 'Ce numéro de téléphone est déjà enregistré. Veuillez vous connecter.',
        'demo_phone_login' => 'Comptes de démonstration (Téléphone / Mot de passe):',
        'profile_completed' => 'Profil mis à jour avec succès!',

        // Driver Enhanced
        'go_online' => 'Passer en ligne',
        'go_offline' => 'Passer hors ligne',
        'you_are_online' => 'Vous êtes en ligne et recevrez de nouvelles commandes',
        'you_are_offline' => 'Vous êtes hors ligne',
        'you_are_offline_no_orders' => 'Vous êtes hors ligne et ne recevrez pas de nouvelles commandes',
        'earnings' => 'Gains',
        'my_earnings' => 'Mes gains',
        'today_earnings' => 'Gains d\'aujourd\'hui',
        'week_earnings' => 'Gains de la semaine',
        'month_earnings' => 'Gains du mois',
        'completed_deliveries' => 'Livraisons terminées',
        'active_deliveries' => 'Livraisons actives',
        'my_rating' => 'Ma note',
        'max_orders_reached' => 'Vous avez atteint le maximum de commandes actives',

        // Client Enhanced
        'active_orders' => 'Commandes actives',
        'track_order' => 'Suivre la commande',
        'order_history' => 'Historique des commandes',
        'no_history' => 'Pas d\'historique de commandes',
        'history_empty' => 'Les commandes terminées apparaîtront ici',
        'rate_driver' => 'Évaluer le chauffeur',
        'rate_delivery' => 'Évaluer la livraison',
        'your_rating' => 'Votre évaluation',
        'leave_comment' => 'Laisser un commentaire',
        'submit_rating' => 'Soumettre l\'évaluation',
        'thanks_for_rating' => 'Merci pour votre évaluation!',

        // Order Status Enhanced
        'st_picked_up' => 'Récupéré',
        'finding_driver' => 'Recherche d\'un chauffeur...',
        'driver_assigned' => 'Chauffeur assigné',
        'driver_on_way' => 'Chauffeur en route',
        'driver_arrived' => 'Chauffeur arrivé',
        'package_picked' => 'Colis récupéré! Entrez le code PIN pour terminer la livraison',
        'on_the_way' => 'En route vers vous',

        // Tracking
        'live_tracking' => 'Suivi en direct',
        'distance' => 'Distance',
        'eta' => 'Temps estimé',
        'km' => 'km',
        'min' => 'min',
        'call_driver' => 'Appeler le chauffeur',
        'message_driver' => 'Message au chauffeur',

        // Stats
        'this_week' => 'Cette semaine',
        'this_month' => 'Ce mois',
        'orders_count' => 'Nombre de commandes',
        'delivery_count' => 'Nombre de livraisons',

        // Help & Footer
        'need_help' => 'Besoin d\'aide?',
        'contact_us' => 'Contactez-nous',
        'connect_with_us' => 'Connectez avec nous',
        'call_us' => 'Appelez-nous',
        'fast_delivery' => 'Service de livraison rapide et fiable pour tous vos besoins.',
        'available_24_7' => 'Disponible 24h/24',
        'location_mauritania' => 'Nouakchott, Mauritanie',
        'secure_service' => 'Service sécurisé',

        // GPS & Location
        'gps_status' => 'Statut GPS',
        'gps_enabled' => 'GPS activé',
        'gps_disabled' => 'GPS désactivé',
        'enable_gps' => 'Activer le GPS',
        'disable_gps' => 'Désactiver le GPS',
        'turn_on_gps' => 'Allumer le GPS',
        'gps_required' => 'La localisation GPS est requise pour que les chauffeurs vous trouvent',
        'gps_driver_note' => 'Activez le GPS pour voir les commandes à proximité (7 km)',
        'enable_gps_first' => 'Activez d\'abord le GPS',
        'no_nearby_orders' => 'Aucune commande à proximité (7 km)',
        'geolocation_not_supported' => 'Votre navigateur ne prend pas en charge la géolocalisation',
        'location_error' => 'Erreur de localisation',
        'location_denied' => 'Accès à la localisation refusé. Veuillez activer le GPS.',
        'click_gps' => 'Cliquez sur GPS pour définir votre position',
        'pickup_location' => 'Lieu de ramassage',
        'updating_location' => 'Mise à jour de la position...',
        'location_updated' => 'Position mise à jour',
        'gps_accuracy' => 'Précision GPS',
        'last_update' => 'Dernière mise à jour',
        'new_order_nearby' => 'Nouvelle commande à proximité!',
        'accept' => 'Accepter',
        'decline' => 'Refuser',
        'no_phone' => 'Pas de téléphone',
        'update_order' => 'Mettre à jour la commande',

        // Zone System
        'pickup_zone' => 'Zone de ramassage',
        'dropoff_zone' => 'Zone de livraison',
        'select_zone' => 'Sélectionner la zone',
        'delivery_price' => 'Prix de livraison',
        'price' => 'Prix',
        'mru' => 'MRU',
        'from_zone' => 'De',
        'to_zone' => 'À',
        'zone_required' => 'Veuillez sélectionner les zones de ramassage et de livraison',
        'address_placeholder' => 'Adresse détaillée (optionnel)',
        
        // Order Summary
        'order_summary' => 'Résumé de la commande',
        'delivery_price' => 'Prix de livraison',
        'discount' => 'Réduction',
        'total_price' => 'Total',
        'subtotal' => 'Sous-total',

        // Working Zones (Driver)
        'working_zones' => 'Zones de travail',
        'working_zones_note' => 'Sélectionnez les zones où vous souhaitez recevoir des commandes. Laissez vide pour recevoir des commandes de toutes les zones.',
        'select_working_zones' => 'Sélectionner les zones de travail',
        'no_working_zones' => 'Aucune zone de travail sélectionnée',
        'no_working_zones_tip' => 'Conseil: Définissez vos zones de travail dans les paramètres pour recevoir uniquement les commandes de vos zones préférées.',

        // Enhanced Admin Statistics
        'new_this_month' => 'Nouveau ce mois',
        'in_progress' => 'En cours',
        'orders' => 'Commandes',
        'revenue' => 'Revenus',
        'delivery_value' => 'Valeur de livraison',
        'performance' => 'Performance',
        'cancelled' => 'Annulé',
        'avg_delivery_time' => 'Temps de livraison moyen',
        'total_delivery_value' => 'Valeur totale',
        'new_users_today' => 'Nouveaux utilisateurs aujourd\'hui',
        'today_delivery_value' => 'Valeur d\'aujourd\'hui',
        'top_drivers' => 'Meilleurs chauffeurs',
        'popular_zones' => 'Zones populaires',
        'no_data' => 'Aucune donnée',
        'pending' => 'En attente',
        
        // Driver Info Display
        'driver_info' => 'Informations du chauffeur',
        'driver_phone' => 'Téléphone du chauffeur',
        'contact_driver' => 'Contacter le chauffeur',
        'driver_rating' => 'Évaluation du chauffeur',
        'order_driver' => 'Chauffeur assigné',
        
        // Missing Admin UI Translations
        'select_all' => 'Tout sélectionner',
        'bulk_recharge' => 'Recharge en masse',
        'promo_codes' => 'Codes promotionnels',
        'create_promo' => 'Créer un code promo',
        'edit_promo' => 'Modifier le code promo',
        'no_promo_codes' => 'Aucun code promo. Créez-en un pour commencer!',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer?',
        'delete_permanently' => 'Supprimer définitivement',
        'confirm_action' => 'Confirmer',
        'success_rate' => 'Taux de réussite',
        'today_orders' => 'Commandes d\'aujourd\'hui',
        'customers' => 'Clients',
        'confirm_recharge' => 'Confirmer la recharge',
        'verify' => 'Vérifier',
        'receiving_orders' => 'Réception de commandes',
        'tap_to_go_online' => 'Appuyez pour passer en ligne',
        'your_balance' => 'Votre solde',
        'whatsapp_recharge' => 'Recharger via WhatsApp',
        'recharge_note' => 'Contactez le support pour ajouter du crédit à votre compte',
        'rating' => 'Évaluation',
        'hello' => 'Bonjour',
        'start_your_day' => 'Commencez votre journée avec énergie!',
        'what_need_today' => 'De quoi avez-vous besoin aujourd\'hui?',
        'phone_auto_verify' => 'Le téléphone est automatiquement vérifié lors de l\'ajout',
        'order_details_placeholder' => 'Décrivez ce que vous voulez faire livrer...',
        'enter_promo_code' => 'Entrez le code promo',
        'apply' => 'Appliquer',
        'total_revenue' => 'Revenus totaux',
        'today_revenue' => 'Revenus d\'aujourd\'hui',
        'delivered' => 'Livré',
        'driver_finish' => 'Terminer',
        'order_released' => 'Commande libérée. Points remboursés.',
        'confirm_release' => 'Libérer cette commande? Les points seront remboursés.',
        
        // Driver Tiers
        'your_tier' => 'Votre niveau',
        'new_driver' => 'Nouveau chauffeur',
        'regular_driver' => 'Chauffeur régulier',
        'pro_driver' => 'Chauffeur professionnel',
        'vip_driver' => 'Chauffeur VIP',
        
        // Recharge History
        'recharge_history' => 'Historique des recharges',
        'no_recharge_history' => 'Aucun historique de recharge pour le moment.',
        'previous_balance' => 'Solde précédent',
        'new_balance' => 'Nouveau solde',
        'bulk' => 'En masse',
        'single' => 'Unique',
        'call' => 'Appel',
        
        // Visitor Statistics
        'site_visitors' => 'Visiteurs du site',
        'visitors_today' => 'Visiteurs aujourd\'hui',
        'visitors_this_week' => 'Visiteurs cette semaine',
        'visitors_this_month' => 'Visiteurs ce mois',
        'total_visitors' => 'Total des visiteurs'
    ]
];
$t = $text[$lang];
?>
