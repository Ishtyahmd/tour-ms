<?php
// Ensure session is started safely across all requests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

/**
 * Authenticates a user by email and password using mysqli procedural functions.
 */
function login_user($email, $password)
{
    $conn = db_connect();

    $email = mysqli_real_escape_string($conn, trim($email));
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verify hash password (or plain text comparison if hashing isn't used yet)
        if (password_verify($password, $user['password'])) {
            // Prevent session fixation attacks
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            db_close($conn);
            return true;
        }
    }

    db_close($conn);
    return false;
}

/**
 * Checks if a user is currently logged in.
 */
function is_logged_in()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !empty($_SESSION['user_id']);
}

/**
 * Procedural authorization guard. Ensures user is logged in and possesses an allowed role.
 * Redirects or terminates execution with a 403 status if unauthorized.
 */
function check_authorization($allowed_roles = array())
{
    // 1. Check if user is logged in
    if (!is_logged_in()) {
        if (is_ajax_request()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized. Please log in.'));
            exit();
        } else {
            header("Location: index.php?action=login&error=login_required");
            exit();
        }
    }

    // 2. Check role permissions if restrictions are specified
    if (!empty($allowed_roles)) {
        $current_role = $_SESSION['user_role'] ?? '';

        if (!in_array($current_role, $allowed_roles)) {
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(array('status' => 'error', 'message' => 'Forbidden. Access denied.'));
                exit();
            } else {
                header("Location: index.php?action=dashboard&error=access_denied");
                exit();
            }
        }
    }
}

/**
 * Helper to detect AJAX requests procedurally.
 */
function is_ajax_request()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Logs out the current user and clears session data.
 */
function logout_user()
{
    $_SESSION = array();

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

    session_destroy();
}
