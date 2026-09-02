<?php
//user management
function getAllUsers($conn) {
    $r = mysqli_query($conn, "SELECT id, name, email, phone, role, is_verified FROM users ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function searchUsers($conn, $q) {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, role, is_verified FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function verifyUser($conn, $userId, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_verified = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $status, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteUser($conn, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function adminAddUser($conn, $name, $email, $password, $role, $verified, $phone = null) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password_hash, phone, role, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $hashed, $phone, $role, $verified);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserRole($conn, $userId, $role) {
    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $role, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//admin notification dashboard
function getAllNotifications($conn) {
    $r = mysqli_query($conn, "SELECT * FROM notifications ORDER BY created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function createNotification($conn, $title, $message, $targetRole) {
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (title, message, target_role) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $title, $message, $targetRole);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteNotification($conn, $id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM notifications WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//revenue dashboard
function getAllDiscounts($conn) {
    $r = mysqli_query($conn, "SELECT * FROM discounts ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function createDiscount($conn, $code, $pct, $validTill) {
    $stmt = mysqli_prepare($conn, "INSERT INTO discounts (code, discount_pct, valid_till) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sds', $code, $pct, $validTill);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function toggleDiscountStatus($conn, $id, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE discounts SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//special request moderation
function getAllSpecialRequests($conn) {
    $stmt = mysqli_prepare($conn, "SELECT sr.*, u.name AS user_name, v.company_name
                                    FROM special_requests sr
                                    JOIN users u ON sr.user_id = u.id
                                    JOIN vendors v ON sr.vendor_id = v.id
                                    ORDER BY sr.created_at DESC");
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function updateSpecialRequestStatus($conn, $id, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE special_requests SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//overall
function getDashboardStats($conn) {
    $stats = [];

    $r = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stats['users_by_role'] = mysqli_fetch_all($r, MYSQLI_ASSOC);

    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = mysqli_fetch_assoc($r)['count'];

    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_verified = 0");
    $stats['pending_verification'] = mysqli_fetch_assoc($r)['count'];

    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM tours");
    $stats['total_tours'] = mysqli_fetch_assoc($r)['count'];

    $r = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM tours GROUP BY status");
    $stats['tours_by_status'] = mysqli_fetch_all($r, MYSQLI_ASSOC);

    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM listings");
    $stats['total_listings'] = mysqli_fetch_assoc($r)['count'];

    $r = mysqli_query($conn, "SELECT COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue FROM bookings WHERE status = 'confirmed'");
    $row = mysqli_fetch_assoc($r);
    $stats['total_bookings'] = $row['count'];
    $stats['total_revenue'] = $row['revenue'];

    $r = mysqli_query($conn, "SELECT COUNT(*) as count FROM special_requests WHERE status = 'pending'");
    $stats['pending_requests'] = mysqli_fetch_assoc($r)['count'];

    return $stats;
}
?>
