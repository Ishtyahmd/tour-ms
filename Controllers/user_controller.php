<?php
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
