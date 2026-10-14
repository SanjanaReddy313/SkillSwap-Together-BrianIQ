<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$sender_id = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $receiver_id = $_POST["receiver_id"];
    $request_message = trim($_POST["message"]);

    if ($receiver_id == $sender_id) {

        $message = "You cannot send a request to yourself.";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM connections
             WHERE sender_id = ? AND receiver_id = ?"
        );

        $check->bind_param("ii", $sender_id, $receiver_id);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Connection request already sent.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO connections
                 (sender_id, receiver_id, message)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "iis",
                $sender_id,
                $receiver_id,
                $request_message
            );

            if ($stmt->execute()) {

                $message = "Connection request sent successfully!";

                /* Notify the receiver */

                $notification_text =
                    "You received a new connection request from " .
                    $_SESSION["user_name"];

                $notification = $conn->prepare(
                    "INSERT INTO notifications
                    (user_id, message)
                    VALUES (?, ?)"
                );

                $notification->bind_param(
                    "is",
                    $receiver_id,
                    $notification_text
                );

                $notification->execute();
                $notification->close();

            } else {

                $message = "Something went wrong.";

            }

            $stmt->close();
        }

        $check->close();
    }
}


/* Get other users */

$stmt = $conn->prepare(
    "SELECT id, name, location
     FROM users
     WHERE id != ?
     ORDER BY name"
);

$stmt->bind_param("i", $sender_id);
$stmt->execute();

$users = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - Connect</title>


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
            max-width: 650px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 22px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .title {
            text-align: center;
            color: #2563eb;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .message {
            background: #eff6ff;
            color: #2563eb;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        select,
        textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            background: white;
        }

        select:focus,
        textarea:focus {
            border-color: #2563eb;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        button {
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

        button:hover {
            background: #1d4ed8;
        }

        .dashboard-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .dashboard-link:hover {
            text-decoration: underline;
        }

        .empty {
            text-align: center;
            padding: 25px;
            background: #f8fafc;
            border-radius: 12px;
            color: #777;
        }

        @media (max-width: 600px) {

            .header {
                padding: 18px;
            }

            .logo {
                font-size: 21px;
            }

            .container {
                margin-top: 25px;
            }

            .card {
                padding: 25px;
            }

            .title {
                font-size: 26px;
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

    <div class="card">


        <h1 class="title">
            🤝 Connect
        </h1>


        <p class="subtitle">
            Connect with people and grow your SkillSwap network.
        </p>


        <div class="info-box">

            💡 Select a SkillSwap user and send them a
            connection request. You can include a short message
            to introduce yourself.

        </div>


        <?php if ($message != ""): ?>

            <div class="message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <?php if ($users->num_rows == 0): ?>


            <div class="empty">

                <h3>
                    No other users are available yet.
                </h3>

                <p style="margin-top: 8px;">
                    Other SkillSwap users will appear here
                    when they join.
                </p>

            </div>


        <?php else: ?>


            <form method="POST">


                <div class="form-group">

                    <label>
                        👤 Select User
                    </label>


                    <select name="receiver_id" required>

                        <option value="">
                            -- Select User --
                        </option>


                        <?php while ($user = $users->fetch_assoc()): ?>

                            <option value="<?php echo $user["id"]; ?>">

                                <?php
                                echo htmlspecialchars(
                                    $user["name"]
                                );
                                ?>

                                -

                                <?php
                                echo htmlspecialchars(
                                    $user["location"] ?? "Location not provided"
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        💬 Message
                    </label>


                    <textarea
                        name="message"
                        placeholder="Write a message to introduce yourself..."
                    ></textarea>

                </div>


                <button type="submit">
                    🤝 Send Connection Request
                </button>


            </form>


        <?php endif; ?>


        <a href="dashboard.php"
           class="dashboard-link">

            ← Back to Dashboard

        </a>


    </div>

</div>


</body>

</html>