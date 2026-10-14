<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "error" => "Not logged in"
    ]);
    exit();
}

include "config/database.php";

$user_id = intval($_SESSION["user_id"]);


/* =========================
   SEND SIGNAL
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $booking_id = isset($_POST["booking_id"])
        ? intval($_POST["booking_id"])
        : 0;

    $receiver_id = isset($_POST["receiver_id"])
        ? intval($_POST["receiver_id"])
        : 0;

    $signal_type = isset($_POST["signal_type"])
        ? $_POST["signal_type"]
        : "";

    $signal_data = isset($_POST["signal_data"])
        ? $_POST["signal_data"]
        : "";


    if (
        $booking_id <= 0 ||
        $receiver_id <= 0 ||
        empty($signal_data)
    ) {

        echo json_encode([
            "success" => false,
            "error" => "Invalid signal data"
        ]);

        exit();
    }


    if (!in_array($signal_type, [
        "offer",
        "answer",
        "candidate"
    ])) {

        echo json_encode([
            "success" => false,
            "error" => "Invalid signal type"
        ]);

        exit();
    }


    $stmt = $conn->prepare(
        "INSERT INTO webrtc_signals
        (
            booking_id,
            sender_id,
            receiver_id,
            signal_type,
            signal_data
        )
        VALUES (?, ?, ?, ?, ?)"
    );


    if (!$stmt) {

        echo json_encode([
            "success" => false,
            "error" => "Database prepare failed"
        ]);

        exit();
    }


    $stmt->bind_param(
        "iiiss",
        $booking_id,
        $user_id,
        $receiver_id,
        $signal_type,
        $signal_data
    );


    if ($stmt->execute()) {

        echo json_encode([
            "success" => true
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "error" => "Database insert failed"
        ]);

    }


    $stmt->close();

    exit();
}


/* =========================
   GET SIGNALS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $booking_id = isset($_GET["booking_id"])
        ? intval($_GET["booking_id"])
        : 0;

    $last_id = isset($_GET["last_id"])
        ? intval($_GET["last_id"])
        : 0;


    if ($booking_id <= 0) {

        echo json_encode([
            "success" => false,
            "error" => "Invalid booking ID"
        ]);

        exit();
    }


    $stmt = $conn->prepare(
        "SELECT
            id,
            sender_id,
            signal_type,
            signal_data
         FROM webrtc_signals
         WHERE booking_id = ?
         AND receiver_id = ?
         AND id > ?
         ORDER BY id ASC"
    );


    if (!$stmt) {

        echo json_encode([
            "success" => false,
            "error" => "Database prepare failed"
        ]);

        exit();
    }


    $stmt->bind_param(
        "iii",
        $booking_id,
        $user_id,
        $last_id
    );


    $stmt->execute();


    $result = $stmt->get_result();


    $signals = [];


    while ($row = $result->fetch_assoc()) {

        $signals[] = $row;

    }


    $stmt->close();


    echo json_encode([
        "success" => true,
        "signals" => $signals
    ]);

    exit();
}


/* =========================
   INVALID REQUEST
========================= */

echo json_encode([
    "success" => false,
    "error" => "Invalid request"
]);

?>