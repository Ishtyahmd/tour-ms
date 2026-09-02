<?php
$user = $_SESSION['user'];

$statusMessages = [
    'tour_requested' => ['success', 'Your tour request has been submitted. A guide will claim it soon.'],
    'tour_canceled' => ['success', 'Tour request canceled.'],
    'rated' => ['success', 'Thanks for your rating!'],
    'already_rated' => ['error', "You've already rated this, or it isn't eligible yet."],
    'error' => ['error', 'Something went wrong.']
];
$flash = isset($_GET['msg']) ? ($statusMessages[$_GET['msg']] ?? null) : null;
$activeCategory = $_GET['category'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">Explore</h1>
                <p class="page-sub">Request a guided tour, or browse hotels and vehicles to book.</p>
            </div>
            <div>
                <a href="index.php?page=user&action=my_tours" class="btn btn-ghost">My Tours</a>
                <a href="index.php?page=user&action=bookings" class="btn btn-ghost">My Bookings</a>
                <a href="index.php?page=user&action=requests" class="btn btn-ghost">Special Requests</a>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash[0] ?>"><?= $flash[1] ?></div>
    <?php endif; ?>

    <!-- REQUEST A TOUR -->
    <div class="card card-margin-bottom">
        <h3 class="card-title">Request a Guided Tour</h3>
        <p class="muted">Tell us where and when — available guides will be able to claim your request.</p>
        <form method="POST" action="index.php?page=user&action=request_tour" class="form form-aligned">
            <div class="field-row">
                <div class="field">
                    <label>Tour Title</label>
                    <input type="text" name="title" required placeholder="e.g. Sundarbans 3-Day Trip">
                </div>
                <div class="field">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="field">
                    <label>End Date</label>
                    <input type="date" name="end_date" required min="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>

    <!-- BROWSE LISTINGS -->
    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Available Listings</h3>
            <div>
                <a href="index.php?page=user" class="btn-sm <?= $activeCategory === '' ? 'btn-edit' : 'btn-ghost' ?>">All</a>
                <a href="index.php?page=user&category=hotels" class="btn-sm <?= $activeCategory === 'hotels' ? 'btn-edit' : 'btn-ghost' ?>">Hotels</a>
                <a href="index.php?page=user&category=vehicle" class="btn-sm <?= $activeCategory === 'vehicle' ? 'btn-edit' : 'btn-ghost' ?>">Vehicles</a>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listings)): ?>
                        <tr><td colspan="5" class="empty">No available listings right now.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listings as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['title']) ?></td>
                                <td><?= $l['category'] === 'hotels' ? 'Hotel' : 'Vehicle' ?></td>
                                <td><?= htmlspecialchars($l['company_name']) ?></td>
                                <td>$<?= number_format($l['price'], 2) ?></td>
                                <td class="text-right">
                                    <button class="btn-sm btn-edit" onclick="openBookModal(<?= $l['id'] ?>, '<?= htmlspecialchars(addslashes($l['title'])) ?>', <?= $l['price'] ?>)">Book</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- BOOKING MODAL (simple inline form, no framework) -->
<div id="book-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:100;">
    <div class="card" style="max-width:420px; width:90%;">
        <h3 class="card-title">Confirm Booking</h3>
        <p id="book-modal-title" class="muted"></p>
        <form method="POST" action="index.php?page=user&action=book_listing" class="form form-aligned">
            <input type="hidden" name="listing_id" id="book-listing-id">
            <div class="field">
                <label>Discount Code (optional)</label>
                <input type="text" name="discount_code" placeholder="e.g. SUMMER25">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Confirm Booking</button>
            <button type="button" class="btn btn-ghost btn-full" onclick="closeBookModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openBookModal(id, title, price) {
    document.getElementById('book-listing-id').value = id;
    document.getElementById('book-modal-title').textContent = title + ' — $' + price.toFixed(2);
    document.getElementById('book-modal').style.display = 'flex';
}
function closeBookModal() {
    document.getElementById('book-modal').style.display = 'none';
}
</script>

</body>
</html>
