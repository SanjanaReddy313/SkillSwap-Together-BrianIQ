<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];


/* Mark notifications as read */

$update = $conn->prepare(
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id = ?"
);

$update->bind_param("i", $user_id);
$update->execute();
$update->close();


/* Get notifications */

$stmt = $conn->prepare(
    "SELECT message, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$notifications = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - Notifications</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #1e293b;
        }

        /* Header */

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

        .dashboard-btn {
            color: white;
            text-decoration: none;
            background: #1d4ed8;
            padding: 10px 18px;
            border-radius: 9px;
            font-weight: bold;
        }

        .dashboard-btn:hover {
            background: #1e40af;
        }

        /* Main */

        .container {
            max-width: 800px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            color: #2563eb;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }

        /* Notification Card */

        .notifications-card {
            background: white;
            border-radius: 20px;
            padding: 25px;

            border: 1px solid #e5e7eb;

            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .notification {
            display: flex;
            gap: 15px;

            padding: 18px;

            background: #f8fafc;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            margin-bottom: 15px;
        }

        .notification:last-child {
            margin-bottom: 0;
        }

        .notification-icon {
            width: 45px;
            height: 45px;

            min-width: 45px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;

            border-radius: 50%;

            font-size: 21px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-message {
            color: #334155;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 7px;
        }

        .notification-time {
            color: #64748b;
            font-size: 12px;
        }

        /* Empty */

        .empty {
            text-align: center;
            padding: 50px 25px;
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty h2 {
            color: #334155;
            margin-bottom: 10px;
        }

        .empty p {
            color: #64748b;
        }

        /* Back */

        .back-link {
            display: block;
            width: fit-content;

            margin: 25px auto 0;

            color: #2563eb;

            text-decoration: none;

            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Mobile */

        @media (max-width: 600px) {

            .header {
                padding: 18px;
            }

            .logo {
                font-size: 21px;
            }

            .container {
                margin-top: 30px;
            }

            .page-title {
                font-size: 27px;
            }

            .notifications-card {
                padding: 15px;
            }

            .notification {
                padding: 15px;
            }

        }

    </style>

</head>

<body>


<!-- Header -->

<div class="header">

    <div class="logo">
        🔄 SkillSwap
    </div>

    <a href="dashboard.php" class="dashboard-btn">
        Dashboard
    </a>

</div>


<!-- Main -->

<div class="container">

    <h1 class="page-title">
        🔔 Notifications
    </h1>

    <p class="subtitle">
        Stay updated with your SkillSwap activities.
    </p>


    <div class="notifications-card">


        <?php if ($notifications->num_rows == 0): ?>


            <div class="empty">

                <div class="empty-icon">
                    🔔
                </div>

                <h2>
                    No Notifications
                </h2>

                <p>
                    You don't have any notifications yet.
                </p>

            </div>


        <?php else: ?>


            <?php while ($notification = $notifications->fetch_assoc()): ?>


                <div class="notification">

                    <div class="notification-icon">
                        🔔
                    </div>


                    <div class="notification-content">

                        <div class="notification-message">

                            <?php
                            echo htmlspecialchars(
                                $notification["message"]
                            );
                            ?>

                        </div>


                        <div class="notification-time">

                            🕐
                            <?php
                            echo htmlspecialchars(
                                $notification["created_at"]
                            );
                            ?>

                        </div>

                    </div>

                </div>


            <?php endwhile; ?>


        <?php endif; ?>


    </div>


    <a href="dashboard.php" class="back-link">

        ← Back to Dashboard

    </a>

</div>


</body>

</html>