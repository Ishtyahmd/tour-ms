<?php
$user = $_SESSION['user'];

$statusMessages = [
    'claimed' => ['success', 'Tour claimed successfully!'],
    'claim_failed' => ['error', 'Could not claim that tour — it may have just been taken.'],
    'completed' => ['success', 'Tour marked as completed.'],
    'canceled' => ['success', 'Tour returned to the pending pool.'],
    'status_updated' => ['success', 'Availability updated.'],
    'status_blocked' => ['error', "You can't change availability while you have active assigned tours."],
    'profile_updated' => ['success', 'Profile updated.'],
    'error' => ['error', 'Something went wrong.']
];
$flash = isset($_GET['msg']) ? ($statusMessages[$_GET['msg']] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guide Panel &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Guide Panel</h1>
        <p class="page-sub">Claim tour requests, manage your assignments, and update your availability.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash[0] ?>"><?= $flash[1] ?></div>
    <?php endif; ?>

    <div class="admin-stack">
        <!-- PROFILE / AVAILABILITY CARD -->
        <div class="card stat-card">
            <h3 class="card-title">Your Profile</h3>
            <p><strong>Location:</strong> <?= htmlspecialchars($guide['location']) ?></p>
            <p><strong>Daily Rate:</strong> $<?= number_format($guide['daily_rate'], 2) ?></p>
            <p>
                <strong>Status:</strong>
                <span class="cost-badge <?= $guide['status'] === 'available' ? 'low' : ($guide['status'] === 'assigned' ? 'medium' : 'high') ?>">
                    <?= ucfirst($guide['status']) ?>
                </span>
            </p>
            <p class="muted">
                <?= (int)$ratingSummary['count'] ?> rating(s), average
                <?= number_format($ratingSummary['avg_rating'], 1) ?> / 5
            </p>

            <?php if ($guide['status'] !== 'assigned'): ?>
                <?php if ($guide['status'] === 'available'): ?>
                    <a href="index.php?page=guide&action=toggle_status&status=offline" class="btn btn-ghost btn-full">Go Offline</a>
                <?php else: ?>
                    <a href="index.php?page=guide&action=toggle_status&status=available" class="btn btn-primary btn-full">Go Available</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted">Availability is locked while you have an active tour.</p>
            <?php endif; ?>

            <details style="margin-top:1rem;">
                <summary style="cursor:pointer;">Edit location / rate</summary>
                <form method="POST" action="index.php?page=guide&action=update_profile" class="form form-aligned" style="margin-top:0.75rem;">
                    <div class="field">
                        <label>Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($guide['location']) ?>" required>
                    </div>
                    <div class="field">
                        <label>Daily Rate (USD)</label>
                        <input type="number" step="0.01" min="0" name="daily_rate" value="<?= htmlspecialchars($guide['daily_rate']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </details>
        </div>
    </div>

    <!-- PENDING TOURS TO CLAIM -->
    <div class="card card-margin-bottom" style="margin-top:1.5rem;">
        <div class="card-toolbar">
            <h3 class="card-title">Available Tour Requests</h3>
            <span class="badge"><?= count($pendingTours) ?> pending</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Traveler</th>
                        <th>Dates</th>
                        <th>Est. Price</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingTours)): ?>
                        <tr><td colspan="5" class="empty">No pending tour requests right now.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pendingTours as $t):
                            $start = new DateTime($t['start_date']);
                            $end = new DateTime($t['end_date']);
                            $days = max(1, $start->diff($end)->days + 1);
                            $estPrice = $days * $guide['daily_rate'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($t['title']) ?></td>
                                <td><?= htmlspecialchars($t['traveler_name']) ?></td>
                                <td><?= htmlspecialchars($t['start_date']) ?> &rarr; <?= htmlspecialchars($t['end_date']) ?> (<?= $days ?>d)</td>
                                <td>$<?= number_format($estPrice, 2) ?></td>
                                <td class="text-right">
                                    <?php if ($guide['status'] === 'available'): ?>
                                        <form method="POST" action="index.php?page=guide&action=claim" style="display:inline;">
                                            <input type="hidden" name="tour_id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn-sm btn-edit">Claim</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MY TOURS -->
    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Your Tours</h3>
            <span class="badge"><?= count($myTours) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Traveler</th>
                        <th>Dates</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myTours)): ?>
                        <tr><td colspan="6" class="empty">You haven't claimed any tours yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myTours as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['title']) ?></td>
                                <td>
                                    <?= htmlspecialchars($t['traveler_name']) ?>
                                    <?php if (!empty($t['traveler_phone'])): ?>
                                        <br><span class="muted"><?= htmlspecialchars($t['traveler_phone']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($t['start_date']) ?> &rarr; <?= htmlspecialchars($t['end_date']) ?></td>
                                <td>$<?= number_format($t['price'], 2) ?></td>
                                <td>
                                    <span class="cost-badge <?= $t['status'] === 'completed' ? 'low' : ($t['status'] === 'assigned' ? 'medium' : 'high') ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($t['status'] === 'assigned'): ?>
                                        <a class="btn-sm btn-edit" href="index.php?page=guide&action=complete&id=<?= $t['id'] ?>">Mark Completed</a>
                                        <a class="btn-sm btn-delete" href="index.php?page=guide&action=cancel&id=<?= $t['id'] ?>"
                                           onclick="return confirm('Cancel this tour? It will go back to the pending pool.')">Cancel</a>
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
