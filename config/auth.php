<?php

/**
 * Global Procedural Authentication & Authorization Helper
 * Paradigm: Pure Procedural PHP (Zero OOP)
 */

// 1. ENSURE SECURE SESSION INITIALIZATION
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie settings before starting session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Ensure database functions are available
require_once __DIR__ . '/database.php';

/**
 * Checks if the current request user is logged in.
 *
 * @return bool True if authenticated, false otherwise.
 */
function is_logged_in()
{
    return isset($_SESSION['logged_in'])
        && $_SESSION['logged_in'] === true
        && !empty($_SESSION['user_id']);
}

/**
 * Returns the currently authenticated user's ID or 0 if guest.
 *
 * @return int
 */
function get_logged_in_user_id()
{
    return is_logged_in() ? (int)$_SESSION['user_id'] : 0;
}

/**
 * Returns the currently authenticated user's role or empty string if guest.
 *
 * @return string
 */
function get_logged_in_user_role()
{
    return is_logged_in() ? $_SESSION['user_role'] : '';
}

/**
 * Detects if the current request was initiated via AJAX / XMLHttpRequest.
 *
 * @return bool
 */
function is_ajax_request()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Procedural authorization guard.
 * Verifies authentication and checks if user role exists within allowed roles list.
 * Terminate or redirects immediately if unauthorized.
 *
 * @param array $allowed_roles List of role strings allowed to access the route.
 * @return void
 */
function check_authorization($allowed_roles = array())
{
    // Step 1: Check if user is logged in
    if (!is_logged_in()) {
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(array(
                'status'  => 'error',
                'message' => 'Unauthorized access. Please log in.'
            ));
            exit();
        } else {
            header("Location: index.php?action=login&error=login_required");
            exit();
        }
    }

    // Step 2: Validate user role against permission whitelist (if restrictions set)
    if (!empty($allowed_roles)) {
        $user_role = get_logged_in_user_role();

        if (!in_array($user_role, $allowed_roles)) {
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(array(
                    'status'  => 'error',
                    'message' => 'Forbidden access. You do not have permission to perform this action.'
                ));
                exit();
            } else {
                header("Location: index.php?action=home&error=access_denied");
                exit();
            }
        }
    }
}

/**
 * Destroys all user session data, invalidates session cookie, and logs user out.
 *
 * @return void
 */
function logout_user()
{
    // Unset all session variables
    $_SESSION = array();

    // Clear session ID cookie if present
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session storage completely
    session_destroy();
}
