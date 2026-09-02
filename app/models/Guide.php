<?php
// guide row by their user_id
function getGuideByUserId($conn, $userId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM guides WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// guide row by their guides.id
function getGuideById($conn, $guideId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM guides WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $guideId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// guide status
function updateGuideStatus($conn, $guideId, $status) {
    $stmt = mysqli_prepare($conn, "UPDATE guides SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $guideId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// guide location / daily rate
function updateGuideProfile($conn, $guideId, $location, $dailyRate) {
    $stmt = mysqli_prepare($conn, "UPDATE guides SET location = ?, daily_rate = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sdi', $location, $dailyRate, $guideId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// guide tour claimation
function getPendingTours($conn) {
    $r = mysqli_query($conn, "SELECT tours.*, users.name AS traveler_name, users.phone AS traveler_phone
                               FROM tours
                               JOIN users ON tours.user_id = users.id
                               WHERE tours.status = 'pending'
                               ORDER BY tours.start_date ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getToursByGuide($conn, $guideId) {
    $stmt = mysqli_prepare($conn, "SELECT tours.*, users.name AS traveler_name, users.phone AS traveler_phone
                                    FROM tours
                                    JOIN users ON tours.user_id = users.id
                                    WHERE tours.guide_id = ?
                                    ORDER BY tours.start_date DESC");
    mysqli_stmt_bind_param($stmt, 'i', $guideId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getTourById($conn, $tourId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM tours WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $tourId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

// guide price logic
function claimTour($conn, $tourId, $guideId, $dailyRate) {
    mysqli_begin_transaction($conn);
    try {
        // is the tour still pending execption
        $stmt = mysqli_prepare($conn, "SELECT start_date, end_date, status FROM tours WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, 'i', $tourId);
        mysqli_stmt_execute($stmt);
        $tour = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$tour || $tour['status'] !== 'pending') {
            mysqli_rollback($conn);
            return ['success' => false, 'message' => 'This tour is no longer available.'];
        }

        $start = new DateTime($tour['start_date']);
        $end = new DateTime($tour['end_date']);
        $days = max(1, $start->diff($end)->days + 1); // inclusive of both start and end day
        $price = round($dailyRate * $days, 2);

        $stmt = mysqli_prepare($conn, "UPDATE tours SET guide_id = ?, price = ?, status = 'assigned' WHERE id = ? AND status = 'pending'");
        mysqli_stmt_bind_param($stmt, 'idi', $guideId, $price, $tourId);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected === 0) {
            mysqli_rollback($conn);
            return ['success' => false, 'message' => 'This tour was just claimed by another guide.'];
        }

        updateGuideStatus($conn, $guideId, 'assigned');
        mysqli_commit($conn);
        return ['success' => true, 'price' => $price, 'days' => $days];
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return ['success' => false, 'message' => 'Something went wrong. Please try again.'];
    }
}

//guide claimed tour completion
function completeTour($conn, $tourId, $guideId) {
    $stmt = mysqli_prepare($conn, "UPDATE tours SET status = 'completed' WHERE id = ? AND guide_id = ? AND status = 'assigned'");
    mysqli_stmt_bind_param($stmt, 'ii', $tourId, $guideId);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        freeGuideIfNoActiveTours($conn, $guideId);
    }
    return $affected > 0;
}

// tour cancellation by guide
function cancelAssignedTour($conn, $tourId, $guideId) {
    $stmt = mysqli_prepare($conn, "UPDATE tours SET status = 'pending', guide_id = NULL, price = 0 WHERE id = ? AND guide_id = ? AND status = 'assigned'");
    mysqli_stmt_bind_param($stmt, 'ii', $tourId, $guideId);
    $ok = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        freeGuideIfNoActiveTours($conn, $guideId);
    }
    return $affected > 0;
}

// guide availability helper function
function freeGuideIfNoActiveTours($conn, $guideId) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as c FROM tours WHERE guide_id = ? AND status = 'assigned'");
    mysqli_stmt_bind_param($stmt, 'i', $guideId);
    mysqli_stmt_execute($stmt);
    $count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
    mysqli_stmt_close($stmt);

    if ($count == 0) {
        updateGuideStatus($conn, $guideId, 'available');
    }
}

// guide rating
function getGuideRatingSummary($conn, $guideId) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count, COALESCE(AVG(rating),0) as avg_rating
                                    FROM ratings WHERE target_type = 'guide' AND target_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $guideId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
?>
