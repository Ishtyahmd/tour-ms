<?php

function db_connect(){
    $dbHost = "";
    $dbUser = "root";
    $dbPass = "";
    $dbName = "tourms_db";

    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    if(!$conn){
        die("connection failed".mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');
}

