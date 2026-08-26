<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submit Special Request - Tour Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f6f9;
        }

        .form-card {
            background: #fff;
            padding: 25px;
            max-width: 500px;
            border-radius: 6px;
            margin: 0 auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <div class="form-card">
        <h2>Request Special Service</h2>
        <p><a href="index.php?action=user_dashboard">&larr; Back to Dashboard</a></p>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'empty_fields'): ?>
            <div style="color: red; margin-bottom: 10px;">Please complete all required fields.</div>
        <?php endif; ?>

        <form action="index.php?action=submit_special_request" method="POST">

            <div class="form-group">
                <label for="vendor_id">Select Vendor / Provider:</label>
                <select name="vendor_id" id="vendor_id" required>
                    <option value="">-- Choose Hotel or Transport Vendor --</option>
                    <?php if (!empty($vendors_list)): ?>
                        <?php foreach ($vendors_list as $vendor): ?>
                            <option value="<?php echo $vendor['id']; ?>">
                                <?php echo htmlspecialchars($vendor['company_name']) . " (" . ucfirst($vendor['type']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="request_type">Request Type:</label>
                <input type="text" id="request_type" name="request_type" placeholder="e.g., Airport Transfer, Wheelchair, Late Check-in" required>
            </div>

            <div class="form-group">
                <label for="details">Additional Details:</label>
                <textarea id="details" name="details" rows="5" placeholder="Specify your requirements in detail..." required></textarea>
            </div>

            <button type="submit">Submit Request</button>
        </form>
    </div>

</body>

</html>
