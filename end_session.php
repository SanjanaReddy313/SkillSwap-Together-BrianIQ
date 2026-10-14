<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = intval($_SESSION["user_id"]);

$booking_id = isset($_GET["booking_id"])
    ? intval($_GET["booking_id"])
    : 0;

if ($booking_id <= 0) {
    die("Invalid booking.");
}

/* Check booking access */
$check = $conn->prepare(
    "SELECT id, user_id, other_user_id
     FROM bookings
     WHERE id = ?
       AND (user_id = ? OR other_user_id = ?)"
);

if (!$check) {
    die("Database error: " . $conn->error);
}

$check->bind_param("iii", $booking_id, $user_id, $user_id);
$check->execute();

$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    die("Booking not found or access denied.");
}

$check->close();

/* Mark session as completed */
$update = $conn->prepare(
    "UPDATE bookings
     SET status = 'completed'
     WHERE id = ?"
);

if (!$update) {
    die("Could not prepare update: " . $conn->error);
}

$update->bind_param("i", $booking_id);

if (!$update->execute()) {
    die("Could not complete session: " . $update->error);
}

$update->close();

/* Open the feedback page */
header("Location: ratings.php?booking_id=" . $booking_id);
exit();
?>