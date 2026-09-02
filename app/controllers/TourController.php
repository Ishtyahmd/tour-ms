<?php
function userCtrl($conn) {
    $userId = $_SESSION['user']['id'];
    $action = $_GET['action'] ?? 'explore';
    $error = '';

    // tour request creation
    if ($action === 'request_tour' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';

        if ($title === '' || $start === '' || $end === '') {
            $error = 'Please fill in all tour details.';
        } elseif (strtotime($end) < strtotime($start)) {
            $error = 'End date must be on or after the start date.';
        } elseif (strtotime($start) < strtotime(date('Y-m-d'))) {
            $error = 'Start date cannot be in the past.';
        } else {
            createTourRequest($conn, $userId, $title, $start, $end);
            header('Location: index.php?page=user&msg=tour_requested');
            exit;
        }
    }

    // pending tour request cancellation
    if ($action === 'cancel_tour' && isset($_GET['id'])) {
        $ok = cancelOwnTour($conn, intval($_GET['id']), $userId);
        header('Location: index.php?page=user&msg=' . ($ok ? 'tour_canceled' : 'error'));
        exit;
    }

    //guide rating after tour completion
    if ($action === 'rate_guide' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $tourId = intval($_POST['tour_id'] ?? 0);
        $guideId = intval($_POST['guide_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);

        if ($rating < 1 || $rating > 5) {
            $error = 'Please choose a rating between 1 and 5.';
        } else {
            $ok = rateGuide($conn, $userId, $tourId, $guideId, $rating);
            header('Location: index.php?page=user&msg=' . ($ok ? 'rated' : 'already_rated'));
            exit;
        }
    }

    // booking
    if ($action === 'book_listing' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $listingId = intval($_POST['listing_id'] ?? 0);
        $discountCode = trim($_POST['discount_code'] ?? '');
        $result = bookListing($conn, $userId, $listingId, $discountCode ?: null);

        header('Location: index.php?page=user&action=bookings&msg=' . ($result['success'] ? 'booked' : 'book_failed'));
        exit;
    }

    // confirmed booking cancellation
    if ($action === 'cancel_booking' && isset($_GET['id'])) {
        $ok = cancelBooking($conn, intval($_GET['id']), $userId);
        header('Location: index.php?page=user&action=bookings&msg=' . ($ok ? 'booking_canceled' : 'error'));
        exit;
    }

    // confirmed booking rating
    if ($action === 'rate_listing' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $listingId = intval($_POST['listing_id'] ?? 0);
        $rating = intval($_POST['rating'] ?? 0);

        if ($rating < 1 || $rating > 5) {
            $error = 'Please choose a rating between 1 and 5.';
        } else {
            $ok = rateListing($conn, $userId, $bookingId, $listingId, $rating);
            header('Location: index.php?page=user&action=bookings&msg=' . ($ok ? 'rated' : 'already_rated'));
            exit;
        }
    }

    // special request to vendors (hotel/transportation)
    if ($action === 'special_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $vendorId = intval($_POST['vendor_id'] ?? 0);
        $type = trim($_POST['request_type'] ?? '');
        $details = trim($_POST['details'] ?? '');

        if ($vendorId <= 0 || $type === '' || $details === '') {
            $error = 'Please complete all fields of the request.';
        } else {
            createSpecialRequest($conn, $userId, $vendorId, $type, $details);
            header('Location: index.php?page=user&action=requests&msg=request_sent');
            exit;
        }
    }

    //sub-pages
    if ($action === 'my_tours') {
        $tours = getToursByUser($conn, $userId);
        require 'app/views/user/my_tours.php';
        return;
    }

    if ($action === 'bookings') {
        $bookings = getBookingsByUser($conn, $userId);
        require 'app/views/user/bookings.php';
        return;
    }

    if ($action === 'requests') {
        $vendors = getVendorsForRequestForm($conn);
        require 'app/views/user/special_request.php';
        return;
    }

    //default page
    $categoryFilter = $_GET['category'] ?? null;
    $listings = getAvailableListings($conn, $categoryFilter);
    require 'app/views/user/explore.php';
}
?>
