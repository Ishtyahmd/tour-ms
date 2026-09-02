<?php
$user = $_SESSION['user'];

$statusMessages = [
    'booked' => ['success', 'Booking confirmed!'],
    'book_failed' => ['error', 'That listing could not be booked — it may already be taken, or your discount code was invalid.'],
    'booking_canceled' => ['success', 'Booking canceled.'],
    'rated' => ['success', 'Thanks for your rating!'],
    'already_rated' => ['error', "You've already rated this listing for this booking."],
    'error' => ['error', 'Something went wrong.']
];
$flash = isset($_GET['msg']) ? ($statusMessages[$_GET['msg']] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">My Bookings</h1>
                <p class="page-sub">Hotel and vehicle bookings you've made.</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">&larr; Back to Explore</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash[0] ?>"><?= $flash[1] ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">All Bookings</h3>
            <span class="badge"><?= count($bookings) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="6" class="empty">You haven't made any bookings yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['title']) ?></td>
                                <td><?= htmlspecialchars($b['company_name']) ?></td>
                                <td>$<?= number_format($b['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($b['booking_date']) ?></td>
                                <td>
                                    <span class="cost-badge <?= $b['status'] === 'confirmed' ? 'low' : 'high' ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($b['status'] === 'confirmed'): ?>
                                        <button class="btn-sm btn-edit" onclick="openRateModal(<?= $b['id'] ?>, <?= $b['listing_id'] ?>, '<?= htmlspecialchars(addslashes($b['title'])) ?>')">Rate</button>
                                        <a class="btn-sm btn-delete" href="index.php?page=user&action=cancel_booking&id=<?= $b['id'] ?>"
                                           onclick="return confirm('Cancel this booking?')">Cancel</a>
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

<!-- RATE LISTING MODAL -->
<div id="rate-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:100;">
    <div class="card" style="max-width:380px; width:90%;">
        <h3 class="card-title">Rate This Listing</h3>
        <p id="rate-modal-title" class="muted"></p>
        <form method="POST" action="index.php?page=user&action=rate_listing" class="form form-aligned">
            <input type="hidden" name="booking_id" id="rate-booking-id">
            <input type="hidden" name="listing_id" id="rate-listing-id">
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
function openRateModal(bookingId, listingId, title) {
    document.getElementById('rate-booking-id').value = bookingId;
    document.getElementById('rate-listing-id').value = listingId;
    document.getElementById('rate-modal-title').textContent = title;
    document.getElementById('rate-modal').style.display = 'flex';
}
function closeRateModal() {
    document.getElementById('rate-modal').style.display = 'none';
}
</script>

</body>
</html>
