<?php
function loginCtrl($conn) {
    $error = '';
    $success = '';

    if (isset($_SESSION['success_flash'])) {
        $success = $_SESSION['success_flash'];
        unset($_SESSION['success_flash']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $error = 'Please enter both email and password';
        } else {
            $user = authUser($conn, $email, $password);

            if ($user) {
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                    'is_verified' => $user['is_verified'],
                    'profile_picture' => $user['profile_picture'] ?? ''
                ];

                // flat session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                //Remember Me cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $hashed_token = hash('sha256', $token);
                    updateRememberToken($conn, $user['id'], $hashed_token);
                    setcookie('remember_token', $token, time() + 86400 * 30, '/');
                } else {
                    updateRememberToken($conn, $user['id'], NULL);
                    setcookie('remember_token', '', time() - 3600, '/');
                }

                // redirection based on roles
                if ($user['role'] === 'admin') {
                    header('Location: index.php?page=admin');
                } elseif ($user['role'] === 'guide') {
                    header('Location: index.php?page=guide');
                } elseif ($user['role'] === 'vendor') {
                    header('Location: index.php?page=vendor');
                } else {
                    header('Location: index.php?page=user');
                }
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        }
    }

    require 'app/views/auth/login.php';
}

//registration Page and Logic
function registerCtrl($conn) {
    $error = $success = '';
    $old = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'user';
        $old = compact('name', 'email', 'phone', 'role');

        //role-specific fields
        $guideLocation = trim($_POST['location'] ?? '');
        $guideRate     = $_POST['daily_rate'] ?? 0;
        $vendorType    = $_POST['vendor_type'] ?? 'hotel';
        $vendorCompany = trim($_POST['company_name'] ?? '');
        $vendorAddress = trim($_POST['address'] ?? '');

        // role validation
        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (emailExists($conn, $email)) {
            $error = 'This email is already registered.';
        } elseif ($role === 'guide' && $guideLocation === '') {
            $error = 'Please provide your guiding location.';
        } elseif ($role === 'vendor' && $vendorCompany === '') {
            $error = 'Please provide your company name.';
        } else {
            // model function call
            $newId = addUser($conn, $name, $email, $password, $role, $phone);
            if ($newId) {
                if ($role === 'guide') {
                    createRoleProfile($conn, $newId, 'guide', [
                        'location' => $guideLocation,
                        'daily_rate' => $guideRate
                    ]);
                } elseif ($role === 'vendor') {
                    createRoleProfile($conn, $newId, 'vendor', [
                        'type' => $vendorType,
                        'company_name' => $vendorCompany,
                        'address' => $vendorAddress
                    ]);
                }

                $_SESSION['success_flash'] = 'Admin needs to verify the account first. You can log in after approval!';
                header('Location: index.php?page=login');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }

    require 'app/views/auth/registration.php';
}
?>
