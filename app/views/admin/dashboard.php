<?php
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Administrative Control Panel</h1>
        <p class="page-sub">Welcome back, Administrator. Overview of system health and pending tasks.</p>
    </div>

    <div class="admin-stack">
        <div class="card stat-card">
            <h3 class="card-title">User Community</h3>
            <div class="stat-value">
                <?= $stats['total_users'] ?>
            </div>
            <div class="user-role-breakdown">
                <?php
                $roles = [];
                foreach ($stats['users_by_role'] as $roleStat) {
                    $roles[] = "<strong>" . ucfirst($roleStat['role']) . ":</strong> " . $roleStat['count'];
                }
                echo implode(' | ', $roles);
                ?>
            </div>
            <p class="muted"><?= $stats['pending_verification'] ?> account(s) awaiting verification</p>
            <a href="index.php?page=admin&action=users" class="btn btn-primary btn-full">Manage Users</a>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Tours & Listings</h3>
            <div class="engagement-row">
                <div class="stat-item">
                    <div class="stat-sub-value"><?= $stats['total_tours'] ?></div>
                    <div class="stat-label">TOTAL TOURS</div>
                </div>
                <div class="stat-item">
                    <div class="stat-sub-value"><?= $stats['total_listings'] ?></div>
                    <div class="stat-label">LISTINGS</div>
                </div>
            </div>
            <div class="user-role-breakdown">
                <?php
                $tstats = [];
                foreach ($stats['tours_by_status'] as $t) {
                    $tstats[] = "<strong>" . ucfirst($t['status']) . ":</strong> " . $t['count'];
                }
                echo implode(' | ', $tstats);
                ?>
            </div>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Revenue & Bookings</h3>
            <div class="stat-value">
                $<?= number_format($stats['total_revenue'], 2) ?>
            </div>
            <p class="muted"><?= $stats['total_bookings'] ?> confirmed booking(s)</p>
            <a href="index.php?page=admin&action=discounts" class="btn btn-primary btn-full">Manage Discounts</a>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Special Requests</h3>
            <div class="stat-value warning">
                <?= $stats['pending_requests'] ?>
            </div>
            <p class="muted">Pending vendor requests</p>
            <a href="index.php?page=admin&action=requests" class="btn btn-primary btn-full">Review Requests</a>
        </div>

        <div class="card stat-card">
            <h3 class="card-title">Broadcast Notifications</h3>
            <p class="muted">Send announcements to all users or a specific role.</p>
            <a href="index.php?page=admin&action=notifications" class="btn btn-primary btn-full">Manage Notifications</a>
        </div>
    </div>
</main>

</body>
</html>
