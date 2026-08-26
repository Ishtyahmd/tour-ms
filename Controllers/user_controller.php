<?php

/**
 * User Controller (Customer Actions)
 * Paradigm: Pure Procedural PHP
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/rating_model.php';

/**
 * Renders the primary user dashboard.
 */
function render_user_dashboard()
{
    $conn = db_connect();
    $user_id = get_logged_in_user_id();

    // Fetch user dashboard data using procedural model functions
    $special_requests = get_user_special_requests($conn, $user_id);
    $user_bookings    = get_user_bookings($conn, $user_id);
    $vendors_list     = get_all_vendors($conn);

    db_close($conn);

    // Render dashboard view
    require_once __DIR__ . '/../views/user/dashboard.php';
}

/**
 * Renders the dedicated form to submit special requests.
 */
function render_special_request_form()
{
    $conn = db_connect();
    $vendors_list = get_all_vendors($conn);
    db_close($conn);

    require_once __DIR__ . '/../views/user/special_request_form.php';
}

/**
 * Processes special request form submissions.
 */
function handle_special_request_submission()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=user_dashboard");
        exit();
    }

    $user_id      = get_logged_in_user_id();
    $vendor_id    = isset($_POST['vendor_id']) ? (int)$_POST['vendor_id'] : 0;
    $request_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
    $details      = isset($_POST['details']) ? trim($_POST['details']) : '';

    if ($vendor_id <= 0 || empty($request_type) || empty($details)) {
        header("Location: index.php?action=submit_special_request&error=empty_fields");
        exit();
    }

    $conn = db_connect();
    $success = create_special_request($conn, $user_id, $vendor_id, $request_type, $details);
    db_close($conn);

    if ($success) {
        header("Location: index.php?action=user_dashboard&status=request_submitted");
    } else {
        header("Location: index.php?action=submit_special_request&error=failed");
    }
    exit();
}

/**
 * Processes standard HTTP POST submission for hotel/guide ratings.
 */
function handle_rating_submission()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?action=user_dashboard");
        exit();
    }

    $user_id     = get_logged_in_user_id();
    $target_type = isset($_POST['target_type']) ? trim($_POST['target_type']) : '';
    $target_id   = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
    $rating      = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review      = isset($_POST['review']) ? trim($_POST['review']) : '';

    if ($target_id <= 0 || $rating < 1 || $rating > 5 || !in_array($target_type, array('hotel', 'guide'))) {
        header("Location: index.php?action=user_dashboard&error=invalid_rating");
        exit();
    }

    $conn = db_connect();
    $success = submit_user_rating($conn, $user_id, $target_type, $target_id, $rating, $review);
    db_close($conn);

    if ($success) {
        header("Location: index.php?action=user_dashboard&status=rating_saved");
    } else {
        header("Location: index.php?action=user_dashboard&error=rating_failed");
    }
    exit();
}
