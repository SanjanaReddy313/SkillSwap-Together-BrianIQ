<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT name, email, location, about, availability, profile_image
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - My Profile</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #222;
        }

        .header {
            background: #2563eb;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
        }

        .back {
            color: white;
            text-decoration: none;
            background: #1d4ed8;
            padding: 10px 18px;
            border-radius: 8px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

       .profile-card {
    background: white;
    padding: 40px;
    border-radius: 22px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
    text-align: center;
    border: 1px solid #e5e7eb;
}

        .profile-image {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #e5edff;
            margin-bottom: 15px;
        }

        .default-icon {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: #e5edff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 55px;
            margin: 0 auto 15px;
        }

        .profile-card h1 {
            color: #2563eb;
            margin-bottom: 8px;
        }

        .email {
            color: #777;
            margin-bottom: 25px;
        }

        .details {
            text-align: left;
            margin-top: 20px;
        }

        .detail-box {
            background: #f8faff;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .detail-box h3 {
            color: #2563eb;
            margin-bottom: 6px;
            font-size: 16px;
        }

        .detail-box p {
            color: #555;
            line-height: 1.5;
        }

        .edit-button {
            display: inline-block;
            margin-top: 25px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
        }

        .edit-button:hover {
            background: #1d4ed8;
        }

        @media (max-width: 600px) {

            .header {
                padding: 18px;
            }

            .container {
                margin-top: 25px;
            }

            .profile-card {
                padding: 25px 18px;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <div class="logo">
        🔄 SkillSwap
    </div>

    <a href="dashboard.php" class="back">
        Dashboard
    </a>

</div>


<div class="container">

    <div class="profile-card">


        <?php if (!empty($user["profile_image"])): ?>

            <img
               src="<?php echo htmlspecialchars($user["profile_image"]); ?>"
                class="profile-image"
                alt="Profile Photo"
            >

        <?php else: ?>

            <div class="default-icon">
                👤
            </div>

        <?php endif; ?>


        <h1>
            <?php echo htmlspecialchars($user["name"]); ?>
        </h1>

        <p class="email">
            <?php echo htmlspecialchars($user["email"]); ?>
        </p>


        <div class="details">


            <div class="detail-box">

                <h3>📍 Location</h3>

                <p>
                    <?php
                    echo !empty($user["location"])
                        ? htmlspecialchars($user["location"])
                        : "Not added";
                    ?>
                </p>

            </div>


            <div class="detail-box">

                <h3>👋 About Me</h3>

                <p>
                    <?php
                    echo !empty($user["about"])
                        ? nl2br(htmlspecialchars($user["about"]))
                        : "No information added yet.";
                    ?>
                </p>

            </div>


            <div class="detail-box">

                <h3>🕐 Availability</h3>

                <p>
                    <?php
                    echo !empty($user["availability"])
                        ? htmlspecialchars($user["availability"])
                        : "Not added";
                    ?>
                </p>

            </div>


        </div>


        <a href="edit_profile.php" class="edit-button">
            ✏️ Edit Profile
        </a>


    </div>

</div>

</body>

</html>