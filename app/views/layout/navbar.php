<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=<?= isset($_SESSION['user']) ? ($_SESSION['user']['role'] === 'user' ? 'user' : $_SESSION['user']['role']) : 'home' ?>">
            <span class="brand-icon">&#127757;</span>
            <span><?= APP_NAME ?></span>
        </a>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_verified'] == 1): ?>
            <div class="nav-links">
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="index.php?page=admin">Admin Dashboard</a>
                    <a href="index.php?page=admin&action=users">Users</a>
                    <a href="index.php?page=admin&action=discounts">Discounts</a>
                    <a href="index.php?page=admin&action=requests">Requests</a>
                    <a href="index.php?page=admin&action=notifications">Notifications</a>
                <?php elseif ($_SESSION['user']['role'] === 'guide'): ?>
                    <a href="index.php?page=guide">Guide Panel</a>
                <?php elseif ($_SESSION['user']['role'] === 'vendor'): ?>
                    <a href="index.php?page=vendor">Vendor Panel</a>
                    <a href="index.php?page=vendor&action=requests">Requests</a>
                <?php elseif ($_SESSION['user']['role'] === 'user'): ?>
                    <a href="index.php?page=user">Explore</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="nav-user">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="index.php?page=profile" class="user-pill">
                    <?php if (!empty($_SESSION['user']['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($_SESSION['user']['profile_picture']) ?>" class="user-avatar" alt="Profile">
                    <?php else: ?>
                        <span class="user-avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></span>
                    <?php endif; ?>
                    <span class="user-meta">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                        <span class="user-role"><?= ucfirst($_SESSION['user']['role']) ?></span>
                    </span>
                </a>
                <a href="index.php?page=logout" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-ghost">Login</a>
                <a href="index.php?page=registration" class="btn btn-primary">Join Now</a>
            <?php endif; ?>
        </div>
    </div>
</header>
