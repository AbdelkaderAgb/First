<?php
/**
 * Main Entry Point - Delivery Pro System
 * This file includes all components and renders the views
 */

// Include configuration and database connection
require_once 'config.php';

// Include helper functions and translations
require_once 'functions.php';

// Include authentication logic
require_once 'auth.php';

// Include action handlers
require_once 'actions.php';

// Track visitor (unique per IP per day)
trackVisitor($conn, isset($_SESSION['user']) ? $_SESSION['user']['id'] : null);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?php echo $t['app_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.<?php echo $lang=='ar'?'rtl.':''; ?>min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>


<!-- Notification Toast Container -->
<div id="notificationContainer" class="notification-toast <?php echo $dir == 'rtl' ? 'rtl' : ''; ?>"></div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Order Notification Bubbles Container (for drivers) -->
<?php if(isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'driver'): ?>
<div id="orderBubbleContainer" class="order-notification-container"></div>
<?php endif; ?>

<?php if (!isset($_SESSION['user'])): ?>
    <!-- ================= LOGIN/REGISTER SCREEN ================= -->
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Logo Area -->
            <div class="login-logo-area">
                <img src="logo.png" alt="<?php echo $t['app_name']; ?>" class="login-logo-img" style="height: 120px; width: auto; margin-bottom: 15px;">
                <h1 class="login-app-title"><?php echo $t['app_name']; ?></h1>
                <p class="login-app-subtitle"><?php echo $t['app_desc']; ?></p>
            </div>

            <!-- Language Switcher -->
            <div class="btn-group btn-group-sm lang-switcher mb-4" role="group">
                <a href="?lang=ar" class="btn btn-outline-secondary <?php echo $lang=='ar'?'active':''; ?>">العربية</a>
                <a href="?lang=fr" class="btn btn-outline-secondary <?php echo $lang=='fr'?'active':''; ?>">Français</a>
            </div>

            <?php echo getFlash(); ?>

            <!-- Auth Toggle -->
            <div class="auth-toggle">
                <button type="button" id="loginToggle" class="active" onclick="showAuthForm('login')">
                    <i class="fas fa-sign-in-alt me-2"></i><?php echo $t['login_title']; ?>
                </button>
                <button type="button" id="registerToggle" onclick="showAuthForm('register')">
                    <i class="fas fa-user-plus me-2"></i><?php echo $t['register_title']; ?>
                </button>
            </div>

            <!-- Login Form -->
            <form method="POST" id="loginForm" class="auth-form active" onsubmit="this.querySelector('button').classList.add('loading')">
                <div class="login-input-group">
                    <i class="fa-solid fa-mobile-screen login-input-icon"></i>
                    <input type="tel" name="phone" class="login-form-control" placeholder="<?php echo $t['phone_example'] ?? '2XXXXXXX'; ?>" required inputmode="tel" maxlength="8" pattern="[234][0-9]{7}" style="direction: ltr; text-align: <?php echo ($lang=='ar')?'right':'left'; ?>;">
                </div>

                <div class="login-input-group">
                    <i class="fa-solid fa-lock login-input-icon"></i>
                    <input type="password" name="password" class="login-form-control" placeholder="<?php echo $t['pass_ph']; ?>" required autocomplete="current-password">
                </div>

                <button name="do_login" class="btn-login-main">
                    <?php echo $t['btn_login']; ?> <i class="fas fa-arrow-<?php echo ($lang=='ar')?'left':'right'; ?>" style="margin-<?php echo ($lang=='ar')?'right':'left'; ?>: 8px;"></i>
                </button>
            </form>

            <!-- Register Form -->
            <form method="POST" id="registerForm" class="auth-form" onsubmit="this.querySelector('button').classList.add('loading')">
                <div class="login-input-group">
                    <i class="fa-solid fa-user login-input-icon"></i>
                    <input type="text" name="reg_full_name" class="login-form-control" placeholder="<?php echo $t['full_name_ph']; ?>" required minlength="2">
                </div>

                <div class="login-input-group">
                    <i class="fa-solid fa-mobile-screen login-input-icon"></i>
                    <input type="tel" name="reg_phone" class="login-form-control" placeholder="<?php echo $t['phone_example'] ?? '2XXXXXXX'; ?>" required inputmode="tel" maxlength="8" minlength="8" pattern="[234][0-9]{7}" style="direction: ltr; text-align: <?php echo ($lang=='ar')?'right':'left'; ?>;">
                </div>

                <div class="login-input-group">
                    <i class="fa-solid fa-lock login-input-icon"></i>
                    <input type="password" name="reg_password" class="login-form-control" placeholder="<?php echo $t['pass_ph']; ?>" required minlength="4" autocomplete="new-password">
                </div>

                <div class="login-input-group">
                    <i class="fa-solid fa-lock login-input-icon"></i>
                    <input type="password" name="reg_confirm_password" class="login-form-control" placeholder="<?php echo $t['confirm_pass_ph']; ?>" required autocomplete="new-password">
                </div>

                <button name="do_register" class="btn-register-main">
                    <?php echo $t['btn_register']; ?> <i class="fas fa-user-plus" style="margin-<?php echo ($lang=='ar')?'right':'left'; ?>: 8px;"></i>
                </button>
            </form>

            <!-- Divider -->
            <div class="login-divider"><?php echo $t['need_help'] ?? 'Need help?'; ?></div>

            <!-- Contact Hub -->
            <div class="login-contact-hub">
                <a href="tel:<?php echo str_replace(' ', '', $help_phone); ?>" class="login-social-btn phone" title="<?php echo $t['call_us'] ?? 'Call Us'; ?>">
                    <i class="fa-solid fa-phone"></i>
                </a>

                <a href="https://wa.me/<?php echo $whatsapp_number; ?>" class="login-social-btn whatsapp" target="_blank" title="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>

                <a href="mailto:<?php echo $help_email; ?>" class="login-social-btn email" title="<?php echo $help_email; ?>">
                    <i class="fa-regular fa-envelope"></i>
                </a>
            </div>

            <!-- Footer Text -->
            <div class="login-footer-text" id="loginFooterText">
                <?php echo $t['no_account'] ?? "Don't have an account?"; ?> <a href="#" onclick="showAuthForm('register'); return false;"><?php echo $t['register_title']; ?></a>
            </div>
            <div class="login-footer-text" id="registerFooterText" style="display: none;">
                <?php echo $t['have_account'] ?? 'Already have an account?'; ?> <a href="#" onclick="showAuthForm('login'); return false;"><?php echo $t['login_title']; ?></a>
            </div>

        </div>
    </div>

<?php else:
    $u = $_SESSION['user'];
    $role = $u['role'];
    $uid = $u['id'];
?>
    <!-- ================= DASHBOARD ================= -->
    <nav class="navbar app-navbar sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <img src="logo.png" alt="<?php echo $t['app_name']; ?>" style="height: 60px; width: auto;" onerror="this.style.display='none'">
                <span class="text-primary d-none d-sm-inline"><?php echo $t['app_name']; ?></span>
            </a>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <?php if($role == 'driver'): ?>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill" id="pointsBadge">
                    <i class="fas fa-coins"></i> <span id="currentPoints"><?php echo $u['points'] ?? 0; ?></span>
                </span>
                <?php endif; ?>
                <a href="?settings=1" class="d-flex align-items-center gap-2 text-decoration-none" title="<?php echo $t['settings']; ?>">
                    <div class="profile-avatar avatar-sm avatar-<?php echo $role; ?>">
                        <?php
                        $navAvatarUrl = getAvatarUrl($u);
                        if ($navAvatarUrl): ?>
                            <img src="<?php echo e($navAvatarUrl); ?>" alt="">
                        <?php else: ?>
                            <i class="fas fa-<?php echo $role == 'admin' ? 'crown' : ($role == 'driver' ? 'truck' : 'user'); ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="d-none d-md-block text-end lh-1">
                        <span class="d-block fw-bold small text-dark"><?php echo e($u['full_name'] ?: $u['username']); ?></span>
                        <span class="role-badge role-badge-<?php echo $role; ?>" style="padding: 2px 8px; font-size: 0.6rem;">
                            <?php echo $t[$role]; ?>
                        </span>
                    </div>
                </a>
                <a href="?logout=1" class="btn btn-light text-danger settings-btn rounded-circle shadow-sm" title="<?php echo $t['logout']; ?>">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <?php echo getFlash(); ?>

        <?php if(isset($_GET['settings'])): ?>
            <!-- ================= SETTINGS/PROFILE PAGE ================= -->
            <div class="row justify-content-center animate-fadeInUp">
                <div class="col-lg-6 col-xl-5">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-user-cog text-primary me-2"></i><?php echo $t['profile']; ?></h5>
                            <a href="index.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fas fa-arrow-<?php echo $dir=='rtl'?'right':'left'; ?> me-1"></i> <?php echo $t['dashboard']; ?>
                            </a>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').classList.add('loading')" id="profileForm">
                                <!-- Hidden input to ensure profile update is triggered even when form is submitted by file upload -->
                                <input type="hidden" name="update_profile" value="1">
                                <!-- Profile Header -->
                                <div class="text-center mb-4">
                                    <label for="avatarInput" class="d-inline-block" style="cursor: pointer;">
                                        <div class="avatar-with-badge">
                                            <div class="profile-avatar avatar-lg avatar-<?php echo $role; ?> mb-3">
                                                <?php
                                                $avatarUrl = getAvatarUrl($u);
                                                if ($avatarUrl): ?>
                                                    <img src="<?php echo e($avatarUrl); ?>" alt="Avatar">
                                                <?php else: ?>
                                                    <i class="fas fa-<?php echo $role == 'admin' ? 'crown' : ($role == 'driver' ? 'truck' : 'user'); ?>"></i>
                                                <?php endif; ?>
                                                <div class="profile-avatar-edit">
                                                    <i class="fas fa-camera"></i> <?php echo $t['change_photo'] ?? 'Change'; ?>
                                                </div>
                                            </div>
                                            <?php if($role == 'driver' && !empty($u['is_verified'])): ?>
                                                <div class="verified-badge" title="<?php echo $t['driver_verified'] ?? 'Verified Driver'; ?>">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            <?php elseif($role == 'admin'): ?>
                                                <div class="verified-badge" style="background: linear-gradient(135deg, #dc2626, #f97316);" title="Admin">
                                                    <i class="fas fa-star"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                    <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="this.form.submit()">

                                    <h5 class="fw-bold mb-1"><?php echo e($u['full_name'] ?: $u['username']); ?></h5>
                                    <span class="role-badge role-badge-<?php echo $role; ?>">
                                        <i class="fas fa-<?php echo $role == 'admin' ? 'crown' : ($role == 'driver' ? 'truck' : 'user'); ?>"></i>
                                        <?php echo $t[$role]; ?>
                                    </span>

                                    <?php if(!empty($u['serial_no'])): ?>
                                    <div class="mt-2">
                                        <span class="serial-badge"><i class="fas fa-id-badge me-1"></i><?php echo e($u['serial_no']); ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($role == 'driver'): ?>
                                    <!-- Driver Verification Status -->
                                    <?php if(!empty($u['is_verified'])): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-success px-3 py-2"><i class="fas fa-certificate me-1"></i> <?php echo $t['driver_verified'] ?? 'Verified Driver'; ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-clock me-1"></i> <?php echo $t['pending_verification'] ?? 'Pending Verification'; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-2">
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="fas fa-coins me-1"></i> <?php echo $u['points'] ?? 0; ?> <?php echo $t['pts']; ?>
                                        </span>
                                        <?php if(!empty($u['rating'])): ?>
                                        <span class="badge bg-light text-dark px-3 py-2 ms-1">
                                            <span class="rating-stars"><i class="fas fa-star"></i></span>
                                            <span class="rating-value"><?php echo number_format($u['rating'], 1); ?></span>
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php
                                    // Get driver stats
                                    $driverStats = getDriverStats($conn, $uid);
                                    ?>
                                    <div class="mini-stats">
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $driverStats['total_delivered']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['completed_orders'] ?? 'Completed'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $driverStats['total_earnings']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['total_earnings'] ?? 'Earnings'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $driverStats['this_month']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['this_month'] ?? 'This Month'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $driverStats['active_orders']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['active_orders'] ?? 'Active'; ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($role == 'customer'): ?>
                                    <?php
                                    // Get client stats
                                    $clientStats = getClientStats($conn, $u['id'], $u['username']);
                                    ?>
                                    <div class="mini-stats">
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $clientStats['total_orders']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['total_orders'] ?? 'Total Orders'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $clientStats['delivered']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['delivered'] ?? 'Delivered'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $clientStats['active']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['active_orders'] ?? 'Active'; ?></div>
                                        </div>
                                        <div class="mini-stat">
                                            <div class="mini-stat-value"><?php echo $clientStats['this_month']; ?></div>
                                            <div class="mini-stat-label"><?php echo $t['this_month'] ?? 'This Month'; ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Personal Info Section -->
                                <div class="section-divider">
                                    <i class="fas fa-id-card me-2"></i> <?php echo $t['personal_info']; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['full_name_ph']; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="full_name" class="form-control" value="<?php echo e($u['full_name']); ?>" placeholder="<?php echo $t['full_name_ph']; ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary">
                                        <?php echo $t['phone_ph']; ?>
                                        <?php if(isPhoneVerified($u)): ?>
                                            <span class="phone-verified ms-2"><i class="fas fa-check-circle"></i> <?php echo $t['phone_verified'] ?? 'Verified'; ?></span>
                                        <?php elseif(!empty($u['phone'])): ?>
                                            <span class="phone-not-verified ms-2"><i class="fas fa-exclamation-circle"></i> <?php echo $t['phone_not_verified'] ?? 'Not Verified'; ?></span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                        <input type="tel" name="phone" class="form-control" value="<?php echo e($u['phone']); ?>" placeholder="<?php echo $t['phone_ph']; ?>">
                                    </div>
                                    <small class="text-muted"><?php echo $t['phone_auto_verify'] ?? 'Phone is auto-verified when added'; ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['email_ph']; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control" value="<?php echo e($u['email']); ?>" placeholder="<?php echo $t['email_ph']; ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['address']; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                        <input type="text" name="profile_address" class="form-control" value="<?php echo e($u['address']); ?>" placeholder="<?php echo $t['address']; ?>">
                                    </div>
                                </div>

                                <?php if($role == 'driver'): ?>
                                <!-- Working Zones Section (Driver Only) -->
                                <div class="section-divider">
                                    <i class="fas fa-map-marked-alt me-2"></i> <?php echo $t['working_zones'] ?? 'Working Zones'; ?>
                                </div>

                                <div class="alert alert-info border-0 rounded-3 mb-3">
                                    <small><i class="fas fa-info-circle me-1"></i> <?php echo $t['working_zones_note'] ?? 'Select the zones where you want to receive orders. Leave empty to receive orders from all zones.'; ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['select_working_zones'] ?? 'Select Working Zones'; ?></label>
                                    <?php
                                    $userWorkingZones = !empty($u['working_zones']) ? explode(',', $u['working_zones']) : [];
                                    ?>
                                    <div class="working-zones-grid">
                                        <?php foreach($zones as $key => $name): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="working_zones[]" value="<?php echo e($key); ?>" id="zone_<?php echo e($key); ?>" <?php echo in_array($key, $userWorkingZones) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="zone_<?php echo e($key); ?>">
                                                <?php echo ($lang == 'ar') ? $name : $key; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Security Section -->
                                <div class="section-divider">
                                    <i class="fas fa-shield-alt me-2"></i> <?php echo $t['security']; ?>
                                </div>

                                <div class="alert alert-light border-0 bg-light rounded-3 mb-3">
                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> <?php echo $t['leave_empty_password']; ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['new_password']; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                                        <input type="password" name="new_password" class="form-control" minlength="4" placeholder="<?php echo $t['new_password']; ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-secondary"><?php echo $t['confirm_new_password']; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                                        <input type="password" name="confirm_new_password" class="form-control" placeholder="<?php echo $t['confirm_new_password']; ?>">
                                    </div>
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $t['save_changes']; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($role == 'admin'): ?>
            <!-- ================= ADMIN DASHBOARD ================= -->

            <!-- Statistics -->
            <?php
            // Enhanced Statistics Queries
            $totalCustomers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='customer'")->fetchColumn();
            $totalDrivers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver'")->fetchColumn();
            $activeDrivers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND status='active'")->fetchColumn();
            $verifiedDrivers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND is_verified=1")->fetchColumn();
            $onlineDrivers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND is_online=1 AND status='active'")->fetchColumn();
            $bannedDrivers = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND status='banned'")->fetchColumn();

            $totalOrders = $conn->query("SELECT COUNT(*) FROM orders1")->fetchColumn();
            $pendingOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE status='pending'")->fetchColumn();
            $activeOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE status IN ('accepted', 'picked_up')")->fetchColumn();
            $deliveredOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE status='delivered'")->fetchColumn();
            $cancelledOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE status='cancelled'")->fetchColumn();

            $todayOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            $todayDelivered = $conn->query("SELECT COUNT(*) FROM orders1 WHERE DATE(delivered_at) = CURDATE() AND status='delivered'")->fetchColumn();
            $todayCancelled = $conn->query("SELECT COUNT(*) FROM orders1 WHERE DATE(cancelled_at) = CURDATE() AND status='cancelled'")->fetchColumn();
            $todayPending = $conn->query("SELECT COUNT(*) FROM orders1 WHERE DATE(created_at) = CURDATE() AND status='pending'")->fetchColumn();

            // Weekly Statistics
            $weekOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
            $weekDelivered = $conn->query("SELECT COUNT(*) FROM orders1 WHERE YEARWEEK(delivered_at, 1) = YEARWEEK(CURDATE(), 1) AND status='delivered'")->fetchColumn();
            $weekRevenue = $conn->query("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE YEARWEEK(delivered_at, 1) = YEARWEEK(CURDATE(), 1) AND status='delivered'")->fetchColumn();

            // Monthly Statistics
            $monthOrders = $conn->query("SELECT COUNT(*) FROM orders1 WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
            $monthDelivered = $conn->query("SELECT COUNT(*) FROM orders1 WHERE MONTH(delivered_at) = MONTH(CURDATE()) AND YEAR(delivered_at) = YEAR(CURDATE()) AND status='delivered'")->fetchColumn();
            $monthRevenue = $conn->query("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE MONTH(delivered_at) = MONTH(CURDATE()) AND YEAR(delivered_at) = YEAR(CURDATE()) AND status='delivered'")->fetchColumn();

            // New Users Statistics
            $newCustomersToday = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='customer' AND DATE(created_at) = CURDATE()")->fetchColumn();
            $newDriversToday = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND DATE(created_at) = CURDATE()")->fetchColumn();
            $newCustomersWeek = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='customer' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
            $newDriversWeek = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
            $newCustomersMonth = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='customer' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
            $newDriversMonth = $conn->query("SELECT COUNT(*) FROM users1 WHERE role='driver' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();

            $totalRevenue = $conn->query("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE status='delivered'")->fetchColumn();
            $todayRevenue = $conn->query("SELECT COALESCE(SUM(points_cost), 0) FROM orders1 WHERE DATE(delivered_at) = CURDATE() AND status='delivered'")->fetchColumn();
            
            // Total Delivery Value (sum of delivery prices)
            $totalDeliveryValue = $conn->query("SELECT COALESCE(SUM(delivery_price), 0) FROM orders1 WHERE status='delivered'")->fetchColumn();
            $todayDeliveryValue = $conn->query("SELECT COALESCE(SUM(delivery_price), 0) FROM orders1 WHERE DATE(delivered_at) = CURDATE() AND status='delivered'")->fetchColumn();
            $weekDeliveryValue = $conn->query("SELECT COALESCE(SUM(delivery_price), 0) FROM orders1 WHERE YEARWEEK(delivered_at, 1) = YEARWEEK(CURDATE(), 1) AND status='delivered'")->fetchColumn();
            $monthDeliveryValue = $conn->query("SELECT COALESCE(SUM(delivery_price), 0) FROM orders1 WHERE MONTH(delivered_at) = MONTH(CURDATE()) AND YEAR(delivered_at) = YEAR(CURDATE()) AND status='delivered'")->fetchColumn();
            
            // Top Performing Drivers
            $topDrivers = $conn->query("SELECT u.id, u.full_name, u.username, u.phone, u.rating, u.avatar_url, COUNT(o.id) as order_count, COALESCE(SUM(o.points_cost), 0) as total_earnings
                FROM users1 u 
                LEFT JOIN orders1 o ON u.id = o.driver_id AND o.status = 'delivered'
                WHERE u.role = 'driver'
                GROUP BY u.id
                ORDER BY order_count DESC
                LIMIT 5")->fetchAll();
            
            // Most Active Zones
            $topZones = $conn->query("SELECT pickup_zone, COUNT(*) as order_count FROM orders1 WHERE pickup_zone IS NOT NULL AND pickup_zone != '' GROUP BY pickup_zone ORDER BY order_count DESC LIMIT 5")->fetchAll();
            
            // Average delivery time (in minutes) - for delivered orders
            $avgDeliveryTime = $conn->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, accepted_at, delivered_at)) FROM orders1 WHERE status='delivered' AND accepted_at IS NOT NULL AND delivered_at IS NOT NULL")->fetchColumn();
            $avgDeliveryTime = $avgDeliveryTime ? round($avgDeliveryTime) : 0;
            
            // Visitor Statistics
            $visitorStats = getVisitorStats($conn);
            ?>

            <!-- Enhanced Statistics Grid -->
            <div class="row g-3 mb-4">
                <!-- User Statistics -->
                <div class="col-6 col-lg-3">
                    <div class="ultra-card stat-card-enhanced">
                        <div class="card-inner text-center">
                            <div class="stat-icon-circle bg-primary-soft mb-2">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                            <h3 class="stat-number mb-1"><?php echo number_format($totalCustomers); ?></h3>
                            <small class="text-muted text-uppercase fw-bold"><?php echo $t['customers'] ?? 'Customers'; ?></small>
                            <div class="mt-2">
                                <span class="badge bg-info" title="<?php echo $t['new_this_month'] ?? 'New this month'; ?>">+<?php echo $newCustomersMonth; ?> <?php echo $t['this_month'] ?? 'month'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="ultra-card stat-card-enhanced">
                        <div class="card-inner text-center">
                            <div class="stat-icon-circle bg-info-soft mb-2">
                                <i class="fas fa-motorcycle text-info"></i>
                            </div>
                            <h3 class="stat-number mb-1"><?php echo number_format($totalDrivers); ?></h3>
                            <small class="text-muted text-uppercase fw-bold"><?php echo $t['drivers'] ?? 'Drivers'; ?></small>
                            <div class="mt-2">
                                <span class="badge bg-success" title="<?php echo $t['online'] ?? 'Online'; ?>"><?php echo $onlineDrivers; ?> <?php echo $t['online'] ?? 'Online'; ?></span>
                                <span class="badge bg-primary" title="<?php echo $t['verified'] ?? 'Verified'; ?>"><?php echo $verifiedDrivers; ?> <?php echo $t['verified'] ?? 'Verified'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Statistics -->
                <div class="col-6 col-lg-3">
                    <div class="ultra-card stat-card-enhanced">
                        <div class="card-inner text-center">
                            <div class="stat-icon-circle bg-success-soft mb-2">
                                <i class="fas fa-box text-success"></i>
                            </div>
                            <h3 class="stat-number mb-1"><?php echo number_format($totalOrders); ?></h3>
                            <small class="text-muted text-uppercase fw-bold"><?php echo $t['total_orders'] ?? 'Total Orders'; ?></small>
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark"><?php echo $pendingOrders; ?> <?php echo $t['pending'] ?? 'Pending'; ?></span>
                                <span class="badge bg-info"><?php echo $activeOrders; ?> <?php echo $t['in_progress'] ?? 'Active'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="ultra-card stat-card-enhanced">
                        <div class="card-inner text-center">
                            <div class="stat-icon-circle bg-warning-soft mb-2">
                                <i class="fas fa-chart-line text-warning"></i>
                            </div>
                            <h3 class="stat-number mb-1"><?php echo number_format($todayOrders); ?></h3>
                            <small class="text-muted text-uppercase fw-bold"><?php echo $t['today_orders'] ?? 'Today\'s Orders'; ?></small>
                            <div class="mt-2">
                                <span class="badge bg-success"><?php echo $todayDelivered; ?> <?php echo $t['delivered'] ?? 'Delivered'; ?></span>
                                <span class="badge bg-warning text-dark"><?php echo $todayPending; ?> <?php echo $t['pending'] ?? 'Pending'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics Row -->
            <div class="row g-3 mb-4">
                <!-- Weekly Stats -->
                <div class="col-md-4">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-calendar-week text-primary me-2"></i><?php echo $t['this_week'] ?? 'This Week'; ?></h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['orders'] ?? 'Orders'; ?></span>
                                <strong><?php echo number_format($weekOrders); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['delivered'] ?? 'Delivered'; ?></span>
                                <strong class="text-success"><?php echo number_format($weekDelivered); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['revenue'] ?? 'Revenue'; ?></span>
                                <strong class="text-primary"><?php echo number_format($weekRevenue); ?> pts</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><?php echo $t['delivery_value'] ?? 'Delivery Value'; ?></span>
                                <strong class="text-info"><?php echo number_format($weekDeliveryValue); ?> MRU</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Stats -->
                <div class="col-md-4">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-calendar-alt text-success me-2"></i><?php echo $t['this_month'] ?? 'This Month'; ?></h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['orders'] ?? 'Orders'; ?></span>
                                <strong><?php echo number_format($monthOrders); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['delivered'] ?? 'Delivered'; ?></span>
                                <strong class="text-success"><?php echo number_format($monthDelivered); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['revenue'] ?? 'Revenue'; ?></span>
                                <strong class="text-primary"><?php echo number_format($monthRevenue); ?> pts</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><?php echo $t['delivery_value'] ?? 'Delivery Value'; ?></span>
                                <strong class="text-info"><?php echo number_format($monthDeliveryValue); ?> MRU</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Stats -->
                <div class="col-md-4">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie text-warning me-2"></i><?php echo $t['performance'] ?? 'Performance'; ?></h6>
                            <?php $successRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['success_rate'] ?? 'Success Rate'; ?></span>
                                <strong class="text-success"><?php echo $successRate; ?>%</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['cancelled'] ?? 'Cancelled'; ?></span>
                                <strong class="text-danger"><?php echo number_format($cancelledOrders); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo $t['avg_delivery_time'] ?? 'Avg Delivery Time'; ?></span>
                                <strong class="text-info"><?php echo $avgDeliveryTime; ?> <?php echo $t['min'] ?? 'min'; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><?php echo $t['total_delivery_value'] ?? 'Total Value'; ?></span>
                                <strong class="text-primary"><?php echo number_format($totalDeliveryValue); ?> MRU</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1"><?php echo $t['total_revenue'] ?? 'Total Revenue'; ?></small>
                                    <h4 class="mb-0 text-success"><?php echo number_format($totalRevenue); ?> <small class="text-muted">pts</small></h4>
                                </div>
                                <div class="stat-icon-circle bg-success-soft">
                                    <i class="fas fa-coins text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1"><?php echo $t['today_revenue'] ?? 'Today\'s Revenue'; ?></small>
                                    <h4 class="mb-0 text-primary"><?php echo number_format($todayRevenue); ?> <small class="text-muted">pts</small></h4>
                                </div>
                                <div class="stat-icon-circle bg-primary-soft">
                                    <i class="fas fa-calendar-day text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1"><?php echo $t['new_users_today'] ?? 'New Users Today'; ?></small>
                                    <h4 class="mb-0 text-info"><?php echo $newCustomersToday + $newDriversToday; ?></h4>
                                </div>
                                <div class="stat-icon-circle bg-info-soft">
                                    <i class="fas fa-user-plus text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1"><?php echo $t['today_delivery_value'] ?? 'Today\'s Value'; ?></small>
                                    <h4 class="mb-0 text-warning"><?php echo number_format($todayDeliveryValue); ?> <small class="text-muted">MRU</small></h4>
                                </div>
                                <div class="stat-icon-circle bg-warning-soft">
                                    <i class="fas fa-money-bill-wave text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor Statistics Row -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-eye text-info me-2"></i><?php echo $t['site_visitors'] ?? 'Site Visitors'; ?></h6>
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), rgba(6, 182, 212, 0.2));">
                                        <div class="stat-icon-circle bg-info-soft mx-auto mb-2" style="width: 40px; height: 40px;">
                                            <i class="fas fa-clock text-info" style="font-size: 1rem;"></i>
                                        </div>
                                        <h4 class="mb-0 text-info"><?php echo number_format($visitorStats['today']); ?></h4>
                                        <small class="text-muted"><?php echo $t['visitors_today'] ?? 'Today'; ?></small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(88, 75, 246, 0.1), rgba(88, 75, 246, 0.2));">
                                        <div class="stat-icon-circle bg-primary-soft mx-auto mb-2" style="width: 40px; height: 40px;">
                                            <i class="fas fa-calendar-week text-primary" style="font-size: 1rem;"></i>
                                        </div>
                                        <h4 class="mb-0 text-primary"><?php echo number_format($visitorStats['this_week']); ?></h4>
                                        <small class="text-muted"><?php echo $t['visitors_this_week'] ?? 'This Week'; ?></small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(0, 200, 81, 0.1), rgba(0, 200, 81, 0.2));">
                                        <div class="stat-icon-circle bg-success-soft mx-auto mb-2" style="width: 40px; height: 40px;">
                                            <i class="fas fa-calendar-alt text-success" style="font-size: 1rem;"></i>
                                        </div>
                                        <h4 class="mb-0 text-success"><?php echo number_format($visitorStats['this_month']); ?></h4>
                                        <small class="text-muted"><?php echo $t['visitors_this_month'] ?? 'This Month'; ?></small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.2));">
                                        <div class="stat-icon-circle bg-warning-soft mx-auto mb-2" style="width: 40px; height: 40px;">
                                            <i class="fas fa-globe text-warning" style="font-size: 1rem;"></i>
                                        </div>
                                        <h4 class="mb-0 text-warning"><?php echo number_format($visitorStats['total']); ?></h4>
                                        <small class="text-muted"><?php echo $t['total_visitors'] ?? 'Total Visitors'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers Row -->
            <div class="row g-3 mb-4">
                <!-- Top Drivers -->
                <div class="col-md-6">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-trophy text-warning me-2"></i><?php echo $t['top_drivers'] ?? 'Top Drivers'; ?></h6>
                            <?php if(empty($topDrivers)): ?>
                            <p class="text-muted text-center mb-0"><?php echo $t['no_data'] ?? 'No data available'; ?></p>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach($topDrivers as $index => $driver): 
                                    $driverAvatarUrl = getAvatarUrl($driver);
                                ?>
                                <div class="list-group-item d-flex align-items-center gap-3 px-0 border-0">
                                    <span class="badge bg-<?php echo $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'light text-dark'); ?> rounded-circle" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;"><?php echo $index + 1; ?></span>
                                    <div class="profile-avatar avatar-sm avatar-driver">
                                        <?php if($driverAvatarUrl): ?>
                                            <img src="<?php echo e($driverAvatarUrl); ?>" alt="">
                                        <?php else: ?>
                                            <?php echo getUserInitials($driver); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo e($driver['full_name'] ?? $driver['username']); ?></div>
                                        <small class="text-muted"><?php echo $driver['order_count']; ?> <?php echo $t['orders'] ?? 'orders'; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-warning"><i class="fas fa-star"></i> <?php echo number_format($driver['rating'], 1); ?></div>
                                        <small class="text-muted"><?php echo number_format($driver['total_earnings']); ?> pts</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Zones -->
                <div class="col-md-6">
                    <div class="ultra-card">
                        <div class="card-inner">
                            <h6 class="fw-bold mb-3"><i class="fas fa-map-marked-alt text-primary me-2"></i><?php echo $t['popular_zones'] ?? 'Popular Zones'; ?></h6>
                            <?php if(empty($topZones)): ?>
                            <p class="text-muted text-center mb-0"><?php echo $t['no_data'] ?? 'No data available'; ?></p>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach($topZones as $index => $zone): ?>
                                <div class="list-group-item d-flex align-items-center justify-content-between px-0 border-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-<?php echo $index == 0 ? 'primary' : 'light text-dark'; ?>"><?php echo $index + 1; ?></span>
                                        <span><?php echo ($lang == 'ar' && isset($zones[$zone['pickup_zone']])) ? e($zones[$zone['pickup_zone']]) : e($zone['pickup_zone']); ?></span>
                                    </div>
                                    <span class="badge bg-success"><?php echo $zone['order_count']; ?> <?php echo $t['orders'] ?? 'orders'; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Tabs -->
            <ul class="nav nav-tabs mb-4 flex-nowrap overflow-auto" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#customers"><i class="fas fa-users"></i> <span class="d-none d-sm-inline"><?php echo $t['manage_users']; ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#drivers"><i class="fas fa-motorcycle"></i> <span class="d-none d-sm-inline"><?php echo $t['manage_drivers']; ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#orders"><i class="fas fa-box"></i> <span class="d-none d-sm-inline"><?php echo $t['manage_orders']; ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#points"><i class="fas fa-coins"></i> <span class="d-none d-sm-inline"><?php echo $t['add_points']; ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#recharge-history"><i class="fas fa-history"></i> <span class="d-none d-sm-inline"><?php echo $t['recharge_history'] ?? 'Recharge History'; ?></span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#promo-codes"><i class="fas fa-tag"></i> <span class="d-none d-sm-inline"><?php echo $t['promo_codes'] ?? 'Promo Codes'; ?></span></a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- CUSTOMERS TAB -->
                <div class="tab-pane fade show active" id="customers">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0"><i class="fas fa-users text-primary"></i> <?php echo $t['manage_users']; ?></h5>
                            <button class="btn btn-sm btn-primary" onclick="showAddUserModal('customer')">
                                <i class="fas fa-plus"></i> <?php echo $t['add_user']; ?>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="admin-cards-grid">
                                <?php
                                $users = $conn->query("SELECT * FROM users1 WHERE role='customer' ORDER BY id DESC");
                                if($users->rowCount() == 0): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p><?php echo $t['no_users'] ?? 'No customers yet'; ?></p>
                                </div>
                                <?php else: while($user = $users->fetch()):
                                    $userAvatarUrl = getAvatarUrl($user);
                                ?>
                                <div class="ultra-card admin-card">
                                    <div class="card-inner">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="profile-avatar avatar-sm avatar-customer">
                                                <?php if($userAvatarUrl): ?>
                                                    <img src="<?php echo e($userAvatarUrl); ?>" alt="">
                                                <?php else: ?>
                                                    <i class="fas fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?php echo e($user['full_name'] ?? $user['username']); ?></h6>
                                                        <small class="text-muted">@<?php echo e($user['username']); ?> #<?php echo $user['id']; ?></small>
                                                    </div>
                                                    <?php if($user['status'] == 'active'): ?>
                                                        <span class="badge bg-success"><?php echo $t['active']; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><?php echo $t['banned']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if(!empty($user['phone'])): ?>
                                                <div class="mb-2">
                                                    <a href="tel:+222<?php echo $user['phone']; ?>" class="text-primary small">
                                                        <i class="fas fa-phone me-1"></i>+222 <?php echo e($user['phone']); ?>
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                                <div class="d-flex gap-2 mt-2">
                                                    <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                        <i class="fas fa-edit me-1"></i><?php echo $t['edit'] ?? 'Edit'; ?>
                                                    </button>
                                                    <a href="?toggle_ban=<?php echo $user['id']; ?>" class="btn btn-sm btn-<?php echo $user['status']=='active'?'warning':'success'; ?>" onclick="return confirm('<?php echo $t['confirm_action'] ?? 'Confirm?'; ?>')">
                                                        <i class="fas fa-<?php echo $user['status']=='active'?'ban':'check'; ?>"></i>
                                                    </a>
                                                    <a href="?delete_user=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?php echo $t['delete_permanently'] ?? 'Delete permanently?'; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DRIVERS TAB -->
                <div class="tab-pane fade" id="drivers">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <h5 class="mb-0"><i class="fas fa-motorcycle text-info"></i> <?php echo $t['manage_drivers']; ?></h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllDrivers" onclick="toggleAllDrivers(this)">
                                    <label class="form-check-label small" for="selectAllDrivers">
                                        <?php echo $t['select_all'] ?? 'Select All'; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-success" onclick="showBulkRechargeModal()" id="bulkRechargeBtn" style="display:none;">
                                    <i class="fas fa-coins"></i> <?php echo $t['bulk_recharge'] ?? 'Bulk Recharge'; ?>
                                </button>
                                <button class="btn btn-sm btn-info text-white" onclick="showAddUserModal('driver')">
                                    <i class="fas fa-plus"></i> <?php echo $t['add_user']; ?>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="admin-cards-grid">
                                <?php
                                $drivers = $conn->query("SELECT * FROM users1 WHERE role='driver' ORDER BY id DESC");
                                if($drivers->rowCount() == 0): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-motorcycle fa-2x mb-2"></i>
                                    <p><?php echo $t['no_drivers'] ?? 'No drivers yet'; ?></p>
                                </div>
                                <?php else: while($driver = $drivers->fetch()):
                                    $driverAvatarUrl = getAvatarUrl($driver);
                                    $isVerified = !empty($driver['is_verified']);
                                ?>
                                <div class="ultra-card admin-card">
                                    <div class="card-inner">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input driver-checkbox" type="checkbox" value="<?php echo $driver['id']; ?>" id="driver<?php echo $driver['id']; ?>" onchange="updateBulkRechargeButton()">
                                            </div>
                                            <div class="avatar-with-badge">
                                                <div class="profile-avatar avatar-sm avatar-driver">
                                                    <?php if($driverAvatarUrl): ?>
                                                        <img src="<?php echo e($driverAvatarUrl); ?>" alt="">
                                                    <?php else: ?>
                                                        <?php echo getUserInitials($driver); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if($isVerified): ?>
                                                    <div class="verified-badge verified-badge-sm" title="<?php echo $t['driver_verified'] ?? 'Verified'; ?>">
                                                        <i class="fas fa-check"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?php echo e($driver['full_name'] ?? $driver['username']); ?></h6>
                                                        <small class="text-muted">@<?php echo e($driver['username']); ?></small>
                                                        <?php if(!empty($driver['serial_no'])): ?>
                                                        <br><small class="text-primary fw-bold"><?php echo e($driver['serial_no']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php if($driver['status'] == 'active'): ?>
                                                            <span class="badge bg-success mb-1"><?php echo $t['active']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger mb-1"><?php echo $t['banned']; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-coins me-1"></i><?php echo $driver['points'] ?? 0; ?> pts</span>
                                                    <?php if($isVerified): ?>
                                                        <span class="badge bg-success"><i class="fas fa-certificate me-1"></i><?php echo $t['verified'] ?? 'Verified'; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i><?php echo $t['pending_verification'] ?? 'Pending'; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if(!empty($driver['phone'])): ?>
                                                <div class="mb-2">
                                                    <a href="tel:+222<?php echo $driver['phone']; ?>" class="text-primary small">
                                                        <i class="fas fa-phone me-1"></i>+222 <?php echo e($driver['phone']); ?>
                                                        <?php if(!empty($driver['phone_verified'])): ?>
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    <a href="?toggle_verify=<?php echo $driver['id']; ?>" class="btn btn-sm btn-<?php echo $isVerified ? 'success' : 'outline-success'; ?>" onclick="return confirm('<?php echo $isVerified ? ($t['confirm_unverify'] ?? 'Remove verification?') : ($t['confirm_verify'] ?? 'Verify this driver?'); ?>')">
                                                        <i class="fas fa-<?php echo $isVerified ? 'certificate' : 'user-check'; ?> me-1"></i><?php echo $isVerified ? ($t['verified'] ?? 'Verified') : ($t['verify'] ?? 'Verify'); ?>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($driver)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?toggle_ban=<?php echo $driver['id']; ?>" class="btn btn-sm btn-<?php echo $driver['status']=='active'?'warning':'success'; ?>" onclick="return confirm('<?php echo $t['confirm_action'] ?? 'Confirm?'; ?>')">
                                                        <i class="fas fa-<?php echo $driver['status']=='active'?'ban':'check'; ?>"></i>
                                                    </a>
                                                    <a href="?delete_user=<?php echo $driver['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?php echo $t['delete_permanently'] ?? 'Delete permanently?'; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ORDERS TAB -->
                <div class="tab-pane fade" id="orders">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0"><i class="fas fa-box text-success"></i> <?php echo $t['manage_orders']; ?></h5>
                            <button class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                                <i class="fas fa-plus"></i> <?php echo $t['add_order']; ?>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="admin-cards-grid">
                                <?php
                                $orders = $conn->query("SELECT o.*, u.username as driver_name FROM orders1 o LEFT JOIN users1 u ON o.driver_id=u.id ORDER BY o.id DESC LIMIT 100");
                                if($orders->rowCount() == 0): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2"></i>
                                    <p><?php echo $t['no_orders'] ?? 'No orders yet'; ?></p>
                                </div>
                                <?php else: while($order = $orders->fetch()):
                                    $st = $order['status'] ?? 'pending';
                                    $statusTagClass = ($st == 'pending') ? 'tag-pending' : (($st == 'accepted') ? 'tag-accepted' : (($st == 'picked_up') ? 'tag-picked' : (($st == 'cancelled') ? 'badge-cancelled' : 'tag-delivered')));
                                ?>
                                <div class="ultra-card admin-card">
                                    <div class="card-inner">
                                        <!-- Card Header -->
                                        <div class="c-header">
                                            <div class="price-tag">#<?php echo $order['id']; ?></div>
                                            <div class="tag-new <?php echo $statusTagClass; ?>">
                                                <i class="fas fa-<?php echo getStatusIcon($st); ?>"></i>
                                                <?php echo $t['st_'.$st] ?? ucfirst($st); ?>
                                            </div>
                                        </div>

                                        <!-- Route Visual -->
                                        <div class="route-row">
                                            <div class="visual-connector">
                                                <div class="dot-circle dot-p"></div>
                                                <div class="line-dashed"></div>
                                                <div class="dot-circle dot-d"></div>
                                            </div>
                                            <div class="text-info-route">
                                                <div>
                                                    <div class="loc-title"><?php echo e($order['details'] ?? ''); ?></div>
                                                    <div class="loc-sub"><?php echo e($order['customer_name'] ?? ''); ?></div>
                                                </div>
                                                <div>
                                                    <div class="loc-title"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo e($order['address'] ?? ''); ?></div>
                                                    <?php if(!empty($order['client_phone'])): ?>
                                                    <div class="loc-sub">
                                                        <a href="tel:+222<?php echo e($order['client_phone']); ?>" class="text-primary">
                                                            <i class="fas fa-phone me-1"></i>+222 <?php echo e($order['client_phone']); ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Meta Info -->
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <span class="badge bg-light text-dark border"><i class="fas fa-key me-1"></i>PIN: <?php echo $order['delivery_code'] ?? '----'; ?></span>
                                            <?php if(!empty($order['driver_name'])): ?>
                                            <span class="badge bg-info text-white"><i class="fas fa-motorcycle me-1"></i><?php echo e($order['driver_name']); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="editOrder(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                                <i class="fas fa-edit me-1"></i><?php echo $t['edit'] ?? 'Edit'; ?>
                                            </button>
                                            <?php if($st == 'pending' || $st == 'accepted'): ?>
                                            <a href="?cancel_order=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('<?php echo $t['confirm_cancel'] ?? 'Cancel this order?'; ?>')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete_order=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?php echo $t['delete_permanently'] ?? 'Delete permanently?'; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- POINTS TAB -->
                <div class="tab-pane fade" id="points">
                    <div class="card content-card" style="max-width: 500px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-coins text-warning"></i> <?php echo $t['add_points']; ?></h5>

                            <!-- Search Box -->
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-search me-1"></i> <?php echo $t['search'] ?? 'Search'; ?> (ID / <?php echo $t['serial_no'] ?? 'Serial No.'; ?>)</label>
                                <input type="text" id="driverSearchInput" class="form-control" placeholder="<?php echo $t['search'] ?? 'Search'; ?>..." onkeyup="filterDrivers()">
                            </div>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['driver'] ?? 'Driver'; ?></label>
                                    <select name="driver_id" id="driverSelect" class="form-select" required>
                                        <option value=""><?php echo $t['select_driver'] ?? 'Select Driver'; ?></option>
                                        <?php
                                        $ds = $conn->query("SELECT id, serial_no, username, full_name, phone, points FROM users1 WHERE role='driver' ORDER BY username");
                                        while($d=$ds->fetch()) {
                                            $displayName = $d['full_name'] ?: $d['username'];
                                            $serialNo = $d['serial_no'] ?: 'N/A';
                                            echo "<option value='{$d['id']}' data-serial='{$serialNo}' data-phone='{$d['phone']}'>[{$serialNo}] {$displayName} ({$d['points']} pts)</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label"><?php echo $t['amount'] ?? 'Amount'; ?></label>
                                    <input type="number" name="amount" class="form-control" placeholder="20" min="1" required>
                                </div>
                                <button name="recharge" class="btn btn-warning w-100 fw-bold">
                                    <i class="fas fa-plus"></i> <?php echo $t['add_points'] ?? 'Add Points'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- RECHARGE HISTORY TAB -->
                <div class="tab-pane fade" id="recharge-history">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-history text-info"></i> <?php echo $t['recharge_history'] ?? 'Recharge History'; ?></h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo $t['date'] ?? 'Date'; ?></th>
                                            <th><?php echo $t['driver'] ?? 'Driver'; ?></th>
                                            <th><?php echo $t['amount'] ?? 'Amount'; ?></th>
                                            <th><?php echo $t['previous_balance'] ?? 'Previous'; ?></th>
                                            <th><?php echo $t['new_balance'] ?? 'New Balance'; ?></th>
                                            <th><?php echo $t['type'] ?? 'Type'; ?></th>
                                            <th><?php echo $t['admin'] ?? 'Admin'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            $recharge_history = $conn->query("
                                                SELECT rh.*, 
                                                       d.full_name as driver_name, d.serial_no as driver_serial, d.username as driver_username,
                                                       a.full_name as admin_name, a.username as admin_username
                                                FROM recharge_history rh
                                                LEFT JOIN users1 d ON rh.driver_id = d.id
                                                LEFT JOIN users1 a ON rh.admin_id = a.id
                                                ORDER BY rh.created_at DESC
                                                LIMIT 100
                                            ");
                                            if($recharge_history->rowCount() == 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                                    <?php echo $t['no_recharge_history'] ?? 'No recharge history yet.'; ?>
                                                </td>
                                            </tr>
                                            <?php else: while($rh = $recharge_history->fetch()): ?>
                                            <tr>
                                                <td class="small"><?php echo fmtDate($rh['created_at']); ?></td>
                                                <td>
                                                    <strong><?php echo e($rh['driver_name'] ?? $rh['driver_username']); ?></strong>
                                                    <?php if(!empty($rh['driver_serial'])): ?>
                                                    <br><small class="text-primary"><?php echo e($rh['driver_serial']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-success">+<?php echo $rh['amount']; ?> <?php echo $t['pts'] ?? 'pts'; ?></span></td>
                                                <td><?php echo $rh['previous_balance']; ?> <?php echo $t['pts'] ?? 'pts'; ?></td>
                                                <td><strong class="text-success"><?php echo $rh['new_balance']; ?> <?php echo $t['pts'] ?? 'pts'; ?></strong></td>
                                                <td>
                                                    <?php if($rh['recharge_type'] == 'bulk'): ?>
                                                    <span class="badge bg-info"><?php echo $t['bulk'] ?? 'Bulk'; ?></span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo $t['single'] ?? 'Single'; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="small"><?php echo e($rh['admin_name'] ?? $rh['admin_username']); ?></td>
                                            </tr>
                                            <?php endwhile; endif;
                                        } catch (PDOException $e) { ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                                    <?php echo $t['no_recharge_history'] ?? 'No recharge history yet.'; ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PROMO CODES TAB -->
                <div class="tab-pane fade" id="promo-codes">
                    <div class="card content-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0"><i class="fas fa-tag text-success"></i> <?php echo $t['promo_codes'] ?? 'Promo Codes'; ?></h5>
                            <button class="btn btn-sm btn-success" onclick="showAddPromoCodeModal()">
                                <i class="fas fa-plus"></i> <?php echo $t['create_promo'] ?? 'Create Promo Code'; ?>
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo $t['code'] ?? 'Code'; ?></th>
                                            <th><?php echo $t['discount'] ?? 'Discount'; ?></th>
                                            <th><?php echo $t['usage'] ?? 'Usage'; ?></th>
                                            <th><?php echo $t['validity'] ?? 'Validity'; ?></th>
                                            <th><?php echo $t['status'] ?? 'Status'; ?></th>
                                            <th><?php echo $t['actions'] ?? 'Actions'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $promo_codes = $conn->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
                                        if($promo_codes->rowCount() == 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-tag fa-2x mb-2 d-block"></i>
                                                <?php echo $t['no_promo_codes'] ?? 'No promo codes yet. Create one to get started!'; ?>
                                            </td>
                                        </tr>
                                        <?php else: while($promo = $promo_codes->fetch()):
                                            $is_expired = $promo['valid_until'] && strtotime($promo['valid_until']) < time();
                                            $is_maxed = $promo['max_uses'] && $promo['used_count'] >= $promo['max_uses'];
                                        ?>
                                        <tr>
                                            <td><strong class="text-primary"><?php echo e($promo['code']); ?></strong></td>
                                            <td>
                                                <?php if($promo['discount_type'] == 'percentage'): ?>
                                                    <span class="badge bg-info"><?php echo $promo['discount_value']; ?>%</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $promo['discount_value']; ?> MRU</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $promo['used_count']; ?> / <?php echo $promo['max_uses'] ?? '∞'; ?>
                                            </td>
                                            <td class="small">
                                                <?php if($promo['valid_from']): ?>
                                                    <div>From: <?php echo date('Y-m-d', strtotime($promo['valid_from'])); ?></div>
                                                <?php endif; ?>
                                                <?php if($promo['valid_until']): ?>
                                                    <div>Until: <?php echo date('Y-m-d', strtotime($promo['valid_until'])); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!$promo['is_active']): ?>
                                                    <span class="badge bg-secondary"><?php echo $t['inactive'] ?? 'Inactive'; ?></span>
                                                <?php elseif($is_expired): ?>
                                                    <span class="badge bg-danger"><?php echo $t['expired'] ?? 'Expired'; ?></span>
                                                <?php elseif($is_maxed): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $t['max_uses_reached'] ?? 'Max Uses'; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $t['active'] ?? 'Active'; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick='editPromoCode(<?php echo json_encode($promo); ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?toggle_promo=<?php echo $promo['id']; ?>" class="btn btn-outline-<?php echo $promo['is_active'] ? 'warning' : 'success'; ?>">
                                                        <i class="fas fa-<?php echo $promo['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    </a>
                                                    <a href="?delete_promo=<?php echo $promo['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('<?php echo $t['confirm_delete'] ?? 'Delete this promo code?'; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function filterDrivers() {
                    const search = document.getElementById('driverSearchInput').value.toLowerCase();
                    const select = document.getElementById('driverSelect');
                    const options = select.querySelectorAll('option');

                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = '';
                            return;
                        }
                        const text = option.textContent.toLowerCase();
                        const serial = (option.dataset.serial || '').toLowerCase();
                        const phone = (option.dataset.phone || '').toLowerCase();
                        const id = option.value;

                        if (text.includes(search) || serial.includes(search) || phone.includes(search) || id.includes(search)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Auto-select if only one match
                    const visible = Array.from(options).filter(o => o.style.display !== 'none' && o.value !== '');
                    if (visible.length === 1) {
                        select.value = visible[0].value;
                    }
                }
                </script>
            </div>

            <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-plus"></i> <?php echo $t['add_user']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['username']; ?></label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['password']; ?></label>
                                    <input type="text" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['role']; ?></label>
                                    <select name="role" id="addUserRole" class="form-select" required>
                                        <option value="customer"><?php echo $t['customer']; ?></option>
                                        <option value="driver"><?php echo $t['driver']; ?></option>
                                        <option value="admin"><?php echo $t['admin']; ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['points']; ?></label>
                                    <input type="number" name="points" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="admin_add_user" class="btn btn-primary">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> <?php echo $t['edit_user']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="user_id" id="edit_user_id">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['username']; ?></label>
                                    <input type="text" id="edit_username" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['full_name_ph'] ?? 'Full Name'; ?></label>
                                    <input type="text" name="full_name" id="edit_full_name" class="form-control" placeholder="<?php echo $t['full_name_ph'] ?? 'Full Name'; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['phone_ph'] ?? 'Phone'; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">+222</span>
                                        <input type="tel" name="phone" id="edit_phone" class="form-control" placeholder="<?php echo $t['phone_example'] ?? '2XXXXXXX'; ?>" pattern="[234][0-9]{7}" maxlength="8" style="direction: ltr;">
                                    </div>
                                    <small class="text-muted"><?php echo $t['phone_format_hint'] ?? '8 digits starting with 2, 3, or 4'; ?></small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['email_ph'] ?? 'Email'; ?></label>
                                    <input type="email" name="email" id="edit_email" class="form-control" placeholder="<?php echo $t['email_ph'] ?? 'Email'; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['password']; ?> (<?php echo $t['leave_empty_password'] ?? 'leave empty to keep'; ?>)</label>
                                    <input type="text" name="password" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['role']; ?></label>
                                    <select name="role" id="edit_role" class="form-select" required>
                                        <option value="customer"><?php echo $t['customer']; ?></option>
                                        <option value="driver"><?php echo $t['driver']; ?></option>
                                        <option value="admin"><?php echo $t['admin']; ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['points']; ?></label>
                                    <input type="number" name="points" id="edit_points" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="admin_edit_user" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo $t['save']; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bulk Recharge Modal -->
            <div class="modal fade" id="bulkRechargeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-coins text-success"></i> <?php echo $t['bulk_recharge'] ?? 'Bulk Recharge Drivers'; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="selectedDriverCount">0</span> <?php echo $t['drivers_selected'] ?? 'drivers selected'; ?>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo $t['amount_to_add'] ?? 'Amount to Add'; ?></label>
                                    <div class="input-group">
                                        <input type="number" name="bulk_amount" id="bulkAmount" class="form-control form-control-lg" min="1" required placeholder="<?php echo $t['enter_amount'] ?? 'Enter amount'; ?>">
                                        <span class="input-group-text"><?php echo $t['pts'] ?? 'pts'; ?></span>
                                    </div>
                                </div>
                                <input type="hidden" name="driver_ids" id="selectedDriverIds">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="bulk_recharge_drivers" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i><?php echo $t['confirm_recharge'] ?? 'Confirm Recharge'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Promo Code Modal -->
            <div class="modal fade" id="promoCodeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-tag text-success"></i> <span id="promoModalTitle"><?php echo $t['create_promo'] ?? 'Create Promo Code'; ?></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" id="promoCodeForm">
                            <div class="modal-body">
                                <input type="hidden" name="promo_id" id="promoId">

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo $t['code'] ?? 'Code'; ?> *</label>
                                    <input type="text" name="promo_code" id="promoCode" class="form-control text-uppercase" required pattern="[A-Z0-9]+" placeholder="e.g. SUMMER2026" maxlength="50" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                                    <small class="text-muted"><?php echo $t['code_help'] ?? 'Uppercase letters and numbers only'; ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo $t['discount_type'] ?? 'Discount Type'; ?> *</label>
                                    <select name="discount_type" id="discountType" class="form-select" required onchange="updateDiscountLabel()">
                                        <option value="percentage"><?php echo $t['percentage'] ?? 'Percentage'; ?> (%)</option>
                                        <option value="fixed"><?php echo $t['fixed_amount'] ?? 'Fixed Amount'; ?> (MRU)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><span id="discountLabel"><?php echo $t['discount_value'] ?? 'Discount Value'; ?></span> *</label>
                                    <input type="number" name="discount_value" id="discountValue" class="form-control" required min="0" step="0.01" placeholder="e.g. 20">
                                    <small class="text-muted" id="discountHelp"><?php echo $t['percentage_help'] ?? 'Enter percentage (e.g., 20 for 20% off)'; ?></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo $t['max_uses'] ?? 'Maximum Uses'; ?></label>
                                    <input type="number" name="max_uses" id="maxUses" class="form-control" min="1" placeholder="<?php echo $t['unlimited'] ?? 'Leave empty for unlimited'; ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold"><?php echo $t['valid_from'] ?? 'Valid From'; ?></label>
                                        <input type="date" name="valid_from" id="validFrom" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold"><?php echo $t['valid_until'] ?? 'Valid Until'; ?></label>
                                        <input type="date" name="valid_until" id="validUntil" class="form-control">
                                    </div>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                                    <label class="form-check-label" for="isActive">
                                        <?php echo $t['active'] ?? 'Active'; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="save_promo_code" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i><?php echo $t['save'] ?? 'Save'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Order Modal -->
            <div class="modal fade" id="addOrderModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-plus-circle"></i> <?php echo $t['add_order']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['customer_name']; ?></label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['order_details']; ?></label>
                                    <textarea name="details" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label"><?php echo $t['pickup_zone'] ?? 'Pickup Zone'; ?></label>
                                        <select name="pickup_zone" class="form-select" required>
                                            <option value=""><?php echo $t['select_zone'] ?? 'Select zone'; ?></option>
                                            <?php foreach($zones as $key => $name): ?>
                                            <option value="<?php echo e($key); ?>"><?php echo ($lang == 'ar') ? $name : $key; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label"><?php echo $t['dropoff_zone'] ?? 'Dropoff Zone'; ?></label>
                                        <select name="dropoff_zone" class="form-select" required>
                                            <option value=""><?php echo $t['select_zone'] ?? 'Select zone'; ?></option>
                                            <?php foreach($zones as $key => $name): ?>
                                            <option value="<?php echo e($key); ?>"><?php echo ($lang == 'ar') ? $name : $key; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['address']; ?></label>
                                    <input type="text" name="address" class="form-control" placeholder="<?php echo $t['address_placeholder'] ?? 'Optional detailed address'; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['status']; ?></label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending"><?php echo $t['st_pending']; ?></option>
                                        <option value="accepted"><?php echo $t['st_accepted']; ?></option>
                                        <option value="delivered"><?php echo $t['st_delivered']; ?></option>
                                        <option value="cancelled"><?php echo $t['st_cancelled']; ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['assign_driver']; ?> (optional)</label>
                                    <select name="driver_id" class="form-select">
                                        <option value=""><?php echo $t['no_driver']; ?></option>
                                        <?php
                                        $ds = $conn->query("SELECT id, username FROM users1 WHERE role='driver' ORDER BY username");
                                        while($d=$ds->fetch()) {
                                            echo "<option value='{$d['id']}'>{$d['username']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="admin_add_order" class="btn btn-success"><?php echo $t['add_order']; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Order Modal -->
            <div class="modal fade" id="editOrderModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit"></i> <?php echo $t['edit_order']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="order_id" id="edit_order_id">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['customer_name']; ?></label>
                                    <input type="text" name="customer_name" id="edit_order_customer" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['order_details']; ?></label>
                                    <textarea name="details" id="edit_order_details" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['address']; ?></label>
                                    <input type="text" name="address" id="edit_order_address" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['status']; ?></label>
                                    <select name="status" id="edit_order_status" class="form-select" required>
                                        <option value="pending"><?php echo $t['st_pending']; ?></option>
                                        <option value="accepted"><?php echo $t['st_accepted']; ?></option>
                                        <option value="delivered"><?php echo $t['st_delivered']; ?></option>
                                        <option value="cancelled"><?php echo $t['st_cancelled']; ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo $t['assign_driver']; ?></label>
                                    <select name="driver_id" id="edit_order_driver" class="form-select">
                                        <option value=""><?php echo $t['no_driver']; ?></option>
                                        <?php
                                        $ds = $conn->query("SELECT id, username FROM users1 WHERE role='driver' ORDER BY username");
                                        while($d=$ds->fetch()) {
                                            echo "<option value='{$d['id']}'>{$d['username']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $t['cancel']; ?></button>
                                <button type="submit" name="admin_edit_order" class="btn btn-primary"><i class="fas fa-save me-1"></i><?php echo $t['update_order'] ?? $t['save']; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= BARQ ULTRA PREMIUM DASHBOARD ================= -->
            <?php
            // Get driver stats for driver role
            if($role == 'driver') {
                $driverStats = getDriverStats($conn, $uid);

                // Enhanced driver tier calculation
                $completedOrders = (int)$driverStats['total_orders'];
                $avgRating = (float)$driverStats['rating']; // Use rating from stats (defaults to 5.0)
                $thisMonthOrders = (int)$driverStats['orders_this_month'];
                $isVerified = !empty($u['is_verified']);
                
                // Calculate tier score based on multiple factors
                // - Completed orders (weight: 40%)
                // - Rating (weight: 30%)
                // - Monthly activity (weight: 20%)
                // - Verification status (weight: 10%)
                
                $orderScore = min($completedOrders / 100, 1) * 40; // Max 40 points at 100+ orders
                $ratingScore = ($avgRating / 5) * 30; // Max 30 points at 5.0 rating
                $activityScore = min($thisMonthOrders / 20, 1) * 20; // Max 20 points at 20+ orders this month
                $verificationScore = $isVerified ? 10 : 0; // 10 points if verified
                
                $totalScore = $orderScore + $ratingScore + $activityScore + $verificationScore;

                // Determine tier based on total score and minimum requirements
                $priorityTier = 4; // Default: New driver
                $priorityBadge = $t['new_driver'] ?? 'New Driver';
                $priorityColor = 'secondary';
                $priorityIcon = 'fa-user';

                if ($totalScore >= 70 && $completedOrders >= 50 && $avgRating >= 4.5 && $isVerified) {
                    // VIP: High score, 50+ orders, 4.5+ rating, verified
                    $priorityTier = 1;
                    $priorityBadge = $t['vip_driver'] ?? 'VIP Driver';
                    $priorityColor = 'warning';
                    $priorityIcon = 'fa-crown';
                } elseif ($totalScore >= 50 && $completedOrders >= 20 && $avgRating >= 4.0) {
                    // Pro: Good score, 20+ orders, 4.0+ rating
                    $priorityTier = 2;
                    $priorityBadge = $t['pro_driver'] ?? 'Pro Driver';
                    $priorityColor = 'primary';
                    $priorityIcon = 'fa-medal';
                } elseif ($totalScore >= 25 && $completedOrders >= 5 && $avgRating >= 3.5) {
                    // Regular: Moderate score, 5+ orders, 3.5+ rating
                    $priorityTier = 3;
                    $priorityBadge = $t['regular_driver'] ?? 'Regular Driver';
                    $priorityColor = 'info';
                    $priorityIcon = 'fa-shield';
                }
            } elseif($role == 'customer') {
                $clientStats = getClientStats($conn, $u['id'], $u['username']);
            }
            ?>

            <!-- HEADER SECTION -->
            <section class="header-section">
                <div class="top-bar">
                    <div class="user-info">
                        <div class="avatar-circle <?php echo $role; ?>">
                            <?php
                            $headerAvatarUrl = getAvatarUrl($u);
                            if ($headerAvatarUrl): ?>
                                <img src="<?php echo e($headerAvatarUrl); ?>" alt="">
                            <?php else:
                                echo getUserInitials($u);
                            endif; ?>
                        </div>
                        <div class="greeting">
                            <h3><?php echo $t['hello'] ?? 'مرحباً'; ?> <?php echo e($u['full_name'] ?: $u['username']); ?></h3>
                            <span><?php echo $role == 'driver' ? ($t['start_your_day'] ?? 'ابدأ يومك بنشاط!') : ($t['what_need_today'] ?? 'ماذا تحتاج اليوم؟'); ?></span>
                        </div>
                    </div>
                    <a href="?settings=1" class="notif-btn" title="<?php echo $t['settings']; ?>">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                </div>
            </section>

            <?php if($role == 'driver'): ?>
            <!-- DRIVER STATS SCROLL -->
            <div class="stats-scroll">
                <div class="stat-card-new card-purple">
                    <i class="fa-solid fa-wallet stat-icon-new"></i>
                    <div>
                        <div class="stat-num-new" id="driverPoints"><?php echo number_format($u['points'] ?? 0); ?></div>
                        <div class="stat-label-new"><?php echo $t['balance'] ?? 'الأرباح'; ?> (<?php echo $t['pts'] ?? 'نقطة'; ?>)</div>
                    </div>
                    <div style="position:absolute; top:-10px; left:-10px; width:60px; height:60px; background:rgba(255,255,255,0.1); border-radius:50%;"></div>
                </div>

                <!-- Priority Badge Card -->
                <div class="stat-card-new card-new-white priority-badge-card">
                    <i class="fa-solid <?php echo $priorityIcon; ?> stat-icon-new" style="color:var(--<?php echo $priorityColor; ?>)"></i>
                    <div>
                        <div class="stat-label-new text-uppercase" style="margin-bottom: 8px;"><?php echo $t['your_tier'] ?? 'Your Tier'; ?></div>
                        <div class="priority-badge-text" style="font-size: 0.85rem; font-weight: 700; color: var(--<?php echo $priorityColor; ?>);">
                            <?php echo $priorityBadge; ?>
                        </div>
                    </div>
                    <?php if($priorityTier == 1): ?>
                        <div class="sparkle-effect"></div>
                    <?php endif; ?>
                </div>

                <div class="stat-card-new card-new-white">
                    <i class="fa-solid fa-star stat-icon-new" style="color:#FFD700"></i>
                    <div>
                        <div class="stat-num-new" style="color:var(--text-main)">
                            <?php
                            $rating = $u['rating'] ?? 0;
                            echo $rating > 0 ? number_format($rating, 1) : '<span style="font-size:0.9rem;">-</span>';
                            ?>
                        </div>
                        <div class="stat-label-new"><?php echo $t['rating'] ?? 'التقييم'; ?></div>
                    </div>
                </div>

                <div class="stat-card-new card-new-white">
                    <i class="fa-solid fa-route stat-icon-new" style="color:var(--success)"></i>
                    <div>
                        <div class="stat-num-new" style="color:var(--text-main)"><?php echo $driverStats['total_delivered']; ?></div>
                        <div class="stat-label-new"><?php echo $t['completed_orders'] ?? 'تم التوصيل'; ?></div>
                    </div>
                </div>

                <div class="stat-card-new card-new-white">
                    <i class="fa-solid fa-clock stat-icon-new" style="color:var(--primary)"></i>
                    <div>
                        <div class="stat-num-new" style="color:var(--text-main)"><?php echo $driverStats['active_orders']; ?></div>
                        <div class="stat-label-new"><?php echo $t['active_orders'] ?? 'طلبات نشطة'; ?></div>
                    </div>
                </div>
            </div>

            <!-- DRIVER CONTROL PANEL -->
            <div class="orders-container mb-3">
                <div class="ultra-card">
                    <div class="card-inner">
                        <div class="row g-3">
                            <!-- Online/Offline Toggle -->
                            <div class="col-12">
                                <div class="driver-control-btn <?php echo ($u['is_online'] ?? 0) ? 'active' : ''; ?>" onclick="document.getElementById('onlineSwitch').checked = !document.getElementById('onlineSwitch').checked; document.getElementById('onlineToggleForm').submit();">
                                    <div class="control-icon <?php echo ($u['is_online'] ?? 0) ? 'online' : 'offline'; ?>">
                                        <i class="fas fa-power-off"></i>
                                    </div>
                                    <div class="control-info">
                                        <span class="control-label"><?php echo ($u['is_online'] ?? 0) ? ($t['online'] ?? 'Online') : ($t['offline'] ?? 'Offline'); ?></span>
                                        <small class="control-hint"><?php echo ($u['is_online'] ?? 0) ? ($t['receiving_orders'] ?? 'Receiving orders') : ($t['tap_to_go_online'] ?? 'Tap to go online'); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php
                            // Show working zones if set
                            $driverWorkingZones = !empty($u['working_zones']) ? explode(',', $u['working_zones']) : [];
                            if (!empty($driverWorkingZones)):
                            ?>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <small class="text-muted"><i class="fas fa-map-marked-alt me-1"></i><?php echo $t['working_zones'] ?? 'Working Zones'; ?>:</small>
                                    <?php foreach($driverWorkingZones as $zone): ?>
                                    <span class="badge bg-primary"><?php echo ($lang == 'ar' && isset($zones[$zone])) ? e($zones[$zone]) : e($zone); ?></span>
                                    <?php endforeach; ?>
                                    <a href="?settings=1" class="btn btn-sm btn-outline-secondary" style="border-radius: 20px; padding: 2px 10px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info mb-0 py-2 px-3" style="border-radius: 12px; border: none;">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php echo $t['no_working_zones_tip'] ?? 'Tip: Set your working zones in settings to receive orders only from your preferred areas.'; ?>
                                        <a href="?settings=1" class="ms-2 fw-bold"><?php echo $t['settings'] ?? 'Settings'; ?></a>
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Online Toggle Form (Hidden) -->
            <form method="POST" id="onlineToggleForm" style="display:none;">
                <input type="checkbox" id="onlineSwitch" name="is_online" value="1" <?php echo ($u['is_online'] ?? 0) ? 'checked' : ''; ?>>
                <input type="hidden" name="toggle_online" value="1">
            </form>

            <?php if(!($u['is_online'] ?? 0)): ?>
            <!-- Offline Warning Banner -->
            <div class="orders-container mb-3">
                <div class="alert alert-warning mb-0 py-3 px-4 d-flex align-items-center justify-content-between" style="border-radius: 16px; border: none;">
                    <div>
                        <i class="fas fa-power-off me-2"></i>
                        <strong><?php echo $t['offline_warning'] ?? 'You are offline'; ?></strong>
                        <p class="mb-0 small"><?php echo $t['offline_warning_desc'] ?? 'Go online to receive new orders'; ?></p>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" onclick="document.getElementById('onlineSwitch').checked = true; document.getElementById('onlineToggleForm').submit();">
                        <i class="fas fa-play me-1"></i><?php echo $t['go_online'] ?? 'Go Online'; ?>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- DRIVER RECHARGE DASHBOARD -->
            <div class="orders-container mb-3">
                <div class="recharge-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="recharge-label"><?php echo $t['your_balance'] ?? 'Your Balance'; ?></div>
                            <div class="recharge-balance"><?php echo number_format($u['points'] ?? 0); ?> <small style="font-size: 0.5em;"><?php echo $t['pts'] ?? 'pts'; ?></small></div>
                        </div>
                        <div class="text-end">
                            <small class="d-block opacity-75"><?php echo $t['cost_per_order'] ?? 'Cost per order'; ?></small>
                            <span class="fw-bold"><?php echo $points_cost_per_order; ?> <?php echo $t['pts'] ?? 'pts'; ?></span>
                        </div>
                    </div>
                    <?php if(($u['points'] ?? 0) < $points_cost_per_order): ?>
                    <div class="alert alert-light mb-3 py-2 px-3" style="background: rgba(255,255,255,0.9); border-radius: 10px;">
                        <small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i><?php echo $t['err_low_bal'] ?? 'Low balance! Recharge to accept orders.'; ?></small>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/<?php echo $whatsapp_number; ?>?text=<?php echo urlencode('مرحبا، أرغب في شحن رصيد' . "\n" . 'المستخدم: ' . ($u['serial_no'] ?? $u['username']) . "\n" . 'الرقم: ' . ($u['phone'] ?? '')); ?>" target="_blank" class="recharge-btn whatsapp flex-grow-1 justify-content-center">
                            <i class="fa-brands fa-whatsapp"></i>
                            <?php echo $t['whatsapp_recharge'] ?? 'WhatsApp to Recharge'; ?>
                        </a>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="opacity-75">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo $t['recharge_note'] ?? 'Contact support to add credits to your account'; ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($role == 'customer'): ?>
            <!-- CUSTOMER STATS SCROLL -->
            <div class="stats-scroll">
                <div class="stat-card-new card-purple">
                    <i class="fa-solid fa-box stat-icon-new"></i>
                    <div>
                        <div class="stat-num-new"><?php echo $clientStats['total_orders']; ?></div>
                        <div class="stat-label-new"><?php echo $t['total_orders'] ?? 'إجمالي الطلبات'; ?></div>
                    </div>
                    <div style="position:absolute; top:-10px; left:-10px; width:60px; height:60px; background:rgba(255,255,255,0.1); border-radius:50%;"></div>
                </div>

                <div class="stat-card-new card-new-white">
                    <i class="fa-solid fa-check-circle stat-icon-new" style="color:var(--success)"></i>
                    <div>
                        <div class="stat-num-new" style="color:var(--text-main)"><?php echo $clientStats['delivered']; ?></div>
                        <div class="stat-label-new"><?php echo $t['delivered'] ?? 'تم التوصيل'; ?></div>
                    </div>
                </div>

                <div class="stat-card-new card-new-white">
                    <i class="fa-solid fa-clock stat-icon-new" style="color:var(--primary)"></i>
                    <div>
                        <div class="stat-num-new" style="color:var(--text-main)"><?php echo $clientStats['active']; ?></div>
                        <div class="stat-label-new"><?php echo $t['active_orders'] ?? 'طلبات نشطة'; ?></div>
                    </div>
                </div>
            </div>

            <!-- NEW ORDER CARD -->
            <div class="orders-container mb-4">
                <div class="ultra-card">
                    <div class="card-inner">
                        <h5 class="fw-bold mb-4">
                            <i class="fas fa-plus-circle text-primary me-2"></i><?php echo $t['new_order']; ?>
                        </h5>
                        <form method="POST" accept-charset="UTF-8" id="newOrderForm">
                            <input type="hidden" name="add_order" value="1">
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-box me-1"></i><?php echo $t['order_details']; ?>
                                </label>
                                <textarea name="details" class="form-control" rows="3" placeholder="<?php echo $t['order_details_placeholder'] ?? 'Describe what you need delivered...'; ?>" required style="border-radius: var(--radius); border: 2px solid var(--gray-200);"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-phone me-1"></i><?php echo $t['phone_ph'] ?? 'Phone'; ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">+222</span>
                                    <input type="tel" name="client_phone" class="form-control"
                                           value="<?php echo e($u['phone'] ?? ''); ?>"
                                           placeholder="<?php echo $t['phone_example'] ?? '2XXXXXXX'; ?>"
                                           pattern="[234][0-9]{7}" maxlength="8" inputmode="tel"
                                           <?php echo !empty($u['phone']) ? '' : 'required'; ?>>
                                </div>
                            </div>

                            <!-- Zone Selection -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1">
                                        <i class="fas fa-map-marker-alt me-1 text-success"></i><?php echo $t['pickup_zone'] ?? 'Pickup Zone'; ?>
                                    </label>
                                    <select name="pickup_zone" id="pickupZone" class="form-select" required onchange="calculateDeliveryPrice()">
                                        <option value=""><?php echo $t['select_zone'] ?? 'Select zone'; ?></option>
                                        <?php foreach($zones as $key => $name): ?>
                                        <option value="<?php echo e($key); ?>"><?php echo ($lang == 'ar') ? $name : $key; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i><?php echo $t['dropoff_zone'] ?? 'Dropoff Zone'; ?>
                                    </label>
                                    <select name="dropoff_zone" id="dropoffZone" class="form-select" required onchange="calculateDeliveryPrice()">
                                        <option value=""><?php echo $t['select_zone'] ?? 'Select zone'; ?></option>
                                        <?php foreach($zones as $key => $name): ?>
                                        <option value="<?php echo e($key); ?>"><?php echo ($lang == 'ar') ? $name : $key; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-home me-1"></i><?php echo $t['address'] ?? 'Address'; ?> <span class="text-muted">(<?php echo $t['optional'] ?? 'Optional'; ?>)</span>
                                </label>
                                <input type="text" name="address" class="form-control" placeholder="<?php echo $t['address_placeholder'] ?? 'Detailed address (optional)'; ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-muted mb-1">
                                    <i class="fas fa-tag me-1 text-success"></i><?php echo $t['promo_code'] ?? 'Promo Code'; ?> <span class="text-muted">(<?php echo $t['optional'] ?? 'Optional'; ?>)</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" name="promo_code" id="promoCodeInput" class="form-control text-uppercase"
                                           placeholder="<?php echo $t['enter_promo_code'] ?? 'Enter promo code'; ?>" maxlength="50">
                                    <button type="button" class="btn btn-outline-success" onclick="validatePromoCode()" id="validatePromoBtn">
                                        <i class="fas fa-check"></i> <?php echo $t['apply'] ?? 'Apply'; ?>
                                    </button>
                                </div>
                                <small id="promoFeedback" class="text-muted"></small>
                            </div>

                            <button type="submit" name="add_order" class="slider-btn-container w-100">
                                <div class="slider-thumb"><i class="fa-solid fa-paper-plane"></i></div>
                                <div class="slider-text"><?php echo $t['btn_publish']; ?></div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TABS FOR FILTERING -->
            <?php 
            $showHistory = isset($_GET['history']) && $_GET['history'] === '1'; 
            $showRecharge = isset($_GET['recharge']) && $_GET['recharge'] === '1';
            $isActive = !$showHistory && !$showRecharge;
            ?>
            <div class="tabs-wrapper">
                <a href="index.php" class="tab-new tab-link <?php echo $isActive ? 'active' : ''; ?>">
                    <?php echo $role == 'driver' ? ($t['active_orders'] ?? 'Active') : ($t['recent_orders'] ?? 'الطلبات'); ?>
                </a>
                <a href="index.php?history=1" class="tab-new tab-link <?php echo $showHistory ? 'active' : ''; ?>">
                    <?php echo $t['order_history'] ?? 'History'; ?>
                </a>
                <?php if($role == 'driver'): ?>
                <a href="index.php?recharge=1" class="tab-new tab-link <?php echo $showRecharge ? 'active' : ''; ?>">
                    <i class="fas fa-history me-1"></i><?php echo $t['recharge_history'] ?? 'Recharge'; ?>
                </a>
                <?php endif; ?>
                <?php if($role == 'driver' && $isActive): ?>
                <span class="badge bg-warning text-dark pulse-badge" id="pendingBadge" style="display:none; margin: auto 0;">0</span>
                <?php endif; ?>
            </div>

            <?php if($role == 'driver' && $showRecharge): ?>
            <!-- RECHARGE HISTORY (Separate Tab) -->
            <div class="orders-container" id="rechargeHistoryContainer">
                <div class="ultra-card">
                    <div class="card-inner">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="fas fa-history text-info me-2"></i><?php echo $t['recharge_history'] ?? 'Recharge History'; ?></h5>
                        </div>
                        <?php
                        try {
                            $driver_recharge_history = $conn->prepare("
                                SELECT rh.*, a.full_name as admin_name, a.username as admin_username
                                FROM recharge_history rh
                                LEFT JOIN users1 a ON rh.admin_id = a.id
                                WHERE rh.driver_id = ?
                                ORDER BY rh.created_at DESC
                                LIMIT 50
                            ");
                            $driver_recharge_history->execute([$uid]);
                            
                            if($driver_recharge_history->rowCount() == 0): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                <h5 class="text-muted"><?php echo $t['no_recharge_history'] ?? 'No recharge history yet.'; ?></h5>
                                <p class="text-muted small mb-0"><?php echo $t['recharge_note'] ?? 'Contact support to add credits to your account'; ?></p>
                            </div>
                            <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php while($drh = $driver_recharge_history->fetch()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 border-bottom py-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-success fs-6">+<?php echo $drh['amount']; ?> <?php echo $t['pts'] ?? 'pts'; ?></span>
                                            <?php if($drh['recharge_type'] == 'bulk'): ?>
                                            <span class="badge bg-info"><?php echo $t['bulk'] ?? 'Bulk'; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i><?php echo fmtDate($drh['created_at']); ?>
                                        </small>
                                        <?php if($drh['admin_name'] || $drh['admin_username']): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user-shield me-1"></i><?php echo $t['admin'] ?? 'Admin'; ?>: <?php echo e($drh['admin_name'] ?? $drh['admin_username']); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-muted small mb-1">
                                            <?php echo $t['previous_balance'] ?? 'Before'; ?>: <?php echo $drh['previous_balance']; ?> <?php echo $t['pts'] ?? 'pts'; ?>
                                        </div>
                                        <div class="text-success fw-bold">
                                            <?php echo $t['new_balance'] ?? 'After'; ?>: <?php echo $drh['new_balance']; ?> <?php echo $t['pts'] ?? 'pts'; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php endif;
                        } catch (PDOException $e) { ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                <h5 class="text-muted"><?php echo $t['no_recharge_history'] ?? 'No recharge history yet.'; ?></h5>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- ORDERS AS ULTRA CARDS -->
            <div class="orders-container" id="ordersContainer">
                <?php
                // Zone-based order filtering (no GPS)
                if($role == 'driver') {
                    if ($showHistory) {
                        // Show driver's delivered and cancelled order history with customer info
                        $sql = "SELECT o.*, c.full_name as client_full_name, c.avatar_url as client_avatar
                                FROM orders1 o
                                LEFT JOIN users1 c ON o.client_id = c.id
                                WHERE o.driver_id = ? AND o.status IN ('delivered', 'cancelled')
                                ORDER BY o.id DESC
                                LIMIT 50";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$uid]);
                    } else {
                        // Driver sees their active orders always
                        // Only show pending orders if driver is ONLINE
                        $isOnline = $u['is_online'] ?? 0;
                        $driverWorkingZones = !empty($u['working_zones']) ? explode(',', $u['working_zones']) : [];
                        
                        // Validate working zones against valid zone list
                        $validZones = array_keys($zones);
                        $driverWorkingZones = array_filter($driverWorkingZones, function($zone) use ($validZones) {
                            return in_array($zone, $validZones);
                        });
                        
                        if ($isOnline && !empty($driverWorkingZones)) {
                            // Online driver with working zones - show pending orders in their zones with customer info
                            $zonePlaceholders = implode(',', array_fill(0, count($driverWorkingZones), '?'));
                            $sql = "SELECT o.*, c.full_name as client_full_name, c.avatar_url as client_avatar
                                    FROM orders1 o
                                    LEFT JOIN users1 c ON o.client_id = c.id
                                    WHERE (o.driver_id = ? AND o.status IN ('accepted', 'picked_up'))
                                    OR (o.status = 'pending' AND (o.pickup_zone IN ($zonePlaceholders) OR o.dropoff_zone IN ($zonePlaceholders)))
                                    ORDER BY CASE WHEN o.driver_id = ? THEN 0 ELSE 1 END, o.id DESC
                                    LIMIT 50";
                            $stmt = $conn->prepare($sql);
                            $params = array_merge([$uid], $driverWorkingZones, $driverWorkingZones, [$uid]);
                            $stmt->execute($params);
                        } elseif ($isOnline) {
                            // Online driver without zones - show all pending orders with customer info
                            $sql = "SELECT o.*, c.full_name as client_full_name, c.avatar_url as client_avatar
                                    FROM orders1 o
                                    LEFT JOIN users1 c ON o.client_id = c.id
                                    WHERE (o.driver_id = ? AND o.status IN ('accepted', 'picked_up'))
                                    OR o.status = 'pending'
                                    ORDER BY CASE WHEN o.driver_id = ? THEN 0 ELSE 1 END, o.id DESC
                                    LIMIT 50";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$uid, $uid]);
                        } else {
                            // Offline driver - only show their active orders (no pending) with customer info
                            $sql = "SELECT o.*, c.full_name as client_full_name, c.avatar_url as client_avatar
                                    FROM orders1 o
                                    LEFT JOIN users1 c ON o.client_id = c.id
                                    WHERE o.driver_id = ? AND o.status IN ('accepted', 'picked_up')
                                    ORDER BY o.id DESC
                                    LIMIT 50";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$uid]);
                        }
                    }
                    $res = $stmt;
                } elseif($role == 'customer') {
                    if ($showHistory) {
                        // Show customer's delivered and cancelled order history
                        $sql = "SELECT o.*, d.full_name as driver_name, d.phone as driver_phone, d.rating as driver_rating, d.is_verified as driver_verified, d.avatar_url as driver_avatar, d.total_orders as driver_total_orders,
                                (SELECT COUNT(*) FROM ratings r WHERE r.order_id = o.id AND r.rater_id = ?) as has_rated
                                FROM orders1 o
                                LEFT JOIN users1 d ON o.driver_id = d.id
                                WHERE (o.customer_name = ? OR o.client_id = ?) AND o.status IN ('delivered', 'cancelled')
                                ORDER BY o.id DESC LIMIT 50";
                    } else {
                        // Show customer's active orders
                        $sql = "SELECT o.*, d.full_name as driver_name, d.phone as driver_phone, d.rating as driver_rating, d.is_verified as driver_verified, d.avatar_url as driver_avatar, d.total_orders as driver_total_orders,
                                (SELECT COUNT(*) FROM ratings r WHERE r.order_id = o.id AND r.rater_id = ?) as has_rated
                                FROM orders1 o
                                LEFT JOIN users1 d ON o.driver_id = d.id
                                WHERE (o.customer_name = ? OR o.client_id = ?) AND o.status IN ('pending', 'accepted', 'picked_up')
                                ORDER BY o.id DESC LIMIT 50";
                    }
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$uid, $u['username'], $uid]);
                    $res = $stmt;
                } else {
                    $sql = "SELECT * FROM orders1 ORDER BY id DESC LIMIT 50";
                    $res = $conn->query($sql);
                }

                if($res->rowCount() == 0): ?>
                <div class="ultra-card">
                    <div class="card-inner text-center py-5">
                        <i class="fas fa-<?php echo $showHistory ? 'clock-rotate-left' : 'box-open'; ?> fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted"><?php echo $showHistory ? ($t['no_history'] ?? 'No order history') : $t['no_orders']; ?></h5>
                        <p class="text-muted small mb-0">
                            <?php echo $showHistory ? ($t['history_empty'] ?? 'Completed orders will appear here') : $t['check_back_later']; ?>
                        </p>
                    </div>
                </div>
                <?php else: while($row = $res->fetch()):
                    $st = $row['status'];
                    $pickupZone = $row['pickup_zone'] ?? '';
                    $dropoffZone = $row['dropoff_zone'] ?? '';
                    $deliveryPrice = $row['delivery_price'] ?? 0;
                    $statusTagClass = ($st == 'pending') ? 'tag-pending' : (($st == 'accepted') ? 'tag-accepted' : (($st == 'picked_up') ? 'tag-picked' : (($st == 'cancelled') ? 'tag-cancelled' : 'tag-delivered')));
                ?>
                <div class="ultra-card">
                    <div class="card-inner">
                        <!-- Card Header -->
                        <div class="c-header">
                            <div class="price-tag">
                                #<?php echo $row['id']; ?>
                            </div>
                            <?php if($role == 'customer' && $st != 'delivered' && $st != 'cancelled' && !empty($row['delivery_code'])): ?>
                            <!-- PIN Code Badge for Customer in Header -->
                            <div class="pin-badge">
                                <i class="fas fa-key"></i>
                                <?php echo $row['delivery_code']; ?>
                            </div>
                            <?php else: ?>
                            <div class="time-tag blue">
                                <i class="fa-regular fa-clock"></i>
                                <?php echo fmtDate($row['created_at']); ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Meta Tags -->
                        <div class="meta-tags">
                            <div class="tag-new <?php echo $statusTagClass; ?>">
                                <i class="fas fa-<?php echo getStatusIcon($st); ?>"></i>
                                <?php echo $t['st_'.$st] ?? ucfirst($st); ?>
                            </div>
                            <?php if($deliveryPrice > 0): ?>
                            <div class="tag-new tag-accepted">
                                <i class="fas fa-money-bill-wave"></i>
                                <?php echo $deliveryPrice; ?> <?php echo $t['mru'] ?? 'MRU'; ?>
                            </div>
                            <?php endif; ?>
                            <?php if(($st == 'accepted' || $st == 'picked_up' || $st == 'delivered') && !empty($row['accepted_at'])): ?>
                            <div class="tag-new tag-time">
                                <i class="fas fa-clock"></i>
                                <?php echo fmtDate($row['accepted_at']); ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if($pickupZone && $dropoffZone): ?>
                        <!-- Zone Info -->
                        <div class="order-zone-info mb-3">
                            <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                <div class="text-center flex-grow-1">
                                    <i class="fas fa-map-marker-alt text-success"></i>
                                    <span class="small fw-bold"><?php echo ($lang == 'ar' && isset($zones[$pickupZone])) ? $zones[$pickupZone] : e($pickupZone); ?></span>
                                </div>
                                <div class="px-2">
                                    <i class="fas fa-arrow-<?php echo $dir == 'rtl' ? 'left' : 'right'; ?> text-muted"></i>
                                </div>
                                <div class="text-center flex-grow-1">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span class="small fw-bold"><?php echo ($lang == 'ar' && isset($zones[$dropoffZone])) ? $zones[$dropoffZone] : e($dropoffZone); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($role == 'driver'): ?>
                        <?php 
                        // Get customer avatar for display
                        $clientAvatar = !empty($row['client_avatar']) ? getAvatarUrl(['avatar_url' => $row['client_avatar']]) : null;
                        $clientDisplayName = !empty($row['client_full_name']) ? $row['client_full_name'] : $row['customer_name'];
                        ?>
                        <!-- Customer Info for Driver -->
                        <div class="customer-info-box mb-3 p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-avatar avatar-sm avatar-customer">
                                    <?php if($clientAvatar): ?>
                                        <img src="<?php echo e($clientAvatar); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark"><?php echo e($clientDisplayName); ?></div>
                                    <?php if($row['client_phone']): ?>
                                    <div class="small text-muted">
                                        <a href="tel:+222<?php echo e($row['client_phone']); ?>" class="text-primary">
                                            <i class="fas fa-phone me-1"></i>+222 <?php echo e($row['client_phone']); ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if($row['client_phone']): ?>
                                <div class="d-flex gap-2">
                                    <a href="tel:+222<?php echo e($row['client_phone']); ?>" class="btn btn-success btn-sm rounded-circle" title="<?php echo $t['call'] ?? 'Call'; ?>">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                    <a href="https://wa.me/<?php echo e($country_code . $row['client_phone']); ?>" target="_blank" class="btn btn-sm rounded-circle" style="background-color: #25D366; color: white;" title="WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Route Visual -->
                        <div class="route-row">
                            <div class="visual-connector">
                                <div class="dot-circle dot-p"></div>
                                <div class="line-dashed"></div>
                                <div class="dot-circle dot-d"></div>
                            </div>
                            <div class="text-info-route">
                                <div>
                                    <div class="loc-title"><?php echo e($row['details']); ?></div>
                                    <?php if($role != 'driver'): ?>
                                    <div class="loc-sub"><?php echo e($row['customer_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="loc-title"><i class="fas fa-map-marker-alt text-danger me-1"></i><?php echo e($row['address']); ?></div>
                                    <?php if($row['client_phone'] && $role != 'driver'): ?>
                                    <div class="loc-sub">
                                        <a href="tel:+222<?php echo e($row['client_phone']); ?>" class="text-primary">
                                            <i class="fas fa-phone me-1"></i>+222 <?php echo e($row['client_phone']); ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if($role == 'customer' && !empty($row['driver_id']) && ($st == 'accepted' || $st == 'picked_up' || $st == 'delivered')): ?>
                        <?php 
                        // Get driver avatar early for use in JSON and display
                        $driverAvatar = !empty($row['driver_avatar']) ? getAvatarUrl(['avatar_url' => $row['driver_avatar']]) : null;
                        ?>
                        <!-- Enhanced Driver Info for Customer (clickable for popup details) -->
                        <div class="driver-info-box mb-3 p-3 bg-light rounded-3 border" style="cursor: pointer;" onclick="showDriverDetails(<?php echo htmlspecialchars(json_encode([
                            'name' => $row['driver_name'] ?? $t['driver'] ?? 'Driver',
                            'phone' => $row['driver_phone'] ?? '',
                            'rating' => $row['driver_rating'] ?? 5.0,
                            'verified' => !empty($row['driver_verified']),
                            'avatar' => $driverAvatar ?? '',
                            'total_deliveries' => $row['driver_total_orders'] ?? 0,
                            'accepted_at' => $row['accepted_at'] ?? '',
                            'status' => $st
                        ]), ENT_QUOTES, 'UTF-8'); ?>)" title="<?php echo $t['view_details'] ?? 'Click for details'; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="driver-avatar-lg position-relative">
                                    <?php if($driverAvatar): ?>
                                        <img src="<?php echo e($driverAvatar); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <i class="fas fa-motorcycle"></i>
                                    <?php endif; ?>
                                    <span class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1" style="font-size: 0.6rem;">
                                        <i class="fas fa-expand-alt text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <strong class="text-dark"><?php echo e($row['driver_name'] ?? ($t['driver'] ?? 'Driver')); ?></strong>
                                        <?php if(!empty($row['driver_verified'])): ?>
                                        <span class="badge bg-success" title="<?php echo $t['driver_verified'] ?? 'Verified'; ?>"><i class="fas fa-check-circle"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($row['driver_rating'])): ?>
                                    <div class="text-warning small mb-1">
                                        <i class="fas fa-star"></i> <?php echo number_format($row['driver_rating'], 1); ?> / 5
                                    </div>
                                    <?php endif; ?>
                                    <div class="small text-muted">
                                        <i class="fas fa-info-circle me-1"></i><?php echo $t['view_details'] ?? 'Tap for details'; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if($st == 'accepted'): ?>
                                    <span class="badge bg-info"><?php echo $t['driver_on_way'] ?? 'On the way'; ?></span>
                                    <?php elseif($st == 'picked_up'): ?>
                                    <span class="badge bg-primary"><?php echo $t['on_the_way'] ?? 'Delivering'; ?></span>
                                    <?php elseif($st == 'delivered'): ?>
                                    <span class="badge bg-success"><?php echo $t['st_delivered'] ?? 'Delivered'; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($role == 'customer' && $st != 'delivered' && $st != 'cancelled'): ?>
                        <!-- PIN Code for Customer -->
                        <div class="alert alert-warning mb-3 py-2">
                            <small class="d-block fw-bold mb-1"><?php echo $t['pin_label']; ?>:</small>
                            <span class="pin-box text-dark fs-5"><?php echo $row['delivery_code']; ?></span>
                            <div class="small text-muted mt-1"><?php echo $t['pin_note']; ?></div>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <?php if($role == 'driver'): ?>

                            <?php if($st == 'pending'): ?>
                            <form method="POST" class="driver-action-form">
                                <input type="hidden" name="oid" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="accept_order" class="slider-btn-container w-100" onclick="return confirm('<?php echo $t['confirm_accept']; ?>\n<?php echo $t['cost_per_order']; ?>: <?php echo $points_cost_per_order; ?> <?php echo $t['pts']; ?>')">
                                    <div class="slider-thumb"><i class="fa-solid fa-check"></i></div>
                                    <div class="slider-text"><?php echo $t['driver_accept']; ?></div>
                                </button>
                            </form>

                            <?php elseif($st == 'accepted' && $row['driver_id'] == $uid): ?>
                            <div class="d-flex gap-2 w-100">
                                <form method="POST" class="flex-grow-1 driver-action-form">
                                    <input type="hidden" name="oid" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="pickup_order" value="1" class="slider-btn-container info w-100">
                                        <div class="slider-thumb"><i class="fa-solid fa-box"></i></div>
                                        <div class="slider-text"><?php echo $t['driver_pickup'] ?? 'Picked Up'; ?></div>
                                    </button>
                                </form>
                                <a href="?driver_cancel=<?php echo $row['id']; ?>" class="btn btn-outline-warning" onclick="return confirm('<?php echo $t['confirm_release'] ?? 'Release this order? Points will be refunded.'; ?>')" style="border-radius: 12px; padding: 12px 16px;">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>

                            <?php elseif($st == 'picked_up' && $row['driver_id'] == $uid): ?>
                            <form method="POST" class="pin-input-row driver-action-form">
                                <input type="hidden" name="oid" value="<?php echo $row['id']; ?>">
                                <input type="text" name="pin" placeholder="<?php echo $t['enter_pin'] ?? 'Enter PIN'; ?>" required pattern="[0-9]{4}" maxlength="4" inputmode="numeric">
                                <button type="submit" name="finish_job" value="1" class="btn btn-success px-4 py-3 fw-bold" style="border-radius: 12px;">
                                    <i class="fas fa-check-double me-1"></i><?php echo $t['driver_finish'] ?? 'Finish'; ?>
                                </button>
                            </form>
                            <?php endif; ?>

                        <?php elseif($role == 'customer'): ?>

                            <?php if($st == 'pending'): ?>
                            <a href="?customer_cancel=<?php echo $row['id']; ?>" class="slider-btn-container muted w-100" onclick="return confirm('<?php echo $t['confirm_cancel'] ?? 'Cancel this order?'; ?>')">
                                <div class="slider-thumb"><i class="fa-solid fa-times"></i></div>
                                <div class="slider-text"><?php echo $t['cancel_order'] ?? 'Cancel Order'; ?></div>
                            </a>
                            <?php elseif($st == 'accepted'): ?>
                            <div class="d-flex gap-2 w-100">
                                <div class="slider-btn-container info flex-grow-1" onclick="showOrderTracking(<?php echo htmlspecialchars(json_encode($row)); ?>)" style="cursor:pointer;">
                                    <div class="slider-thumb"><i class="fa-solid fa-location-dot"></i></div>
                                    <div class="slider-text"><?php echo $t['track_order'] ?? 'Track'; ?></div>
                                </div>
                                <a href="?customer_cancel=<?php echo $row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('<?php echo $t['confirm_cancel'] ?? 'Cancel this order?'; ?>')" style="border-radius: 12px; padding: 12px 16px;">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            <?php elseif($st == 'picked_up'): ?>
                            <div class="slider-btn-container info w-100" onclick="showOrderTracking(<?php echo htmlspecialchars(json_encode($row)); ?>)" style="cursor:pointer;">
                                <div class="slider-thumb"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="slider-text"><?php echo $t['track_order'] ?? 'Track Order'; ?></div>
                            </div>
                            <?php elseif($st == 'delivered' && empty($row['has_rated'])): ?>
                            <form method="POST" class="order-actions-row">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <select name="score" class="form-select" required style="border-radius: 12px;">
                                    <option value=""><?php echo $t['rate_driver'] ?? 'Rate Driver'; ?></option>
                                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                    <option value="4">⭐⭐⭐⭐ (4)</option>
                                    <option value="3">⭐⭐⭐ (3)</option>
                                    <option value="2">⭐⭐ (2)</option>
                                    <option value="1">⭐ (1)</option>
                                </select>
                                <button type="submit" name="submit_rating" class="btn btn-warning fw-bold" style="border-radius: 12px;">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
            <?php endif; ?>

            <!-- GLASS NAVIGATION BAR -->
            <?php if($role == 'driver'): ?>
            <!-- Driver Navigation -->
            <nav class="glass-nav">
                <a href="index.php" class="nav-icon active"><i class="fa-solid fa-house"></i></a>
                <a href="?settings=1" class="nav-icon"><i class="fa-solid fa-chart-simple"></i></a>

                <div class="nav-center-btn <?php echo $u['is_online'] ? 'online' : 'offline'; ?>" onclick="document.getElementById('onlineSwitch').checked = !document.getElementById('onlineSwitch').checked; document.getElementById('onlineToggleForm').submit();" title="<?php echo $u['is_online'] ? ($t['go_offline'] ?? 'Go Offline') : ($t['go_online'] ?? 'Go Online'); ?>">
                    <i class="fa-solid fa-power-off"></i>
                </div>

                <a href="?settings=1" class="nav-icon"><i class="fa-solid fa-gear"></i></a>
                <a href="?settings=1" class="nav-icon"><i class="fa-regular fa-user"></i></a>
            </nav>
            <?php elseif($role == 'customer'): ?>
            <!-- Customer Navigation -->
            <nav class="glass-nav">
                <a href="index.php" class="nav-icon active"><i class="fa-solid fa-house"></i></a>
                <a href="#" class="nav-icon" onclick="document.getElementById('newOrderForm').scrollIntoView({behavior: 'smooth'})"><i class="fa-solid fa-plus"></i></a>

                <div class="nav-center-btn">
                    <i class="fa-solid fa-box"></i>
                </div>

                <a href="?settings=1" class="nav-icon"><i class="fa-solid fa-gear"></i></a>
                <a href="?settings=1" class="nav-icon"><i class="fa-regular fa-user"></i></a>
            </nav>
            <?php else: ?>
            <!-- Admin Navigation -->
            <nav class="glass-nav">
                <a href="index.php" class="nav-icon active"><i class="fa-solid fa-house"></i></a>
                <a href="?settings=1" class="nav-icon"><i class="fa-solid fa-chart-bar"></i></a>

                <div class="nav-center-btn" style="background: linear-gradient(135deg, var(--primary), var(--secondary));">
                    <i class="fa-solid fa-crown"></i>
                </div>

                <a href="?settings=1" class="nav-icon"><i class="fa-solid fa-gear"></i></a>
                <a href="?settings=1" class="nav-icon"><i class="fa-regular fa-user"></i></a>
            </nav>
            <?php endif; ?>

        <?php endif; ?>

        <?php if($role == 'customer'): ?>
        <!-- Order Tracking Modal -->
        <div class="modal fade" id="orderTrackingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-map-marker-alt me-2"></i><?php echo $t['track_order'] ?? 'Track Order'; ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Order Progress -->
                        <div class="order-progress mb-4">
                            <div class="progress-track">
                                <div class="progress-step completed" id="step-pending">
                                    <div class="step-icon"><i class="fas fa-receipt"></i></div>
                                    <div class="step-label"><?php echo $t['st_pending'] ?? 'Pending'; ?></div>
                                </div>
                                <div class="progress-step" id="step-accepted">
                                    <div class="step-icon"><i class="fas fa-truck"></i></div>
                                    <div class="step-label"><?php echo $t['st_accepted'] ?? 'Accepted'; ?></div>
                                </div>
                                <div class="progress-step" id="step-picked_up">
                                    <div class="step-icon"><i class="fas fa-box"></i></div>
                                    <div class="step-label"><?php echo $t['st_picked_up'] ?? 'Picked Up'; ?></div>
                                </div>
                                <div class="progress-step" id="step-delivered">
                                    <div class="step-icon"><i class="fas fa-check-double"></i></div>
                                    <div class="step-label"><?php echo $t['st_delivered'] ?? 'Delivered'; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Driver Info -->
                        <div class="driver-info-card bg-light rounded-3 p-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="driver-avatar-lg" id="tracking-driver-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="mb-0 fw-bold" id="tracking-driver-name">Driver</h6>
                                        <span class="badge bg-success" id="tracking-verified-badge" style="display:none;">
                                            <i class="fas fa-check-circle"></i> <?php echo $t['verified'] ?? 'Verified'; ?>
                                        </span>
                                    </div>
                                    <div class="text-warning small" id="tracking-driver-rating">
                                        <i class="fas fa-star"></i> 5.0
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="#" id="tracking-call-btn" class="btn btn-success btn-sm flex-fill rounded-pill">
                                    <i class="fas fa-phone me-1"></i> <?php echo $t['call_driver'] ?? 'Call'; ?>
                                </a>
                                <a href="#" id="tracking-whatsapp-btn" class="btn btn-sm flex-fill rounded-pill" style="background-color: #25D366; color: white;">
                                    <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="order-details-card border rounded-3 p-3">
                            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-primary me-2"></i><?php echo $t['order_details'] ?? 'Order Details'; ?></h6>
                            <p class="mb-2 small" id="tracking-order-details">-</p>
                            <p class="mb-0 small text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> <span id="tracking-order-address">-</span></p>
                        </div>

                        <!-- Time Info -->
                        <div class="mt-3 text-center">
                            <small class="text-muted" id="tracking-time-info"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Details Popup Modal -->
        <div class="modal fade" id="driverDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                    <div class="modal-body p-0">
                        <!-- Header with gradient background -->
                        <div class="text-center py-4" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                            <div class="position-relative d-inline-block">
                                <div id="driverModalAvatar" class="driver-avatar-lg mx-auto mb-3" style="width: 90px; height: 90px; border: 4px solid white; box-shadow: 0 8px 25px rgba(0,0,0,0.2);">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                                <span id="driverModalVerifiedBadge" class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-1" style="display: none; width: 28px; height: 28px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold text-white mb-1" id="driverModalName">-</h5>
                            <div class="text-white" id="driverModalRating">
                                <i class="fas fa-star text-warning"></i> <span>5.0</span> / 5
                            </div>
                        </div>
                        
                        <!-- Info Cards -->
                        <div class="p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-3 text-center">
                                        <div class="text-primary fw-bold fs-5" id="driverModalDeliveries">0</div>
                                        <div class="small text-muted"><?php echo $t['completed_deliveries'] ?? 'Deliveries'; ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light rounded-3 p-3 text-center">
                                        <div id="driverModalStatus" class="fw-bold fs-6">-</div>
                                        <div class="small text-muted"><?php echo $t['status'] ?? 'Status'; ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Buttons -->
                            <div class="d-grid gap-2" id="driverModalContactSection">
                                <a href="#" id="driverModalCallBtn" class="btn btn-success btn-lg rounded-pill">
                                    <i class="fas fa-phone me-2"></i><?php echo $t['call_driver'] ?? 'Call Driver'; ?>
                                </a>
                                <a href="#" id="driverModalWhatsappBtn" class="btn btn-lg rounded-pill" style="background-color: #25D366; color: white;">
                                    <i class="fa-brands fa-whatsapp me-2"></i><?php echo $t['message_driver'] ?? 'WhatsApp'; ?>
                                </a>
                            </div>
                            
                            <!-- Timestamp Info -->
                            <div class="text-center mt-3 small text-muted" id="driverModalTimeInfo">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light w-100 rounded-pill" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i><?php echo $t['close'] ?? 'Close'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script>
// PHP-based translations and initialization
const AppTranslations = {
    // Status translations
    st_pending: '<?php echo $t['st_pending'] ?? 'Pending'; ?>',
    st_accepted: '<?php echo $t['st_accepted'] ?? 'Accepted'; ?>',
    st_picked_up: '<?php echo $t['st_picked_up'] ?? 'Picked Up'; ?>',
    st_delivered: '<?php echo $t['st_delivered'] ?? 'Delivered'; ?>',
    st_cancelled: '<?php echo $t['st_cancelled'] ?? 'Cancelled'; ?>',

    // Common translations
    order_number: '<?php echo $t['order_number'] ?? 'Order'; ?>',
    close: '<?php echo $t['close'] ?? 'Close'; ?>',
    success: '<?php echo $t['success'] ?? 'Success'; ?>',
    error: '<?php echo $t['error'] ?? 'Error'; ?>',
    try_again: '<?php echo $t['try_again'] ?? 'Please try again'; ?>',
    accept: '<?php echo $t['accept'] ?? 'Accept'; ?>',
    decline: '<?php echo $t['decline'] ?? 'Decline'; ?>',

    // GPS translations
    geolocation_not_supported: '<?php echo $t['geolocation_not_supported'] ?? 'Geolocation is not supported by your browser'; ?>',
    location_error: '<?php echo $t['location_error'] ?? 'Error getting location'; ?>',
    location_denied: '<?php echo $t['location_denied'] ?? 'Location access denied. Please enable GPS.'; ?>',
    gps_disabled: '<?php echo $t['gps_disabled'] ?? 'GPS Disabled'; ?>',
    gps_enabled: '<?php echo $t['gps_enabled'] ?? 'GPS Enabled'; ?>',
    gps_driver_note: '<?php echo $t['gps_driver_note'] ?? 'Enable GPS to see nearby orders'; ?>',
    updating_location: '<?php echo $t['updating_location'] ?? 'Updating location...'; ?>',
    location_updated: '<?php echo $t['location_updated'] ?? 'Location updated'; ?>',

    // Order translations
    new_order_nearby: '<?php echo $t['new_order_nearby'] ?? 'New Order Nearby!'; ?>',
    new_order_alert: '<?php echo $t['new_order_alert'] ?? 'New Order Alert'; ?>',
    new_order: '<?php echo $t['new_order'] ?? 'new order(s)'; ?>',
    order_accepted: '<?php echo $t['order_accepted'] ?? 'Order accepted!'; ?>',
    order_status_changed: '<?php echo $t['order_status_changed'] ?? 'Order Status Changed'; ?>',
    no_phone: '<?php echo $t['no_phone'] ?? 'No phone'; ?>',

    // Time/Distance translations
    km: '<?php echo $t['km'] ?? 'km'; ?>',
    min: '<?php echo $t['min'] ?? 'min'; ?>',
    driver_assigned: '<?php echo $t['driver_assigned'] ?? 'Driver assigned'; ?>',

    // Zone translations
    mru: '<?php echo $t['mru'] ?? 'MRU'; ?>'
};

const AppConfig = {
    lang: '<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>',
    userRole: '<?php echo isset($_SESSION['user']) ? htmlspecialchars($role, ENT_QUOTES, 'UTF-8') : ''; ?>',
    isLoggedIn: <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>,
    countryCode: '<?php echo htmlspecialchars($country_code, ENT_QUOTES, 'UTF-8'); ?>'
};

// Zone price matrix for client-side calculation
const zonePrices = <?php echo json_encode($zone_prices); ?>;

// Global state for order pricing
let currentBasePrice = 0;
let currentDiscount = 0;
let currentPromoValid = false;

// Calculate delivery price based on selected zones
function calculateDeliveryPrice() {
    const pickupZone = document.getElementById('pickupZone')?.value;
    const dropoffZone = document.getElementById('dropoffZone')?.value;

    // Pricing constants for fallback when no routes found
    const DEFAULT_PRICE = 150;

    // Calculate price when both zones are selected
    if (pickupZone && dropoffZone) {
        // Both zones selected - find exact price
        let price = DEFAULT_PRICE;
        for (const route of zonePrices) {
            if (route.from === pickupZone && route.to === dropoffZone) {
                price = route.price;
                break;
            }
        }
        currentBasePrice = price;
    } else {
        currentBasePrice = 0;
    }
}


window.showOrderTracking = function(order) {
    _showOrderTracking(order, AppTranslations);
};

// Show driver details popup modal
window.showDriverDetails = function(driver) {
    // Set avatar (escape src attribute to prevent XSS)
    const avatarEl = document.getElementById('driverModalAvatar');
    if (driver.avatar) {
        const img = document.createElement('img');
        img.src = driver.avatar;
        img.alt = '';
        img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
        avatarEl.innerHTML = '';
        avatarEl.appendChild(img);
    } else {
        avatarEl.innerHTML = '<i class="fas fa-motorcycle"></i>';
    }
    
    // Set name
    document.getElementById('driverModalName').textContent = driver.name || '-';
    
    // Set rating
    const rating = parseFloat(driver.rating) || 5.0;
    document.getElementById('driverModalRating').innerHTML = '<i class="fas fa-star text-warning"></i> <span>' + rating.toFixed(1) + '</span> / 5';
    
    // Set verified badge
    const verifiedBadge = document.getElementById('driverModalVerifiedBadge');
    verifiedBadge.style.display = driver.verified ? 'flex' : 'none';
    verifiedBadge.style.alignItems = 'center';
    verifiedBadge.style.justifyContent = 'center';
    
    // Set deliveries count
    document.getElementById('driverModalDeliveries').textContent = driver.total_deliveries || '0';
    
    // Set status badge
    const statusEl = document.getElementById('driverModalStatus');
    let statusBadge = '';
    if (driver.status === 'accepted') {
        statusBadge = '<span class="badge bg-info">' + (AppTranslations.st_accepted || 'Accepted') + '</span>';
    } else if (driver.status === 'picked_up') {
        statusBadge = '<span class="badge bg-primary">' + (AppTranslations.st_picked_up || 'Picked Up') + '</span>';
    } else if (driver.status === 'delivered') {
        statusBadge = '<span class="badge bg-success">' + (AppTranslations.st_delivered || 'Delivered') + '</span>';
    }
    statusEl.innerHTML = statusBadge;
    
    // Set contact buttons
    const contactSection = document.getElementById('driverModalContactSection');
    if (driver.phone) {
        contactSection.style.display = 'grid';
        const countryCode = AppConfig.countryCode || '222';
        document.getElementById('driverModalCallBtn').href = 'tel:+' + countryCode + driver.phone;
        document.getElementById('driverModalWhatsappBtn').href = 'https://wa.me/' + countryCode + driver.phone;
    } else {
        contactSection.style.display = 'none';
    }
    
    // Set time info (sanitize the date value to prevent XSS)
    const timeInfoEl = document.getElementById('driverModalTimeInfo');
    if (driver.accepted_at) {
        // Create elements safely to avoid XSS
        const icon = document.createElement('i');
        icon.className = 'fas fa-clock me-1';
        const text = document.createTextNode((AppTranslations.driver_assigned || 'Assigned') + ': ' + driver.accepted_at);
        timeInfoEl.innerHTML = '';
        timeInfoEl.appendChild(icon);
        timeInfoEl.appendChild(text);
    } else {
        timeInfoEl.innerHTML = '';
    }
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('driverDetailsModal'));
    modal.show();
};

<?php if(isset($_SESSION['user']) && !isset($_GET['settings'])): ?>
// Initialize real-time polling
initRealtimePolling(AppConfig.userRole, AppTranslations);
<?php endif; ?>
</script>
</body>
</html>
