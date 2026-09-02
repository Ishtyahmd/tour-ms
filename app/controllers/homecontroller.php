<?php

function homeCtrl($conn) {
    $user = $_SESSION['user'] ?? null;
    require 'app/views/home/index.php';
}
?>
