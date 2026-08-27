<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard - Tour Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f6f9;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: #fff;
            font-size: 12px;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-approved {
            background-color: #28a745;
        }

        .badge-rejected {
            background-color: #dc3545;
        }
    </style>
</head>

<body>

    <header class="card">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <nav>
            <a href="index.php?action=user_dashboard">Dashboard</a> |
            <a href="index.php?action=submit_special_request">Submit Special Request</a> |
            <a href="index.php?action=logout">Logout</a>
        </nav>
    </header>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'request_submitted'): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 15px;">Your special request was submitted successfully!</div>
    <?php endif; ?>

    <!-- 1. Special Requests Status -->
    <div class="card">
        <h3>My Special Requests</h3>
        <?php if (!empty($special_requests)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($special_requests as $req): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['request_type']); ?></td>
                            <td><?php echo htmlspecialchars($req['details']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($req['status']); ?>">
                                    <?php echo ucfirst($req['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $req['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No special requests submitted yet.</p>
        <?php endif; ?>
    </div>

    <!-- 2. Rate Booked Services / Guides -->
    <div class="card">
        <h3>Submit Rating & Review</h3>
        <form action="index.php?action=rate_service" method="POST">
            <div style="margin-bottom: 10px;">
                <label>Select Service Type:</label><br>
                <select name="target_type" required>
                    <option value="hotel">Hotel</option>
                    <option value="guide">Tour Guide</option>
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <label>Vendor / Guide ID:</label><br>
                <input type="number" name="target_id" placeholder="Enter ID" required>
            </div>

            <div style="margin-bottom: 10px;">
                <label>Rating (1 to 5 Stars):</label><br>
                <select name="rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Terrible</option>
                </select>
            </div>

            <div style="margin-bottom: 10px;">
                <label>Review Comment:</label><br>
                <textarea name="review" rows="3" style="width: 100%;" placeholder="Write your feedback..."></textarea>
            </div>

            <button type="submit">Submit Review</button>
        </form>
    </div>

</body>

</html>
