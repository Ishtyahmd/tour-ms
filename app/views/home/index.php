<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <?php if (!isset($_SESSION['user'])): ?>
        <!-- VIEW FOR NON-REGISTERED USERS -->
        <div class="page-header">
            <h1 class="page-title">Plan Your Perfect Tour</h1>
            <p class="page-sub">Book guides, hotels, and vehicles all in one place — with verified reviews and transparent pricing.</p>
            <div class="hero-actions home-actions">
                <a href="index.php?page=registration" class="btn btn-primary">Join Now</a>
                <a href="index.php?page=login" class="btn btn-ghost">Sign In</a>
            </div>
        </div>

    <?php elseif ($_SESSION['user']['is_verified'] == 0): ?>
        <!-- VIEW FOR LOGGED-IN BUT NOT VERIFIED USERS -->
        <div class="page-header">
            <div class="card">
                <h1 class="page-title">Verification Pending</h1>
                <p class="page-sub">Your account is currently being reviewed by our administrators.</p>
                <div class="alert alert-warning">
                    <strong>Notice:</strong> Your account is pending admin approval. Please wait for access to detailed site features.
                </div>
                <a href="index.php?page=logout" class="btn btn-ghost">Logout</a>
            </div>
        </div>

    <?php else: ?>
        <!-- VERIFIED USER: role-appropriate welcome -->
        <div class="page-header">
            <h1 class="page-title">Welcome back, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>
            <p class="page-sub">
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    Head to your dashboard to manage the platform.
                <?php elseif ($_SESSION['user']['role'] === 'guide'): ?>
                    Head to your Guide Panel to manage your availability and assigned tours.
                <?php elseif ($_SESSION['user']['role'] === 'vendor'): ?>
                    Head to your Vendor Panel to manage your listings and bookings.
                <?php else: ?>
                    Head to Explore to plan and book your next tour.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
