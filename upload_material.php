<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = isset($_POST["booking_id"])
    ? intval($_POST["booking_id"])
    : 0;

if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

    if (!isset($_FILES["material"])) {
        die("No file selected.");
    }

    $file = $_FILES["material"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        die("File upload failed.");
    }

    $allowed_extensions = [
        "pdf",
        "doc",
        "docx",
        "ppt",
        "pptx",
        "jpg",
        "jpeg",
        "png"
    ];

    $file_name = $file["name"];
    $file_tmp = $file["tmp_name"];
    $file_size = $file["size"];

    $extension = strtolower(
        pathinfo($file_name, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $allowed_extensions)) {
        die("This file type is not allowed.");
    }

    /* Maximum size: 10 MB */
    if ($file_size > 10 * 1024 * 1024) {
        die("File size must be less than 10 MB.");
    }

    /* Create unique file name */
    $new_file_name =
        "material_" .
        $user_id .
        "_" .
        time() .
        "." .
        $extension;

    $upload_path = "materials/" . $new_file_name;

    if (move_uploaded_file($file_tmp, $upload_path)) {
        $save = $conn->prepare(
    "INSERT INTO session_materials
    (booking_id, uploaded_by, file_name, file_path)
    VALUES (?, ?, ?, ?)"
);

$save->bind_param(
    "iiss",
    $booking_id,
    $user_id,
    $file_name,
    $upload_path
);

$save->execute();
$save->close();

        echo "<h2>✅ Material uploaded successfully!</h2>";

        echo "<p>File: " .
             htmlspecialchars($file_name) .
             "</p>";

        echo '<p><a href="session.php">← Back to Session</a></p>';

    } else {

        echo "❌ Unable to upload the file.";

    }
}

?>