<?php
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Requests &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">Special Requests</h1>
                <p class="page-sub">view only — vendors approve or reject requests directly</p>
            </div>
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Special Requests</h3>
            <span class="badge"><?= count($specialRequests) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Vendor</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($specialRequests)): ?>
                        <tr><td colspan="5" class="empty">No special requests submitted yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($specialRequests as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['user_name']) ?></td>
                                <td><?= htmlspecialchars($r['company_name']) ?></td>
                                <td><?= htmlspecialchars($r['request_type']) ?></td>
                                <td><?= htmlspecialchars($r['details']) ?></td>
                                <td>
                                    <span class="cost-badge <?= $r['status'] === 'approved' ? 'low' : ($r['status'] === 'rejected' ? 'high' : 'medium') ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
