<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// VIEW RENDERERS (GET Requests)
function render_login_form()
{
    // If user is already logged in, redirect them to home
    if (is_logged_in()) {
        header("Location: index.php?action=home");
        exit();
    }

    // Include the HTML view for login
    require_once __DIR__ . '/../views/auth/login.php';
}

function render_registration_form()
{
    if (is_logged_in()) {
        header("Location: index.php?action=home");
        exit();
    }

    require_once __DIR__ . '/../views/auth/register.php';
}

// -----------------------------------------------------------------------------
// ACTION PROCESSORS (POST & Session Handlers)
// -----------------------------------------------------------------------------

/**
 * Handles user registration form submission
 */
function process_registration()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=register");
        exit();
    }

    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $phone    = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $role     = isset($_POST['role']) ? trim($_POST['role']) : 'user';

    // 1. Validate mandatory fields
    if (empty($name) || empty($email) || empty($password)) {
        header("Location: index.php?action=register&error=empty_fields");
        exit();
    }

    // 2. Validate role input (Restrict to allowed options)
    $allowed_roles = array('user', 'guide', 'vendor');
    if (!in_array($role, $allowed_roles)) {
        $role = 'user';
    }

    $conn = db_connect();

    // 3. Check if email already exists using prepared statements
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        db_close($conn);
        header("Location: index.php?action=register&error=email_taken");
        exit();
    }
    mysqli_stmt_close($stmt);

    // 4. Hash password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert primary user record
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, "sssss", $name, $email, $hashed_password, $phone, $role);
    $user_created = mysqli_stmt_execute($insert_stmt);
    $new_user_id  = mysqli_insert_id($conn);
    mysqli_stmt_close($insert_stmt);

    if (!$user_created) {
        db_close($conn);
        header("Location: index.php?action=register&error=failed");
        exit();
    }

    // 6. Create default secondary profiles if registering as a guide or vendor
    if ($role === 'guide') {
        $location = isset($_POST['location']) ? trim($_POST['location']) : 'Not Specified';
        $daily_rate = isset($_POST['daily_rate']) ? (float)$_POST['daily_rate'] : 0.00;

        $g_stmt = mysqli_prepare($conn, "INSERT INTO guides (user_id, location, daily_rate) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($g_stmt, "isd", $new_user_id, $location, $daily_rate);
        mysqli_stmt_execute($g_stmt);
        mysqli_stmt_close($g_stmt);
    } elseif ($role === 'vendor') {
        $vendor_type  = isset($_POST['vendor_type']) ? trim($_POST['vendor_type']) : 'hotel';
        $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : $name;
        $address      = isset($_POST['address']) ? trim($_POST['address']) : '';

        $v_stmt = mysqli_prepare($conn, "INSERT INTO vendors (user_id, type, company_name, address) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($v_stmt, "isss", $new_user_id, $vendor_type, $company_name, $address);
        mysqli_stmt_execute($v_stmt);
        mysqli_stmt_close($v_stmt);
    }

    db_close($conn);

    // Redirect to login page upon success
    header("Location: index.php?action=login&status=registered");
    exit();
}

/**
 * Handles user login authentication
 */
function process_login()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=login");
        exit();
    }

    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        header("Location: index.php?action=login&error=empty_fields");
        exit();
    }

    $conn = db_connect();

    // Query credentials procedurally
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify hashed password
        if (password_verify($password, $row['password'])) {
            // Prevent session fixation
            session_regenerate_id(true);

            // Populate session data
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['logged_in'] = true;

            mysqli_stmt_close($stmt);
            db_close($conn);

            // Redirect dynamically based on user role
            switch ($row['role']) {
                case 'admin':
                    header("Location: index.php?action=admin_dashboard");
                    break;
                case 'guide':
                    header("Location: index.php?action=guide_dashboard");
                    break;
                case 'vendor':
                    header("Location: index.php?action=vendor_dashboard");
                    break;
                default:
                    header("Location: index.php?action=user_dashboard");
                    break;
            }
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    db_close($conn);

    // Invalid credentials fallback
    header("Location: index.php?action=login&error=invalid_credentials");
    exit();
}

/**
 * Destroys session and logs the user out
 */
function process_logout()
{
    logout_user(); // Calling auth.php logout helper function
    header("Location: index.php?action=login&status=logged_out");
    exit();
}
