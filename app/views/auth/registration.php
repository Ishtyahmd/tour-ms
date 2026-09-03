<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>

<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">&#127757;</div>
        <h1>Join Us</h1>
        <p>Create an account to plan tours, guide travelers, or list your hotel or vehicle services</p>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p class="muted">Join the TourMS community</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=registration" class="form" id="register-form">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                           placeholder="Enter your name" required>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               placeholder="example@email.com" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                               placeholder="e.g. 01712345678">
                    </div>
                </div>
                <div class="field">
                    <label for="role">I want to be a...</label>
                    <select name="role" id="role">
                        <option value="user" <?= ($old['role'] == 'user') ? 'selected' : '' ?>>Traveler (General User)</option>
                        <option value="guide" <?= ($old['role'] == 'guide') ? 'selected' : '' ?>>Tour Guide</option>
                        <option value="vendor" <?= ($old['role'] == 'vendor') ? 'selected' : '' ?>>Vendor (Hotel / Transport)</option>
                    </select>
                </div>

                <div class="field-group" id="guide-fields" style="display:none;">
                    <div class="field-row">
                        <div class="field">
                            <label for="location">Guiding Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Cox's Bazar">
                        </div>
                        <div class="field">
                            <label for="daily_rate">Daily Rate (USD)</label>
                            <input type="number" step="0.01" min="0" id="daily_rate" name="daily_rate" placeholder="e.g. 50.00">
                        </div>
                    </div>
                </div>

                <div class="field-group" id="vendor-fields" style="display:none;">
                    <div class="field-row">
                        <div class="field">
                            <label for="vendor_type">Vendor Type</label>
                            <select name="vendor_type" id="vendor_type">
                                <option value="hotel">Hotel</option>
                                <option value="transport">Transport</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" placeholder="Business name">
                        </div>
                    </div>
                    <div class="field">
                        <label for="address">Business Address</label>
                        <input type="text" id="address" name="address" placeholder="Street, city">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Type your password again" required>
                    </div>
                </div>
                <div class="field">
                    <button type="submit" class="btn btn-primary">Register Account</button>
                </div>
            </form>

            <p class="auth-foot">Already have an account? <a href="index.php?page=login">Sign in</a></p>
        </div>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('role');
    const guideFields = document.getElementById('guide-fields');
    const vendorFields = document.getElementById('vendor-fields');

    function toggleRoleFields() {
        guideFields.style.display = roleSelect.value === 'guide' ? 'block' : 'none';
        vendorFields.style.display = roleSelect.value === 'vendor' ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleRoleFields);
    toggleRoleFields();
</script>

</body>
</html>
