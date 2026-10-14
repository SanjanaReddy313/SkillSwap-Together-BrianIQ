<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];
$message = "";


/* Get current user details */

$stmt = $conn->prepare(
    "SELECT name, location, email, about, availability, profile_image
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


/* Update profile */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $about = trim($_POST["about"]);
    $availability = trim($_POST["availability"]);


    /* Update profile details */

    $update = $conn->prepare(
        "UPDATE users
         SET name = ?, location = ?, about = ?, availability = ?
         WHERE id = ?"
    );

    $update->bind_param(
        "ssssi",
        $name,
        $location,
        $about,
        $availability,
        $user_id
    );


    if ($update->execute()) {

        $_SESSION["user_name"] = $name;

        $message = "Profile updated successfully!";

        $user["name"] = $name;
        $user["location"] = $location;
        $user["about"] = $about;
        $user["availability"] = $availability;

    } else {

        $message = "Something went wrong while updating your profile.";

    }

    $update->close();


    /* Profile photo upload */

    if (
        isset($_FILES["profile_image"]) &&
        $_FILES["profile_image"]["error"] == 0
    ) {

        $file_name = $_FILES["profile_image"]["name"];
        $file_tmp = $_FILES["profile_image"]["tmp_name"];

        $extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        $allowed = [
            "jpg",
            "jpeg",
            "png",
            "gif",
            "webp"
        ];


        if (in_array($extension, $allowed)) {

            /* Create uploads folder if needed */

            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }


            $new_file_name =
                "profile_" . $user_id . "." . $extension;

            $upload_path =
                "uploads/" . $new_file_name;


            /* Move uploaded photo */

            if (move_uploaded_file(
                $file_tmp,
                $upload_path
            )) {

                /* Save photo path in database */

                $photo_stmt = $conn->prepare(
                    "UPDATE users
                     SET profile_image = ?
                     WHERE id = ?"
                );

                $photo_stmt->bind_param(
                    "si",
                    $upload_path,
                    $user_id
                );

                if ($photo_stmt->execute()) {

                    $user["profile_image"] = $upload_path;

                    $message =
                        "Profile and photo updated successfully!";

                } else {

                    $message =
                        "Profile updated, but photo information could not be saved.";

                }

                $photo_stmt->close();

            } else {

                $message =
                    "Profile updated, but the photo could not be uploaded.";

            }

        } else {

            $message =
                "Invalid image format. Use JPG, JPEG, PNG, GIF or WEBP.";

        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>SkillSwap - Edit Profile</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }


        body {
            background: #f4f7fb;
            color: #1f2937;
            min-height: 100vh;
        }


        .header {
            background: #2563eb;
            color: white;
            text-align: center;
            padding: 25px 20px;
        }


        .header h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }


        .header p {
            font-size: 15px;
            opacity: 0.9;
        }


        .container {
            width: 90%;
            max-width: 700px;
            margin: 35px auto;
        }


        .message {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }


        .profile-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.10);
            border: 1px solid #e5e7eb;
        }


        .profile-card h2 {
            text-align: center;
            color: #2563eb;
            margin-bottom: 25px;
        }


        .form-group {
            margin-bottom: 20px;
        }


        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #374151;
        }


        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }


        input:focus,
        textarea:focus,
        select:focus {
            border-color: #2563eb;
        }


        input:disabled {
            background: #f3f4f6;
            color: #6b7280;
        }


        textarea {
            resize: vertical;
            min-height: 120px;
        }


        .photo-box {
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }


        .save-button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }


        .save-button:hover {
            background: #1d4ed8;
        }


        .back-button {
            display: block;
            width: 220px;
            margin: 25px auto 0;
            padding: 13px;
            text-align: center;
            text-decoration: none;
            background: #111827;
            color: white;
            border-radius: 10px;
            font-weight: bold;
        }


        .back-button:hover {
            background: #374151;
        }


        @media (max-width: 600px) {

            .container {
                width: 94%;
            }

            .profile-card {
                padding: 22px;
            }

            .header h1 {
                font-size: 25px;
            }

        }

    </style>

</head>


<body>


<div class="header">

    <h1>✏️ Edit Profile</h1>

    <p>Update your SkillSwap profile information</p>

</div>


<div class="container">


    <?php if ($message != ""): ?>

        <div class="message">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="profile-card">

        <h2>Profile Information</h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="form-group">

                <label>Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($user["name"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Location</label>

                <input
                    type="text"
                    name="location"
                    value="<?php echo htmlspecialchars($user["location"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Email</label>

                <input
                    type="email"
                    value="<?php echo htmlspecialchars($user["email"] ?? ""); ?>"
                    disabled
                >

            </div>


            <div class="form-group photo-box">

                <label>📷 Profile Photo</label>

                <input
                    type="file"
                    name="profile_image"
                    accept="image/*"
                >

            </div>


            <div class="form-group">

                <label>About Me</label>

                <textarea
                    name="about"
                    placeholder="Tell others about yourself..."
                ><?php echo htmlspecialchars($user["about"] ?? ""); ?></textarea>

            </div>


            <div class="form-group">

                <label>Availability</label>

                <select name="availability">

                    <option value="">
                        Select Availability
                    </option>

                    <option value="Weekdays"
                    <?php
                    if (($user["availability"] ?? "") == "Weekdays")
                        echo "selected";
                    ?>>
                        Weekdays
                    </option>


                    <option value="Weekends"
                    <?php
                    if (($user["availability"] ?? "") == "Weekends")
                        echo "selected";
                    ?>>
                        Weekends
                    </option>


                    <option value="Morning"
                    <?php
                    if (($user["availability"] ?? "") == "Morning")
                        echo "selected";
                    ?>>
                        Morning
                    </option>


                    <option value="Afternoon"
                    <?php
                    if (($user["availability"] ?? "") == "Afternoon")
                        echo "selected";
                    ?>>
                        Afternoon
                    </option>


                    <option value="Evening"
                    <?php
                    if (($user["availability"] ?? "") == "Evening")
                        echo "selected";
                    ?>>
                        Evening
                    </option>


                    <option value="All Time"
                    <?php
                    if (($user["availability"] ?? "") == "All Time")
                        echo "selected";
                    ?>>
                        All Time
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="save-button"
            >
                💾 Save Changes
            </button>


        </form>


        <a
            href="profile.php"
            class="back-button"
        >
            ← Back to Profile
        </a>

    </div>

</div>


</body>

</html>