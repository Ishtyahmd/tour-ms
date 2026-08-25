
<?php
// ================================================================
// FRONT CONTROLLER (router)
// Handles routing, AJAX search endpoint, logout, role checks.
// ================================================================
session_start();

//require 'config.php';
//require 'models.php';
//require 'controllers.php';

$page = $_GET['page'] ?? 'login';

/* -- Logout --*/
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_user', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}


if ($page === 'ajax') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $type = $_GET['type'] ?? '';
    $q    = trim($_GET['q'] ?? '');

    if ($type === 'librarian' && $_SESSION['user']['role'] === 'admin') {
        echo json_encode($q === '' ? getLibrarians($conn) : searchLibrarians($conn, $q));
    } elseif ($type === 'book' && $_SESSION['user']['role'] === 'librarian') {
        echo json_encode($q === '' ? getBooks($conn) : searchBooks($conn, $q));
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
    }
    exit;
}

/* -- Auth gates -- */
$publicPages = ['login', 'register'];

// Already logged in -> skip login/register
if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    header('Location: index.php?page=' . $_SESSION['user']['role']);
    exit;
}

// Protected pages require login
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

// Role gate
if ($page === 'admin'     && $_SESSION['user']['role'] !== 'admin')      { header('Location: index.php?page=login'); exit; }
if ($page === 'librarian' && $_SESSION['user']['role'] !== 'librarian')  { header('Location: index.php?page=login'); exit; }

/* -- Dispatch -- */
switch ($page) {
    case 'login':     loginCtrl($conn);     break;
    case 'register':  registerCtrl($conn);  break;
    case 'admin':     adminCtrl($conn);     break;
    case 'librarian': librarianCtrl($conn); break;
    default:
        header('Location: index.php?page=login');
        exit;
}

mysqli_close($conn);
?>