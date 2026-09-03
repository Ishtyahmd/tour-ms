<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">

</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127757;</div>
        <h1><?= APP_NAME ?></h1>
        <p>Plan tours, book guides, hotels and vehicles. Sign in to continue.</p>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p class="muted">Please sign in to continue</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" class="form">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($prefill ?? '') ?>"
                           placeholder="example@email.com" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
            </form>

            <p class="auth-foot">Don't have an account? <a href="index.php?page=registration">Register here</a></p>

            <div class="test-account">
                <strong>Test Account:</strong><br>
                Admin: admin@tourms.com | Password: admin123
            </div>
        </div>
    </div>
</div>

</body>
</html>
