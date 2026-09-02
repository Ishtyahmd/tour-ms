<?php
function adminCtrl($conn) {
    $action = $_GET['action'] ?? 'dashboard'; 
    $error = $success = '';

    //USER MANAGEMENT
    if ($action === 'users') {
        //Handle user deletion
        if (isset($_GET['delete']) && isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            
            // Prevent self-deletion
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

        //Handle new user creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $v = intval($_POST['is_verified'] ?? 0);

            // Check for unique email
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
                $error = "This email is already registered.";
            } else {
                if (adminAddUser($conn, $name, $email, $pass, $role, $v)) {
                    header("Location: index.php?page=admin&action=users&msg=user_added");
                    exit;
                }
            }
        }

        //Handle verification toggle
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

        //Handle role change
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

    //POST MODERATION
    if ($action === 'posts') {
        //Approve a request
        if (isset($_GET['approve']) && isset($_GET['id'])) {
            $requestId = intval($_GET['id']);
            if (approvePostRequest($conn, $requestId)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['success' => true]);
                    exit;
                }
                header("Location: index.php?page=admin&action=posts&msg=post_approved");
                exit;
            }
        }
        //Reject a request
        if (isset($_GET['reject']) && isset($_GET['id'])) {
            $requestId = intval($_GET['id']);
            if (rejectPostRequest($conn, $requestId)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    echo json_encode(['success' => true]);
                    exit;
                }
                header("Location: index.php?page=admin&action=posts&msg=post_rejected");
                exit;
            }
        }

        //Delete a published post
        if (isset($_GET['delete_post']) && isset($_GET['id'])) {
            $postId = intval($_GET['id']);
            if (deletePostAdmin($conn, $postId)) {
                header("Location: index.php?page=admin&action=posts&msg=post_deleted");
                exit;
            }
        }

        //Update an existing post
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
            $postId = intval($_POST['post_id']);
            $data = [
                'title' => trim($_POST['title']),
                'country' => trim($_POST['country']),
                'short_history' => trim($_POST['short_history']),
                'genre' => trim($_POST['genre']),
                'cost_level' => trim($_POST['cost_level']),
                'travel_medium_info' => trim($_POST['travel_medium_info']),
                'image_path' => $_POST['image_path'] ?? ''
            ];
            if (updatePostAdmin($conn, $postId, $data)) {
                header("Location: index.php?page=admin&action=posts&msg=post_updated");
                exit;
            }
        }

        $pendingRequests = getPendingRequests($conn);
        $allPosts = getAllPostsAdmin($conn);
        require 'app/views/admin/moderate_posts.php';
        return;
    }
    //COMMENT MODERATION
    if ($action === 'comments') {
        //Delete a comment
        if (isset($_GET['delete']) && isset($_GET['id'])) {
            $commentId = intval($_GET['id']);
            deleteCommentByAdmin($conn, $commentId);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => true]);
                exit;
            }
            header("Location: index.php?page=admin&action=comments&msg=comment_deleted");
            exit;
        }

        $comments = getAllComments($conn);
        require 'app/views/admin/moderate_comments.php';
        return;
    }

    //ADMIN DASHBOARD
    $stats = getDashboardStats($conn);
    require 'app/views/admin/dashboard.php';
}
?>
