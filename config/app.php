<?php
define('ROOT_URL', 'http://localhost/tourms/TourMS/');
define('APP_NAME', 'TourMS');

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($location) {
    header("Location: " . ROOT_URL . $location);
    exit;
}

?>
