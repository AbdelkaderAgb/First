/**
 * Barq Delivery Pro - Main Application JavaScript
 * Separated from index.php for better code organization
 */

// ==========================================
// WEB AUDIO API - NOTIFICATION SOUNDS
// ==========================================
let audioContext = null;

function getAudioContext() {
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioContext;
}

function createNotificationSound() {
    try {
        const ctx = getAudioContext();

        // Resume context if suspended (needed for user interaction requirement)
        if (ctx.state === 'suspended') {
            ctx.resume();
        }

        const currentTime = ctx.currentTime;

        // Create oscillator for the main tone
        const osc1 = ctx.createOscillator();
        const osc2 = ctx.createOscillator();
        const gainNode = ctx.createGain();

        // Connect nodes
        osc1.connect(gainNode);
        osc2.connect(gainNode);
        gainNode.connect(ctx.destination);

        // Configure sound - pleasant notification tone
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(880, currentTime); // A5
        osc1.frequency.setValueAtTime(1046.5, currentTime + 0.1); // C6

        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(659.25, currentTime); // E5
        osc2.frequency.setValueAtTime(783.99, currentTime + 0.1); // G5

        // Envelope
        gainNode.gain.setValueAtTime(0, currentTime);
        gainNode.gain.linearRampToValueAtTime(0.3, currentTime + 0.02);
        gainNode.gain.linearRampToValueAtTime(0.2, currentTime + 0.1);
        gainNode.gain.linearRampToValueAtTime(0.3, currentTime + 0.12);
        gainNode.gain.linearRampToValueAtTime(0, currentTime + 0.3);

        // Start and stop
        osc1.start(currentTime);
        osc2.start(currentTime);
        osc1.stop(currentTime + 0.3);
        osc2.stop(currentTime + 0.3);

        return true;
    } catch(e) {
        console.log('Audio not available:', e);
        return false;
    }
}

// ==========================================
// RTL SUPPORT - PHONE NUMBERS DISPLAY
// ==========================================
const isRTL = document.documentElement.dir === 'rtl';

// Keep phone and number inputs LTR for correct display
document.addEventListener('DOMContentLoaded', function() {
    // Make phone inputs LTR for proper number display (always left-aligned)
    document.querySelectorAll('input[type="tel"], input[name*="phone"]').forEach(input => {
        input.style.direction = 'ltr';
        input.style.textAlign = 'left';
    });

    // Make number inputs LTR (always left-aligned for proper number entry)
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.style.direction = 'ltr';
        input.style.textAlign = 'left';
    });

    // Ensure phone number displays stay LTR
    document.querySelectorAll('.phone-display, [data-phone]').forEach(el => {
        el.style.direction = 'ltr';
        el.style.unicodeBidi = 'embed';
        el.style.display = 'inline-block';
        el.style.textAlign = 'left';
    });
});

// ==========================================
// NAVBAR SCROLL EFFECT
// ==========================================
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.app-navbar');
    if (navbar) {
        if (window.scrollY > 10) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// ==========================================
// AUTH FORM TOGGLE
// ==========================================
function showAuthForm(form) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginToggle = document.getElementById('loginToggle');
    const registerToggle = document.getElementById('registerToggle');
    const loginFooterText = document.getElementById('loginFooterText');
    const registerFooterText = document.getElementById('registerFooterText');

    if (!loginForm || !registerForm) return;

    loginForm.classList.remove('active');
    registerForm.classList.remove('active');
    loginToggle.classList.remove('active');
    registerToggle.classList.remove('active');

    document.getElementById(form + 'Form').classList.add('active');
    document.getElementById(form + 'Toggle').classList.add('active');

    // Toggle footer text
    if (loginFooterText && registerFooterText) {
        if (form === 'login') {
            loginFooterText.style.display = 'block';
            registerFooterText.style.display = 'none';
        } else {
            loginFooterText.style.display = 'none';
            registerFooterText.style.display = 'block';
        }
    }
}

// ==========================================
// FORM VALIDATION
// ==========================================
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredInputs = this.querySelectorAll('input[required], textarea[required], select[required]');
        let valid = true;

        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        // Password match check for registration
        const password = this.querySelector('input[name="reg_password"]');
        const confirm = this.querySelector('input[name="reg_confirm_password"]');
        if (password && confirm && password.value !== confirm.value) {
            valid = false;
            confirm.classList.add('is-invalid');
        }

        if (!valid) {
            e.preventDefault();
            this.querySelector('button').classList.remove('loading');
        }
    });
});

// Remove invalid class on input
document.querySelectorAll('input, textarea, select').forEach(element => {
    const removeInvalidClass = function() {
        this.classList.remove('is-invalid');
    };
    element.addEventListener('input', removeInvalidClass);
    element.addEventListener('change', removeInvalidClass);
});

// ==========================================
// ADMIN MODALS
// ==========================================
function showAddUserModal(role) {
    document.getElementById('addUserRole').value = role;
    var modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}

function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_full_name').value = user.full_name || '';
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_points').value = user.points;

    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Bulk Recharge Functions
function toggleAllDrivers(checkbox) {
    const driverCheckboxes = document.querySelectorAll('.driver-checkbox');
    driverCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkRechargeButton();
}

function updateBulkRechargeButton() {
    const checkedBoxes = document.querySelectorAll('.driver-checkbox:checked');
    const bulkBtn = document.getElementById('bulkRechargeBtn');
    const selectAll = document.getElementById('selectAllDrivers');

    if (bulkBtn) {
        if (checkedBoxes.length > 0) {
            bulkBtn.style.display = 'inline-block';
            const bulkText = (typeof AppTranslations !== 'undefined' && AppTranslations['bulk_recharge']) ? AppTranslations['bulk_recharge'] : 'Bulk Recharge';
            bulkBtn.innerHTML = '<i class="fas fa-coins"></i> ' + bulkText + ' (' + checkedBoxes.length + ')';
        } else {
            bulkBtn.style.display = 'none';
        }
    }

    // Update select all checkbox state
    if (selectAll) {
        const allDriverCheckboxes = document.querySelectorAll('.driver-checkbox');
        selectAll.checked = allDriverCheckboxes.length > 0 && checkedBoxes.length === allDriverCheckboxes.length;
    }
}

function showBulkRechargeModal() {
    const checkedBoxes = document.querySelectorAll('.driver-checkbox:checked');
    const driverIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');

    const selectedDriverIds = document.getElementById('selectedDriverIds');
    const selectedDriverCount = document.getElementById('selectedDriverCount');
    const bulkAmount = document.getElementById('bulkAmount');
    
    if (selectedDriverIds) selectedDriverIds.value = driverIds;
    if (selectedDriverCount) selectedDriverCount.textContent = checkedBoxes.length;
    if (bulkAmount) bulkAmount.value = '';

    var modal = new bootstrap.Modal(document.getElementById('bulkRechargeModal'));
    modal.show();
}

// Promo Code Functions
function showAddPromoCodeModal() {
    document.getElementById('promoCodeForm').reset();
    document.getElementById('promoId').value = '';
    document.getElementById('promoModalTitle').textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['create_promo']) ? AppTranslations['create_promo'] : 'Create Promo Code';
    document.getElementById('isActive').checked = true;
    updateDiscountLabel();

    var modal = new bootstrap.Modal(document.getElementById('promoCodeModal'));
    modal.show();
}

function editPromoCode(promo) {
    document.getElementById('promoId').value = promo.id;
    document.getElementById('promoCode').value = promo.code;
    document.getElementById('discountType').value = promo.discount_type;
    document.getElementById('discountValue').value = promo.discount_value;
    document.getElementById('maxUses').value = promo.max_uses || '';
    document.getElementById('validFrom').value = promo.valid_from ? promo.valid_from.split(' ')[0] : '';
    document.getElementById('validUntil').value = promo.valid_until ? promo.valid_until.split(' ')[0] : '';
    document.getElementById('isActive').checked = promo.is_active == 1;
    document.getElementById('promoModalTitle').textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['edit_promo']) ? AppTranslations['edit_promo'] : 'Edit Promo Code';
    updateDiscountLabel();

    var modal = new bootstrap.Modal(document.getElementById('promoCodeModal'));
    modal.show();
}

// Initialize promo code input to auto-uppercase
document.addEventListener('DOMContentLoaded', function() {
    const promoCodeInput = document.getElementById('promoCode');
    if (promoCodeInput) {
        promoCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
});

function updateDiscountLabel() {
    const type = document.getElementById('discountType');
    const label = document.getElementById('discountLabel');
    const help = document.getElementById('discountHelp');
    
    if (!type || !label || !help) return;

    const discountValue = document.getElementById('discountValue');
    if (!discountValue) return;

    if (type.value === 'percentage') {
        label.textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['percentage']) ? AppTranslations['percentage'] : 'Percentage';
        help.textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['percentage_help']) ? AppTranslations['percentage_help'] : 'Enter percentage (e.g., 20 for 20% off)';
        discountValue.placeholder = 'e.g. 20';
        discountValue.max = '100';
    } else {
        label.textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['fixed_amount']) ? AppTranslations['fixed_amount'] : 'Fixed Amount (MRU)';
        help.textContent = (typeof AppTranslations !== 'undefined' && AppTranslations['fixed_help']) ? AppTranslations['fixed_help'] : 'Enter fixed discount amount in MRU';
        discountValue.placeholder = 'e.g. 50';
        discountValue.removeAttribute('max');
    }
}

function editOrder(order) {
    document.getElementById('edit_order_id').value = order.id;
    document.getElementById('edit_order_customer').value = order.customer_name;
    document.getElementById('edit_order_details').value = order.details;
    document.getElementById('edit_order_address').value = order.address;
    document.getElementById('edit_order_status').value = order.status;
    document.getElementById('edit_order_driver').value = order.driver_id || '';

    var modal = new bootstrap.Modal(document.getElementById('editOrderModal'));
    modal.show();
}

// ==========================================
// ORDER TRACKING FOR CUSTOMERS
// ==========================================
function _showOrderTracking(order, translations) {
    // Reset all steps
    document.querySelectorAll('.progress-step').forEach(step => {
        step.classList.remove('completed', 'active');
    });

    // Mark completed and active steps based on status
    const steps = ['pending', 'accepted', 'picked_up', 'delivered'];
    const currentIndex = steps.indexOf(order.status);

    steps.forEach((step, index) => {
        const stepEl = document.getElementById('step-' + step);
        if (stepEl) {
            if (index < currentIndex) {
                stepEl.classList.add('completed');
            } else if (index === currentIndex) {
                stepEl.classList.add('completed', 'active');
            }
        }
    });

    // Set driver info
    document.getElementById('tracking-driver-name').textContent = order.driver_name;
    document.getElementById('tracking-driver-rating').innerHTML = '<i class="fas fa-star"></i> ' + parseFloat(order.driver_rating).toFixed(1);

    // Set avatar (escape src attribute to prevent XSS)
    const avatarEl = document.getElementById('tracking-driver-avatar');
    if (order.driver_avatar) {
        const img = document.createElement('img');
        img.src = order.driver_avatar;
        img.alt = 'Driver';
        avatarEl.innerHTML = '';
        avatarEl.appendChild(img);
    } else {
        avatarEl.innerHTML = '<i class="fas fa-user"></i>';
    }

    // Show verified badge
    const verifiedBadge = document.getElementById('tracking-verified-badge');
    verifiedBadge.style.display = order.driver_verified ? 'inline-block' : 'none';

    // Set contact buttons (phone call and WhatsApp)
    if (order.driver_phone) {
        const countryCode = (typeof AppConfig !== 'undefined' && AppConfig.countryCode) ? AppConfig.countryCode : '222';
        document.getElementById('tracking-call-btn').href = 'tel:+' + countryCode + order.driver_phone;
        const whatsappBtn = document.getElementById('tracking-whatsapp-btn');
        if (whatsappBtn) {
            whatsappBtn.href = 'https://wa.me/' + countryCode + order.driver_phone;
        }
    }

    // Set order details
    document.getElementById('tracking-order-details').textContent = order.details;
    document.getElementById('tracking-order-address').textContent = order.address;

    // Calculate and show time info
    let timeInfo = '';
    if (order.accepted_at) {
        const acceptedTime = new Date(order.accepted_at);
        const now = new Date();
        const diffMins = Math.floor((now - acceptedTime) / 60000);
        if (diffMins < 60) {
            timeInfo = (translations.driver_assigned || 'Driver assigned') + ' ' + diffMins + ' ' + (translations.min || 'min') + ' ago';
        } else {
            const diffHours = Math.floor(diffMins / 60);
            timeInfo = (translations.driver_assigned || 'Driver assigned') + ' ' + diffHours + 'h ago';
        }
    }
    document.getElementById('tracking-time-info').textContent = timeInfo;

    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('orderTrackingModal'));
    modal.show();
}

// ==========================================
// NOTIFICATION FUNCTIONS
// ==========================================
function showNotification(title, message, type = 'info') {
    const container = document.getElementById('notificationContainer');
    const id = 'toast-' + Date.now();

    const bgClass = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-primary');
    const textClass = type === 'warning' ? 'text-dark' : 'text-white';

    const toast = document.createElement('div');
    toast.id = id;
    toast.className = `toast show ${bgClass} ${textClass} mb-2`;
    toast.innerHTML = `
        <div class="toast-header ${bgClass} ${textClass}">
            <i class="fas fa-bell me-2"></i>
            <strong class="me-auto">${title}</strong>
            <button type="button" class="btn-close btn-close-white" onclick="this.closest('.toast').remove()"></button>
        </div>
        <div class="toast-body">${message}</div>
    `;

    container.appendChild(toast);

    // Play notification sound
    playNotificationSound();

    // Auto remove after 5 seconds
    setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.remove();
    }, 5000);
}

function playNotificationSound() {
    // Use Web Audio API for notification sound
    createNotificationSound();
}

// Initialize audio context on first user interaction (required by browsers)
document.addEventListener('click', function initAudio() {
    getAudioContext();
    document.removeEventListener('click', initAudio);
}, { once: true });

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// ==========================================
// DRIVER ORDER BUBBLE FUNCTIONS
// ==========================================

// Track displayed order IDs to prevent duplicates
let displayedOrderIds = new Set();

// Play order ring sound
function playOrderRingSound() {
    try {
        const ctx = getAudioContext();
        const now = ctx.currentTime;

        // Create a pleasant ring tone
        for (let i = 0; i < 3; i++) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now + i * 0.3); // A5 note
            osc.frequency.setValueAtTime(1100, now + i * 0.3 + 0.1); // C#6 note

            gain.gain.setValueAtTime(0.3, now + i * 0.3);
            gain.gain.exponentialRampToValueAtTime(0.01, now + i * 0.3 + 0.25);

            osc.start(now + i * 0.3);
            osc.stop(now + i * 0.3 + 0.3);
        }
    } catch(e) {
        console.log('Sound not available');
    }
}

// Create order bubble HTML (Zone-based, no distance)
function createOrderBubble(order, translations) {
    const bubble = document.createElement('div');
    bubble.className = 'order-bubble';
    bubble.id = `order-bubble-${order.id}`;
    bubble.dataset.orderId = order.id;

    const priceText = order.delivery_price ? `${order.delivery_price} ${translations.mru || 'MRU'}` : '---';
    const phone = order.client_phone || (translations.no_phone || 'No phone');
    const pickupZone = order.pickup_zone || '';
    const dropoffZone = order.dropoff_zone || '';

    bubble.innerHTML = `
        <div class="order-bubble-header">
            <div class="new-order-badge">
                <i class="fas fa-bell"></i>
                ${translations.new_order_nearby || 'New Order!'}
            </div>
            <div class="order-bubble-timer"><span class="timer-seconds">10</span>s</div>
        </div>
        <div class="order-bubble-body">
            <div class="order-bubble-distance">
                <i class="fas fa-money-bill-wave"></i>
                ${priceText}
            </div>
            ${pickupZone && dropoffZone ? `
            <div class="order-bubble-zones mb-2">
                <span class="badge bg-success">${pickupZone}</span>
                <i class="fas fa-arrow-right mx-2"></i>
                <span class="badge bg-danger">${dropoffZone}</span>
            </div>
            ` : ''}
            <div class="order-bubble-details">${escapeHtml(order.details)}</div>
            <div class="order-bubble-address">
                <i class="fas fa-map-marker-alt"></i>
                <span>${escapeHtml(order.address)}</span>
            </div>
            <div class="order-bubble-phone">
                <i class="fas fa-phone"></i>
                <a href="tel:+222${phone}" dir="ltr">+222 ${phone}</a>
            </div>
            <div class="order-bubble-actions">
                <button class="btn btn-accept" onclick="acceptOrderFromBubble(${order.id}, this)">
                    <i class="fas fa-check me-2"></i>${translations.accept || 'Accept'}
                </button>
                <button class="btn btn-decline" onclick="declineOrderBubble(${order.id})">
                    <i class="fas fa-times me-2"></i>${translations.decline || 'Decline'}
                </button>
            </div>
        </div>
        <div class="order-bubble-progress">
            <div class="order-bubble-progress-bar" style="width: 100%"></div>
        </div>
    `;

    return bubble;
}

// Show order bubble with countdown
function showOrderBubble(order, translations) {
    if (displayedOrderIds.has(order.id)) return;

    displayedOrderIds.add(order.id);

    const container = document.getElementById('orderBubbleContainer');
    if (!container) return;

    const bubble = createOrderBubble(order, translations);
    container.appendChild(bubble);

    // Play ring sound
    playOrderRingSound();

    // Start countdown
    let secondsLeft = 10;
    const timerSpan = bubble.querySelector('.timer-seconds');
    const progressBar = bubble.querySelector('.order-bubble-progress-bar');

    const countdown = setInterval(() => {
        secondsLeft--;
        if (timerSpan) timerSpan.textContent = secondsLeft;
        if (progressBar) progressBar.style.width = (secondsLeft / 10 * 100) + '%';

        if (secondsLeft <= 0) {
            clearInterval(countdown);
            removeBubble(order.id);
        }
    }, 1000);

    // Store countdown reference
    bubble.dataset.countdown = countdown;
}

// Decline order bubble (just dismiss it)
function declineOrderBubble(orderId) {
    removeBubble(orderId);
    // Keep in set for this session to avoid showing again
}

// Remove bubble with animation
function removeBubble(orderId) {
    const bubble = document.getElementById(`order-bubble-${orderId}`);
    if (bubble) {
        // Clear countdown
        if (bubble.dataset.countdown) {
            clearInterval(parseInt(bubble.dataset.countdown));
        }

        bubble.classList.add('fade-out');
        setTimeout(() => bubble.remove(), 400);
    }
}

// Fetch pending orders for driver (zone-based, no GPS)
function fetchPendingOrders(translations) {
    fetch('api.php?action=get_pending_orders')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders && data.orders.length > 0) {
                data.orders.forEach(order => {
                    showOrderBubble(order, translations);
                });
            }
        })
        .catch(() => {});
}

// ==========================================
// ACCEPT ORDER FROM BUBBLE (FOR DRIVERS)
// ==========================================
function _acceptOrderFromBubble(orderId, btn, translations) {
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    // Submit accept request via API
    fetch('api.php?action=accept_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `oid=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success - remove bubble and reload page
            removeBubble(orderId);
            showNotification(translations.success || 'Success', data.message || translations.order_accepted || 'Order accepted!', 'success');

            // Update points display if available
            if (data.new_points !== undefined) {
                const pointsElement = document.getElementById('currentPoints');
                const driverPointsElement = document.getElementById('driverPoints');
                if (pointsElement) pointsElement.textContent = data.new_points;
                if (driverPointsElement) driverPointsElement.textContent = data.new_points;
            }

            setTimeout(() => location.reload(), 1000);
        } else {
            // Error
            btn.innerHTML = '<i class="fas fa-check me-2"></i>' + (translations.accept || 'Accept');
            btn.disabled = false;
            showNotification(translations.error || 'Error', data.error || translations.try_again || 'Please try again', 'warning');
        }
    })
    .catch(() => {
        btn.innerHTML = '<i class="fas fa-check me-2"></i>' + (translations.accept || 'Accept');
        btn.disabled = false;
        showNotification(translations.error || 'Error', translations.try_again || 'Please try again', 'warning');
    });
}

// Accept order from bubble wrapper
function acceptOrderFromBubble(orderId, btn) {
    _acceptOrderFromBubble(orderId, btn, window.AppTranslations || {});
}

// ==========================================
// REAL-TIME NOTIFICATIONS POLLING
// ==========================================

// Configuration constants for form interaction tracking
const FORM_BLUR_RESET_DELAY_MS = 500;       // Delay before resetting flag when form loses focus
const ZONE_SELECTION_RESET_DELAY_MS = 10000; // Delay before resetting flag after zone selection

// Track if user is actively filling out the new order form
let isFillingOrderForm = false;

// Timeout ID for resetting form interaction flag
let formInteractionTimeout = null;

// Helper function to clear and reset the interaction timeout
function clearFormInteractionTimeout() {
    if (formInteractionTimeout !== null) {
        clearTimeout(formInteractionTimeout);
        formInteractionTimeout = null;
    }
}

// Helper function to check if an element still exists in DOM
function isElementInDOM(element) {
    return element && document.body.contains(element);
}

// Helper function to check if focus is inside a form
function isFocusInsideForm(formElement) {
    // If formElement is null, no active element, or body is focused, consider focus as outside
    const activeElement = document.activeElement;
    if (!formElement || !activeElement || activeElement === document.body) {
        return false;
    }
    return formElement.contains(activeElement);
}

// Set up form interaction tracking
document.addEventListener('DOMContentLoaded', function() {
    const newOrderForm = document.getElementById('newOrderForm');
    if (!newOrderForm) return;
    
    // Track when user starts interacting with the form
    newOrderForm.addEventListener('focusin', function() {
        isFillingOrderForm = true;
        clearFormInteractionTimeout();
    });
    
    // Track when user stops interacting with the form (with a delay to prevent premature reset)
    newOrderForm.addEventListener('focusout', function() {
        // Clear any existing timeout first to prevent memory leaks
        clearFormInteractionTimeout();
        
        // Use a delay to avoid flickering when moving between form fields
        formInteractionTimeout = setTimeout(function() {
            // Check if form still exists in DOM and if focus moved outside
            if (isElementInDOM(newOrderForm) && !isFocusInsideForm(newOrderForm)) {
                isFillingOrderForm = false;
            }
        }, FORM_BLUR_RESET_DELAY_MS);
    });
    
    // Also track when zones are selected - set flag and schedule reset
    const pickupZone = document.getElementById('pickupZone');
    const dropoffZone = document.getElementById('dropoffZone');
    
    function handleZoneChange() {
        isFillingOrderForm = true;
        // Clear any existing timeout first to prevent memory leaks
        clearFormInteractionTimeout();
        
        // Schedule flag reset after a reasonable time
        // This gives the user time to continue filling the form
        formInteractionTimeout = setTimeout(function() {
            // Only reset if form still exists and focus is outside the form
            if (isElementInDOM(newOrderForm) && !isFocusInsideForm(newOrderForm)) {
                isFillingOrderForm = false;
            }
        }, ZONE_SELECTION_RESET_DELAY_MS);
    }
    
    if (pickupZone) {
        pickupZone.addEventListener('change', handleZoneChange);
    }
    if (dropoffZone) {
        dropoffZone.addEventListener('change', handleZoneChange);
    }
});

function initRealtimePolling(userRole, translations) {
    let lastCheck = Math.floor(Date.now() / 1000);

    function checkForUpdates() {
        fetch(`api.php?action=check_orders&last_check=${lastCheck}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    lastCheck = data.timestamp;

                    if (userRole === 'driver' && data.should_notify && data.new_orders > 0) {
                        showNotification(
                            translations.new_order_alert,
                            `${data.new_orders} ${translations.new_order}`,
                            'warning'
                        );

                        // Update pending badge
                        const badge = document.getElementById('pendingBadge');
                        if (badge && data.pending_count > 0) {
                            badge.textContent = data.pending_count;
                            badge.style.display = 'inline';
                        }

                        // Refresh page to show new orders
                        setTimeout(() => location.reload(), 2000);
                    }

                    if (userRole === 'customer' && data.should_notify && data.changed_orders.length > 0) {
                        data.changed_orders.forEach(order => {
                            showNotification(
                                translations.order_status_changed,
                                `Order #${order.order_id}: ${order.status}`,
                                'success'
                            );
                        });

                        // Only refresh page if user is not actively filling out the order form
                        if (!isFillingOrderForm) {
                            setTimeout(() => location.reload(), 2000);
                        }
                    }
                }
            })
            .catch(err => console.log('Check failed:', err));
    }

    // Check every 10 seconds
    setInterval(checkForUpdates, 10000);

    // Initial check after 3 seconds
    setTimeout(checkForUpdates, 3000);

    // For drivers, also poll for new orders to show bubbles
    if (userRole === 'driver') {
        // Start polling for new orders every 15 seconds
        fetchPendingOrders(translations);
        setInterval(() => fetchPendingOrders(translations), 15000);
    }
}

// ==========================================
// FORM LOADING STATES
// ==========================================

// Minimum delay (in ms) before disabling submit button after form submission starts.
// This ensures the browser has time to process the form data before the button is disabled,
// which prevents form submission cancellation in browsers that check button state during submit.
const FORM_SUBMIT_BUTTON_DISABLE_DELAY = 100;

// Add loading state to all forms on submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't add loading state to forms that shouldn't have it
            if (form.classList.contains('no-loading')) {
                return;
            }

            // Add loading class to form
            form.classList.add('submitting');

            // Find submit button and add loading state
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');

                // Store original text
                if (!submitBtn.dataset.originalText) {
                    submitBtn.dataset.originalText = submitBtn.innerHTML;
                }

                // Delay disabling the button to allow form submission to complete.
                // Disabling synchronously can cancel form submission in some browsers
                // because the submit button's name/value won't be included in form data.
                setTimeout(function() {
                    submitBtn.disabled = true;
                }, FORM_SUBMIT_BUTTON_DISABLE_DELAY);
            }

            // Show loading overlay for important forms
            if (form.id === 'newOrderForm' || form.classList.contains('show-overlay')) {
                showLoadingOverlay();
            }
        });
    });

    // Modal forms
    const modalForms = document.querySelectorAll('.modal form');
    modalForms.forEach(form => {
        form.addEventListener('submit', function() {
            showLoadingOverlay();
        });
    });
});

// Loading overlay functions
function showLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.add('active');
    }
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Hide loading overlay on page load (in case of redirect back)
window.addEventListener('load', function() {
    hideLoadingOverlay();
});

// ==========================================
// ENHANCED ERROR HANDLING
// ==========================================

// Auto-hide flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// ==========================================
// PROMO CODE VALIDATION
// ==========================================
function validatePromoCode() {
    const input = document.getElementById('promoCodeInput');
    const feedback = document.getElementById('promoFeedback');
    const btn = document.getElementById('validatePromoBtn');
    const code = input.value.trim().toUpperCase();

    if (!code) {
        feedback.innerHTML = '';
        feedback.className = 'text-muted';
        // Clear discount when promo code is removed
        if (typeof currentDiscount !== 'undefined') {
            currentDiscount = 0;
            currentPromoValid = false;
            if (typeof updateOrderSummary === 'function') {
                updateOrderSummary();
            }
        }
        return;
    }

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    feedback.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Validating...';
    feedback.className = 'text-info';

    // Validate promo code via API
    fetch('api.php?action=validate_promo&code=' + encodeURIComponent(code))
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Apply';

            if (data.success) {
                feedback.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + data.message;
                feedback.className = 'text-success fw-bold';
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
                
                // Calculate and apply discount
                if (data.promo && typeof currentBasePrice !== 'undefined') {
                    let discountAmount = 0;
                    if (data.promo.discount_type === 'percentage') {
                        discountAmount = Math.round((currentBasePrice * data.promo.discount_value) / 100);
                    } else {
                        // Fixed discount
                        discountAmount = Math.min(data.promo.discount_value, currentBasePrice);
                    }
                    currentDiscount = discountAmount;
                    currentPromoValid = true;
                    
                    // Update order summary display
                    if (typeof updateOrderSummary === 'function') {
                        updateOrderSummary();
                    }
                }
            } else {
                feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>' + data.message;
                feedback.className = 'text-danger';
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                
                // Clear discount on invalid promo
                if (typeof currentDiscount !== 'undefined') {
                    currentDiscount = 0;
                    currentPromoValid = false;
                    if (typeof updateOrderSummary === 'function') {
                        updateOrderSummary();
                    }
                }
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Apply';
            feedback.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error validating code';
            feedback.className = 'text-warning';
            
            // Clear discount on error
            if (typeof currentDiscount !== 'undefined') {
                currentDiscount = 0;
                currentPromoValid = false;
                if (typeof updateOrderSummary === 'function') {
                    updateOrderSummary();
                }
            }
        });
}
