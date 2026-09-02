<?php
function adminCtrl($conn)
{
    $action = $_GET['action'] ?? 'dashboard';
    $error = $success = '';

    //user management code
    if ($action === 'users') {
        //user deletion handling
        if (isset($_GET['delete']) && isset($_GET['id'])) {
            $userId = intval($_GET['id']);

            // self-deletion prevention code
            if ($userId === $_SESSION['user']['id']) {
                $error = "You can't delete your own account";
            } else {
                deleteUser($conn, $userId);
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['success' => true]);
                    exit;
                }
                header("Location: index.php?page=admin&action=users&msg=user_deleted");
                exit;
            }
        }

        // new user creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $pass = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $v = intval($_POST['is_verified'] ?? 0);

            if (emailExists($conn, $email)) {
                $error = "This email is already registered.";
            } else {
                if (adminAddUser($conn, $name, $email, $pass, $role, $v, $phone)) {
                    header("Location: index.php?page=admin&action=users&msg=user_added");
                    exit;
                }
            }
        }

        // verification toggle
        if (isset($_GET['verify']) && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            $status = intval($_GET['verify']);
            if (verifyUser($conn, $userId, $status)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['success' => true]);
                    exit;
                }
                header("Location: index.php?page=admin&action=users&msg=user_updated");
                exit;
            }
        }

        // role change code
        if (isset($_GET['new_role']) && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            $role = $_GET['new_role'];
            if (updateUserRole($conn, $userId, $role)) {
                header("Location: index.php?page=admin&action=users&msg=user_updated");
                exit;
            }
        }

        $users = getAllUsers($conn);
        require 'app/views/admin/manage_users.php';
        return;
    }

    // notification pannel
    if ($action === 'notifications') {
        if (isset($_GET['delete']) && isset($_GET['id'])) {
            deleteNotification($conn, intval($_GET['id']));
            header("Location: index.php?page=admin&action=notifications&msg=notification_deleted");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notification'])) {
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $targetRole = $_POST['target_role'] ?? 'all';

            if ($title === '' || $message === '') {
                $error = 'Title and message are required.';
            } else {
                createNotification($conn, $title, $message, $targetRole);
                header("Location: index.php?page=admin&action=notifications&msg=notification_sent");
                exit;
            }
        }

        $notifications = getAllNotifications($conn);
        require 'app/views/admin/notifications.php';
        return;
    }

    // discount pannel
    if ($action === 'discounts') {
        if (isset($_GET['toggle']) && isset($_GET['id'])) {
            $status = $_GET['toggle'] === 'active' ? 'active' : 'expired';
            toggleDiscountStatus($conn, intval($_GET['id']), $status);
            header("Location: index.php?page=admin&action=discounts&msg=discount_updated");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_discount'])) {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $pct = floatval($_POST['discount_pct'] ?? 0);
            $validTill = $_POST['valid_till'] ?? '';

            if ($code === '' || $validTill === '') {
                $error = 'Code and valid-till date are required.';
            } else {
                if (createDiscount($conn, $code, $pct, $validTill)) {
                    header("Location: index.php?page=admin&action=discounts&msg=discount_added");
                    exit;
                } else {
                    $error = 'That discount code already exists.';
                }
            }
        }

        $discounts = getAllDiscounts($conn);
        require 'app/views/admin/discounts.php';
        return;
    }

    // special request monitoring
    if ($action === 'requests') {
        $specialRequests = getAllSpecialRequests($conn);
        require 'app/views/admin/moderate_requests.php';
        return;
    }

    // admin pannel
    $stats = getDashboardStats($conn);
    require 'app/views/admin/dashboard.php';
}
