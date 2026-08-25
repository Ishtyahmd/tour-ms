<?php

// 1. GLOBAL SECURITY HEADERS & SESSION SETUP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. INCLUDE GLOBAL DEPENDENCIES
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// 3. PARSE ACTION PARAMETER
// Reads action from $_GET or defaults to 'home'
$action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : 'home';

// Basic sanitization of action string
$action = preg_replace('/[^a-z0-9_]/', '', $action);

// 4. CENTRAL ROUTING SWITCH STATEMENT
switch ($action) {

    // PUBLIC & AUTHENTICATION ROUTES
    case 'home':
        require_once __DIR__ . '/controllers/home_controller.php';
        render_home_page();
        break;

    case 'login':
        require_once __DIR__ . '/controllers/auth_controller.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            process_login();
        } else {
            render_login_form();
        }
        break;

    case 'register':
        require_once __DIR__ . '/controllers/auth_controller.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            process_registration();
        } else {
            render_registration_form();
        }
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/auth_controller.php';
        process_logout();
        break;

    // USER (CUSTOMER) ROUTES

    case 'user_dashboard':
        check_authorization(array('user'));
        require_once __DIR__ . '/controllers/user_controller.php';
        render_user_dashboard();
        break;

    case 'submit_special_request':
        check_authorization(array('user'));
        require_once __DIR__ . '/controllers/user_controller.php';
        handle_special_request_submission();
        break;

    case 'rate_service':
        check_authorization(array('user'));
        require_once __DIR__ . '/controllers/user_controller.php';
        handle_rating_submission();
        break;

    // ADMIN ROUTES

    case 'admin_dashboard':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/admin_controller.php';
        render_admin_dashboard();
        break;

    case 'admin_assign_guide':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/admin_controller.php';
        handle_guide_assignment();
        break;

    case 'admin_manage_discounts':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/admin_controller.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handle_create_discount();
        } else {
            render_discounts_page();
        }
        break;

    case 'admin_broadcast':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/admin_controller.php';
        handle_broadcast_notification();
        break;

    // TOUR GUIDE ROUTES
    case 'guide_dashboard':
        check_authorization(array('guide'));
        require_once __DIR__ . '/controllers/guide_controller.php';
        render_guide_dashboard();
        break;

    case 'guide_update_status':
        check_authorization(array('guide'));
        require_once __DIR__ . '/controllers/guide_controller.php';
        handle_status_update();
        break;

    // VENDOR (HOTEL / TRANSPORTATION) ROUTES

    case 'vendor_dashboard':
        check_authorization(array('vendor'));
        require_once __DIR__ . '/controllers/vendor_controller.php';
        render_vendor_dashboard();
        break;

    case 'vendor_manage_listings':
        check_authorization(array('vendor'));
        require_once __DIR__ . '/controllers/vendor_controller.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            handle_add_listing();
        } else {
            render_listings_page();
        }
        break;

    case 'vendor_update_request_status':
        check_authorization(array('vendor'));
        require_once __DIR__ . '/controllers/vendor_controller.php';
        handle_update_special_request();
        break;

    // PROCEDURAL AJAX ENDPOINTS

    case 'ajax_assign_guide':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/ajax_assign_guide.php';
        break;

    case 'ajax_submit_rating':
        check_authorization(array('user'));
        require_once __DIR__ . '/controllers/ajax_submit_rating.php';
        break;

    case 'ajax_send_notification':
        check_authorization(array('admin'));
        require_once __DIR__ . '/controllers/ajax_send_notification.php';
        break;

    case 'ajax_fetch_notifications':
        check_authorization(array('admin', 'user', 'guide', 'vendor'));
        require_once __DIR__ . '/controllers/ajax_fetch_notifications.php';
        break;


    // 404 NOT FOUND FALLBACK

    default:
        http_response_code(404);
        require_once __DIR__ . '/views/errors/404.php';
        render_404_page();
        break;
}
