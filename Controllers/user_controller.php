<?php
<<<<<<< HEAD
function profileCtrl($conn) {
    $userId = $_SESSION['user']['id'];
    $error = $success = '';
    $action = $_GET['action'] ?? 'view';

    //Update Profile Info (Name, Email)
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $error = 'Name and Email required';
        } elseif (emailExists($conn, $email, $userId)) {
            $error = 'Email already taken';
        } else {
            if (updateUserInfo($conn, $userId, $name, $email)) {
                header("Location: index.php?page=profile&msg=updated");
                exit;
            }
            $error = 'Failed update profile';
        }
    }

    //Change Password
    if ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        //Fetch current hash from DB to verify
        $currentHash = getPasswordHashById($conn, $userId);

        if ($currentPass === '' || $newPass === '') {
            $error = 'Please fill all passwords';
        } elseif (!password_verify($currentPass, $currentHash)) {
            $error = 'Current password incorrect';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be 8+ characters';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New passwords do not match';
        } else {
            if (updatePassword($conn, $userId, $newPass)) {
                header("Location: index.php?page=profile&msg=password_changed");
                exit;
            }
            $error = 'Failed to update password';
        }
    }

    //Update Profile Picture
    if ($action === 'upload_pic' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $file = $_FILES['profile_pic'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array(strtolower($ext), $allowed)) {
                $error = 'Invalid file type. Please upload an image (JPG, PNG, JPEG).';
            } else {
                //Create folder if not exists
                if (!is_dir('public/uploads')) { mkdir('public/uploads', 0777, true); }
                
                $filename = uniqid('user_' . $userId . '_', true) . '.' . $ext;
                $dest = 'public/uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    updateProfilePic($conn, $userId, $dest);
                    $_SESSION['user']['profile_picture'] = $dest;
                    header("Location: index.php?page=profile&msg=pic_updated");
                    exit;
                } else {
                    $error = 'Failed move uploaded file';
                }
            }
        } else {
            $error = 'No image uploaded or upload error';
        }
    }

    //Delete Profile Picture
    if ($action === 'delete_pic') {
        if (!empty($_SESSION['user']['profile_picture'])) {
            $oldPic = $_SESSION['user']['profile_picture'];
            if (file_exists($oldPic)) {
                unlink($oldPic);
            }
            updateProfilePic($conn, $userId, null);
            $_SESSION['user']['profile_picture'] = null;
            header("Location: index.php?page=profile&msg=pic_updated");
            exit;
        }
    }

    //Fetch the latest user data to show in the view
    $user = getUserById($conn, $userId);

    require 'app/views/profile/view.php';
}
?>
=======

/**
 * User Controller (Customer Actions)
 * Paradigm: Pure Procedural PHP
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/rating_model.php';

/**
 * Renders the primary user dashboard.
 */
function render_user_dashboard()
{
    $conn = db_connect();
    $user_id = get_logged_in_user_id();

    // Fetch user dashboard data using procedural model functions
    $special_requests = get_user_special_requests($conn, $user_id);
    $user_bookings    = get_user_bookings($conn, $user_id);
    $vendors_list     = get_all_vendors($conn);

    db_close($conn);

    // Render dashboard view
    require_once __DIR__ . '/../views/user/dashboard.php';
}

/**
 * Renders the dedicated form to submit special requests.
 */
function render_special_request_form()
{
    $conn = db_connect();
    $vendors_list = get_all_vendors($conn);
    db_close($conn);

    require_once __DIR__ . '/../views/user/special_request_form.php';
}

/**
 * Processes special request form submissions.
 */
function handle_special_request_submission()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=user_dashboard");
        exit();
    }

    $user_id      = get_logged_in_user_id();
    $vendor_id    = isset($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : 0;
    $request_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
    $details      = isset($_POST['details']) ? trim($_POST['details']) : '';

    if ($vendor_id <= 0 || empty($request_type) || empty($details)) {
        header("Location: index.php?action=submit_special_request&error=empty_fields");
        exit();
    }

    $conn = db_connect();
    $success = create_special_request($conn, $user_id, $vendor_id, $request_type, $details);
    db_close($conn);

    if ($success) {
        header("Location: index.php?action=user_dashboard&status=request_submitted");
    } else {
        header("Location: index.php?action=submit_special_request&error=failed");
    }
    exit();
}

/**
 * Processes standard HTTP POST submission for hotel/guide ratings.
 */
function handle_rating_submission()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=user_dashboard");
        exit();
    }

    $user_id     = get_logged_in_user_id();
    $target_type = isset($_POST['target_type']) ? trim($_POST['target_type']) : '';
    $target_id   = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
    $rating      = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review      = isset($_POST['review']) ? trim($_POST['review']) : '';

    if ($target_id <= 0 || $rating < 1 || $rating > 5 || !in_array($target_type, array('hotel', 'guide'))) {
        header("Location: index.php?action=user_dashboard&error=invalid_rating");
        exit();
    }

    $conn = db_connect();
    $success = submit_user_rating($conn, $user_id, $target_type, $target_id, $rating, $review);
    db_close($conn);

    if ($success) {
        header("Location: index.php?action=user_dashboard&status=rating_saved");
    } else {
        header("Location: index.php?action=user_dashboard&error=rating_failed");
    }
    exit();
}
>>>>>>> 50bd9b5c8289da9598e8516b10b5f4df52d12195
