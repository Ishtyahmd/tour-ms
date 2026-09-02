<?php
function guideCtrl($conn) {
    $userId = $_SESSION['user']['id'];
    $guide = getGuideByUserId($conn, $userId);

    if (!$guide) {
        // if no guide exists in the system
        echo "<p style='font-family:sans-serif;padding:2rem;'>No guide profile found for this account. Please contact an administrator.</p>";
        return;
    }

    $guideId = $guide['id'];
    $action = $_GET['action'] ?? 'dashboard';
    $error = '';

    // pending tour claiming logic
    if ($action === 'claim' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $tourId = intval($_POST['tour_id'] ?? 0);
        $result = claimTour($conn, $tourId, $guideId, $guide['daily_rate']);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode($result);
            exit;
        }
        header('Location: index.php?page=guide&msg=' . ($result['success'] ? 'claimed' : 'claim_failed'));
        exit;
    }

    // assigned tour completetion logic
    if ($action === 'complete' && isset($_GET['id'])) {
        $ok = completeTour($conn, intval($_GET['id']), $guideId);
        header('Location: index.php?page=guide&msg=' . ($ok ? 'completed' : 'error'));
        exit;
    }

    // assigned tour cancelletion
    if ($action === 'cancel' && isset($_GET['id'])) {
        $ok = cancelAssignedTour($conn, intval($_GET['id']), $guideId);
        header('Location: index.php?page=guide&msg=' . ($ok ? 'canceled' : 'error'));
        exit;
    }

    // availability toggle
    if ($action === 'toggle_status' && isset($_GET['status'])) {
        $newStatus = $_GET['status'] === 'offline' ? 'offline' : 'available';

        $hasActiveTour = false;
        foreach (getToursByGuide($conn, $guideId) as $t) {
            if ($t['status'] === 'assigned') {
                $hasActiveTour = true;
                break;
            }
        }
        if (!$hasActiveTour) {
            updateGuideStatus($conn, $guideId, $newStatus);
        } else {
            $error = "You can't change availability while you have active assigned tours.";
        }
        header('Location: index.php?page=guide&msg=' . (empty($error) ? 'status_updated' : 'status_blocked'));
        exit;
    }

    // guide profile
    if ($action === 'update_profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $location = trim($_POST['location'] ?? '');
        $rate = floatval($_POST['daily_rate'] ?? 0);

        if ($location === '' || $rate <= 0) {
            $error = 'Please provide a valid location and daily rate.';
        } else {
            updateGuideProfile($conn, $guideId, $location, $rate);
            header('Location: index.php?page=guide&msg=profile_updated');
            exit;
        }
    }

    $guide = getGuideByUserId($conn, $userId);
    $pendingTours = getPendingTours($conn);
    $myTours = getToursByGuide($conn, $guideId);
    $ratingSummary = getGuideRatingSummary($conn, $guideId);

    require 'app/views/guide/dashboard.php';
}
?>
