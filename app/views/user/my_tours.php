<?php
$user = $_SESSION['user'];

$statusMessages = [
    'tour_requested' => ['success', 'Your tour request has been submitted.'],
    'tour_canceled' => ['success', 'Tour request canceled.'],
    'rated' => ['success', 'Thanks for your rating!'],
    'already_rated' => ['error', "You've already rated this guide for this tour, or it isn't eligible yet."],
    'error' => ['error', 'Something went wrong.']
];
$flash = isset($_GET['msg']) ? ($statusMessages[$_GET['msg']] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tours &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">My Tours</h1>
                <p class="page-sub">Track your tour requests from submission to completion.</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">&larr; Back to Explore</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash[0] ?>"><?= $flash[1] ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Requests</h3>
            <span class="badge"><?= count($tours) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Dates</th>
                        <th>Guide</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tours)): ?>
                        <tr><td colspan="6" class="empty">You haven't requested any tours yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tours as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['title']) ?></td>
                                <td><?= htmlspecialchars($t['start_date']) ?> &rarr; <?= htmlspecialchars($t['end_date']) ?></td>
                                <td><?= $t['guide_name'] ? htmlspecialchars($t['guide_name']) . ' (' . htmlspecialchars($t['guide_location']) . ')' : '—' ?></td>
                                <td><?= $t['price'] > 0 ? '$' . number_format($t['price'], 2) : '—' ?></td>
                                <td>
                                    <span class="cost-badge <?= $t['status'] === 'completed' ? 'low' : ($t['status'] === 'assigned' ? 'medium' : ($t['status'] === 'canceled' ? 'high' : 'medium')) ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($t['status'] === 'pending'): ?>
                                        <a class="btn-sm btn-delete" href="index.php?page=user&action=cancel_tour&id=<?= $t['id'] ?>"
                                           onclick="return confirm('Cancel this tour request?')">Cancel</a>
                                    <?php elseif ($t['status'] === 'completed'): ?>
                                        <button class="btn-sm btn-edit" onclick="openRateModal(<?= $t['id'] ?>, <?= $t['guide_id'] ?? 'null' ?>, '<?= htmlspecialchars(addslashes($t['title'])) ?>')">Rate Guide</button>
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

<!-- RATE GUIDE MODAL -->
<div id="rate-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:100;">
    <div class="card" style="max-width:380px; width:90%;">
        <h3 class="card-title">Rate Your Guide</h3>
        <p id="rate-modal-title" class="muted"></p>
        <form method="POST" action="index.php?page=user&action=rate_guide" class="form form-aligned">
            <input type="hidden" name="tour_id" id="rate-tour-id">
            <input type="hidden" name="guide_id" id="rate-guide-id">
            <div class="field">
                <label>Rating (1-5)</label>
                <select name="rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Below Average</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Submit Rating</button>
            <button type="button" class="btn btn-ghost btn-full" onclick="closeRateModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openRateModal(tourId, guideId, title) {
    document.getElementById('rate-tour-id').value = tourId;
    document.getElementById('rate-guide-id').value = guideId;
    document.getElementById('rate-modal-title').textContent = title;
    document.getElementById('rate-modal').style.display = 'flex';
}
function closeRateModal() {
    document.getElementById('rate-modal').style.display = 'none';
}
</script>

</body>
</html>
