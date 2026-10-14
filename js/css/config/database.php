<?php

$host = "127.0.0.1";
$port = "3307";
$username = "root";
$password = "";
$database = "skillswap";

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>