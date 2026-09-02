<?php
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discounts &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">Discounts & Revenue</h1>
                <p class="page-sub">Create and manage promotional discount codes</p>
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
                'discount_added' => 'Discount code created successfully',
                'discount_updated' => 'Discount status updated'
            ];
            $msg = $messages[$_GET['msg']] ?? null;
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card card-margin-bottom">
        <h3 class="card-title">New Discount Code</h3>
        <form method="POST" action="index.php?page=admin&action=discounts" class="form form-aligned">
            <input type="hidden" name="add_discount" value="1">
            <div class="field-row">
                <div class="field">
                    <label>Code</label>
                    <input type="text" name="code" required placeholder="e.g. SUMMER25">
                </div>
                <div class="field">
                    <label>Discount %</label>
                    <input type="number" step="0.01" min="0" max="100" name="discount_pct" required placeholder="e.g. 15">
                </div>
                <div class="field">
                    <label>Valid Till</label>
                    <input type="date" name="valid_till" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Discount</button>
        </form>
    </div>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Discount Codes</h3>
            <span class="badge"><?= count($discounts) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Valid Till</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($discounts)): ?>
                        <tr><td colspan="5" class="empty">No discount codes yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($discounts as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['code']) ?></strong></td>
                                <td><?= htmlspecialchars($d['discount_pct']) ?>%</td>
                                <td><?= htmlspecialchars($d['valid_till']) ?></td>
                                <td>
                                    <span class="cost-badge <?= $d['status'] === 'active' ? 'low' : 'high' ?>">
                                        <?= ucfirst($d['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($d['status'] === 'active'): ?>
                                        <a class="btn-sm btn-edit" href="index.php?page=admin&action=discounts&toggle=expired&id=<?= $d['id'] ?>">Expire</a>
                                    <?php else: ?>
                                        <a class="btn-sm btn-edit" href="index.php?page=admin&action=discounts&toggle=active&id=<?= $d['id'] ?>">Reactivate</a>
                                    <?php endif; ?>
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
