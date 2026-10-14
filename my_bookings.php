<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$sql = "
    SELECT
        b.id,
        b.user_id,
        b.other_user_id,
        b.session_date,
        b.session_time,
        b.duration,
        b.message,
        b.status,
        CASE
            WHEN b.user_id = ? THEN u2.name
            ELSE u1.name
        END AS other_user_name
    FROM bookings b
    JOIN users u1 ON b.user_id = u1.id
    JOIN users u2 ON b.other_user_id = u2.id
    WHERE b.user_id = ? OR b.other_user_id = ?
    ORDER BY b.session_date DESC, b.session_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | SkillSwap</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 1000px;
            margin: 25px auto;
        }

        h1 {
            text-align: center;
            color: #2563eb;
        }

        .notice {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
        }

        .booking {
            background: white;
            margin-bottom: 20px;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,.06);
        }

        .booking h2 {
            margin-top: 0;
            color: #2563eb;
        }

        .detail {
            margin: 10px 0;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            background: #dbeafe;
            color: #1d4ed8;
            border-radius: 20px;
            font-size: 13px;
        }

        .button {
            display: inline-block;
            margin: 12px 8px 0 0;
            padding: 11px 16px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .empty {
            background: white;
            text-align: center;
            padding: 30px;
            border-radius: 12px;
        }

        .back {
            display: inline-block;
            margin-top: 10px;
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>My Bookings</h1>

    <?php if (isset($_GET["booked"])): ?>
        <div class="notice">
            Your booking was created successfully!
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>

        <div class="empty">
            <h2>No bookings yet</h2>
            <p>Your booked sessions will appear here.</p>
            <a class="button" href="booking.php">Book a Session</a>
        </div>

    <?php else: ?>

        <?php while ($booking = $result->fetch_assoc()): ?>

            <div class="booking">
                <h2>
                    Session with
                    <?php echo htmlspecialchars($booking["other_user_name"]); ?>
                </h2>

                <p class="detail">
                    <strong>Date:</strong>
                    <?php echo htmlspecialchars($booking["session_date"]); ?>
                </p>

                <p class="detail">
                    <strong>Time:</strong>
                    <?php echo htmlspecialchars($booking["session_time"]); ?>
                </p>

                <p class="detail">
                    <strong>Duration:</strong>
                    <?php echo (int) $booking["duration"]; ?> minutes
                </p>

                <p class="detail">
                    <strong>Message:</strong>
                    <?php
                    echo htmlspecialchars(
                        $booking["message"] ?? ""
                    ) ?: "No message";
                    ?>
                </p>

                <p>
                    <span class="status">
                        <?php echo htmlspecialchars($booking["status"]); ?>
                    </span>
                </p>

                <?php if (strtolower($booking["status"]) !== "completed"): ?>
                    <a
                        class="button"
                        href="session.php?booking_id=<?php echo (int) $booking["id"]; ?>"
                    >
                        Start Session
                    </a>
                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php endif; ?>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>

</div>
</body>
</html>