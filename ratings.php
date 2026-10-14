<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = (int) $_SESSION["user_id"];
$message = "";

$booking_id = isset($_GET["booking_id"])
    ? (int) $_GET["booking_id"]
    : (isset($_POST["booking_id"]) ? (int) $_POST["booking_id"] : 0);

/* Submit feedback */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $booking_id = (int) ($_POST["booking_id"] ?? 0);
    $rating = (int) ($_POST["rating"] ?? 0);
    $feedback = trim($_POST["feedback"] ?? "");

    /*
       Verify that this is a completed session
       and the logged-in user is a participant.
    */
    $checkBooking = $conn->prepare(
        "SELECT id, user_id, other_user_id
         FROM bookings
         WHERE id = ?
           AND status = 'completed'
           AND (user_id = ? OR other_user_id = ?)"
    );

    $checkBooking->bind_param(
        "iii",
        $booking_id,
        $user_id,
        $user_id
    );

    $checkBooking->execute();
    $bookingResult = $checkBooking->get_result();
    $booking = $bookingResult->fetch_assoc();
    $checkBooking->close();

    if (!$booking) {
        $message = "Completed session not found, or you are not part of this session.";
    } elseif ($rating < 1 || $rating > 5) {
        $message = "Please select a rating from 1 to 5.";
    } else {

        /* Find the other participant */
        $reviewed_user_id = (
            (int) $booking["user_id"] === $user_id
        )
            ? (int) $booking["other_user_id"]
            : (int) $booking["user_id"];

        /* Prevent duplicate ratings */
        $check = $conn->prepare(
            "SELECT id
             FROM ratings
             WHERE booking_id = ? AND reviewer_id = ?"
        );

        $check->bind_param("ii", $booking_id, $user_id);
        $check->execute();
        $alreadyRated = $check->get_result()->num_rows > 0;
        $check->close();

        if ($alreadyRated) {
            $message = "You have already rated this session.";
        } else {

            $stmt = $conn->prepare(
                "INSERT INTO ratings
                 (booking_id, reviewer_id, reviewed_user_id, rating, feedback)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiiis",
                $booking_id,
                $user_id,
                $reviewed_user_id,
                $rating,
                $feedback
            );

            if ($stmt->execute()) {
                $message = "Rating submitted successfully!";
            } else {
                $message = "Could not save feedback. Please try again.";
            }

            $stmt->close();
        }
    }
}

/* Get only the selected completed session */
$stmt = $conn->prepare(
    "SELECT
        b.id,
        b.session_date,
        b.session_time,
        b.duration,
        CASE
            WHEN b.user_id = ? THEN b.other_user_id
            ELSE b.user_id
        END AS other_user_id,
        u.name,
        r.id AS rating_id
     FROM bookings b
     INNER JOIN users u
        ON u.id = CASE
            WHEN b.user_id = ? THEN b.other_user_id
            ELSE b.user_id
        END
     LEFT JOIN ratings r
        ON r.booking_id = b.id
       AND r.reviewer_id = ?
     WHERE b.id = ?
       AND (b.user_id = ? OR b.other_user_id = ?)
       AND b.status = 'completed'"
);

$stmt->bind_param(
    "iiiiii",
    $user_id,
    $user_id,
    $user_id,
    $booking_id,
    $user_id,
    $user_id
);

$stmt->execute();
$bookings = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillSwap - Ratings & Feedback</title>

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
            padding: 25px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 35px auto;
        }

        .message {
            background: #dcfce7;
            color: #166534;
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
        }

        .rating-card, .empty {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        .rating-card h2 {
            color: #2563eb;
            margin-bottom: 18px;
            font-size: 22px;
        }

        .session-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .session-info p {
            margin: 8px 0;
            color: #4b5563;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #374151;
        }

        select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
            min-height: 110px;
        }

        button, .back-button {
            display: block;
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .back-button {
            width: 220px;
            margin: 30px auto;
            background: #111827;
        }

        .empty {
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>

<body>
<div class="header">
    <h1>⭐ Ratings & Feedback</h1>
    <p>Share your experience and help improve SkillSwap</p>
</div>

<div class="container">

    <?php if ($message !== ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($bookings->num_rows === 0): ?>

        <div class="empty">
            <h2>No Sessions Available</h2>
            <br>
            <p>This session may not be completed, may not belong to your account, or may not exist.</p>
        </div>

    <?php else: ?>

        <?php while ($booking = $bookings->fetch_assoc()): ?>

            <div class="rating-card">
                <h2>
                    👤 Session with
                    <?php echo htmlspecialchars($booking["name"]); ?>
                </h2>

                <div class="session-info">
                    <p>📅 <strong>Date:</strong>
                        <?php echo htmlspecialchars($booking["session_date"]); ?>
                    </p>
                    <p>🕐 <strong>Time:</strong>
                        <?php echo htmlspecialchars($booking["session_time"]); ?>
                    </p>
                    <p>⏱️ <strong>Duration:</strong>
                        <?php echo htmlspecialchars($booking["duration"]); ?> minutes
                    </p>
                </div>

                <?php if ($booking["rating_id"] !== null): ?>

                    <p><strong>You have already submitted feedback for this session.</strong></p>

                <?php else: ?>

                    <form method="POST" action="ratings.php?booking_id=<?php echo (int) $booking["id"]; ?>">
                        <input type="hidden" name="booking_id"
                               value="<?php echo (int) $booking["id"]; ?>">

                        <div class="form-group">
                            <label>⭐ Rating</label>
                            <select name="rating" required>
                                <option value="">-- Select Rating --</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                <option value="3">⭐⭐⭐ Good</option>
                                <option value="2">⭐⭐ Needs Improvement</option>
                                <option value="1">⭐ Poor</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>💬 Feedback</label>
                            <textarea name="feedback"
                                      placeholder="Write your feedback..."></textarea>
                        </div>

                        <button type="submit">⭐ Submit Rating</button>
                    </form>

                <?php endif; ?>
            </div>

        <?php endwhile; ?>

    <?php endif; ?>

    <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
</div>
</body>
</html>