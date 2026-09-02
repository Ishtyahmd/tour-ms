<?php
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">User Management</h1>
                <p class="page-sub">Verify new accounts and manage system access</p>
            </div>
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <?php
            $messages = [
                'user_updated' => 'User account updated successfully',
                'user_deleted' => 'User account has been removed',
                'user_added' => 'New user account created successfully'
            ];
            $msg = $messages[$_GET['msg']] ?? null;
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <!-- ADD USER FORM -->
    <div class="card card-margin-bottom">
        <h3 class="card-title">Add New System User</h3>
        <form method="POST" action="index.php?page=admin&action=users" class="form form-aligned">
            <input type="hidden" name="add_user" value="1">
            <div class="field-row">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="User name">
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="example@email.com">
                </div>
                <div class="field">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="Optional">
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters">
                </div>
                <div class="field">
                    <label>System Role</label>
                    <select name="role">
                        <option value="user">General User</option>
                        <option value="guide">Tour Guide</option>
                        <option value="vendor">Vendor</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="field">
                    <label>Initial Status</label>
                    <select name="is_verified">
                        <option value="1">Verified (Active)</option>
                        <option value="0">Pending (Locked)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>
    </div>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Registered Users</h3>
            <span class="badge"><?= count($users) ?> total users</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Verification</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="empty">No users found in the database.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                            <tr id="user-row-<?= $u['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
                                <td>
                                    <select onchange="location.href='index.php?page=admin&action=users&id=<?= $u['id'] ?>&new_role='+this.value">
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="guide" <?= $u['role'] === 'guide' ? 'selected' : '' ?>>Guide</option>
                                        <option value="vendor" <?= $u['role'] === 'vendor' ? 'selected' : '' ?>>Vendor</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="cost-badge <?= $u['is_verified'] ? 'low' : 'high' ?>">
                                        <?= $u['is_verified'] ? 'Verified' : 'Pending' ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <!-- Toggle Verification -->
                                    <button class="btn-sm btn-edit" id="verify-btn-<?= $u['id'] ?>"
                                       onclick="toggleVerify(<?= $u['id'] ?>, <?= $u['is_verified'] ? '0' : '1' ?>)">
                                        <?= $u['is_verified'] ? 'Unverify' : 'Verify' ?>
                                    </button>
                                    <!-- Delete User -->
                                    <button class="btn-sm btn-delete"
                                       onclick="deleteUser(<?= $u['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
function toggleVerify(id, status) {
    fetch('index.php?page=admin&action=users&verify=' + status + '&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteUser(id) {
    if (!confirm('Delete this user and all their data?')) return;

    fetch('index.php?page=admin&action=users&delete=1&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('user-row-' + id).remove();
        } else {
            alert(data.message || 'Error deleting user.');
        }
    });
}
</script>
</body>
</html>
