<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$current_user_id = (int) $_SESSION["user_id"];
$error = "";

/* Load users for the dropdown */
$stmt = $conn->prepare(
    "SELECT id, name FROM users WHERE id != ? ORDER BY name"
);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$users_result = $stmt->get_result();

$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

$other_user_id = (int) ($_GET["user_id"] ?? 0);

/* Create booking */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $other_user_id = (int) ($_POST["other_user_id"] ?? 0);
    $session_date = trim($_POST["session_date"] ?? "");
    $session_time = trim($_POST["session_time"] ?? "");
    $duration = (int) ($_POST["duration"] ?? 0);
    $message = trim($_POST["message"] ?? "");

    if ($other_user_id <= 0 || $other_user_id === $current_user_id) {
        $error = "Please select another user.";
    } elseif ($session_date === "" || $session_time === "") {
        $error = "Please select a date and time.";
    } elseif (!in_array($duration, [30, 60, 90, 120], true)) {
        $error = "Please select a valid duration.";
    } elseif (
        strtotime($session_date . " " . $session_time) === false ||
        strtotime($session_date . " " . $session_time) <= time()
    ) {
        $error = "Please choose a future date and time.";
    } else {
        /* Confirm the selected user exists */
        $check = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $check->bind_param("i", $other_user_id);
        $check->execute();

        if (!$check->get_result()->fetch_assoc()) {
            $error = "The selected user was not found.";
        } else {
            $insert = $conn->prepare(
                "INSERT INTO bookings
                (user_id, other_user_id, session_date, session_time,
                 duration, message, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')"
            );

            $insert->bind_param(
                "iissis",
                $current_user_id,
                $other_user_id,
                $session_date,
                $session_time,
                $duration,
                $message
            );

            if ($insert->execute()) {
                $notification_message = "You have received a new session booking.";

                $notify = $conn->prepare(
                    "INSERT INTO notifications (user_id, message)
                     VALUES (?, ?)"
                );
                $notify->bind_param(
                    "is",
                    $other_user_id,
                    $notification_message
                );
                $notify->execute();

                header("Location: my_bookings.php?booked=1");
                exit;
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book a Session | SkillSwap</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0; padding: 20px;
    font-family: Arial, sans-serif;
    background: #f1f5f9; color: #1e293b;
}
.container {
    max-width: 600px; margin: 30px auto; padding: 28px;
    background: white; border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0,0,0,.08);
}
h1 { text-align: center; color: #2563eb; }
label { display: block; margin: 16px 0 7px; font-weight: bold; }
input, select, textarea {
    width: 100%; padding: 12px;
    border: 1px solid #cbd5e1; border-radius: 8px;
    font-size: 15px; background: white;
}
textarea { min-height: 90px; }
button {
    width: 100%; margin-top: 22px; padding: 13px;
    background: #2563eb; color: white; border: 0;
    border-radius: 8px; font-size: 16px; cursor: pointer;
}
.error {
    padding: 12px; margin-bottom: 16px;
    color: #991b1b; background: #fee2e2; border-radius: 8px;
}
.back { display: block; text-align: center; margin-top: 18px; }
</style>
</head>
<body>
<div class="container">
    <h1>Book a Session</h1>

    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p>No other registered users were found.</p>
    <?php else: ?>
    <form method="POST" action="booking.php">
        <label for="other_user_id">Select User</label>
        <select id="other_user_id" name="other_user_id" required>
            <option value="">Choose a user</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo (int) $user["id"]; ?>"
                    <?php echo ((int) $user["id"] === $other_user_id) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($user["name"]); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="session_date">Session Date</label>
        <input type="date" id="session_date" name="session_date"
               min="<?php echo date("Y-m-d"); ?>" required>

        <label for="session_time">Session Time</label>
        <input type="time" id="session_time" name="session_time" required>

        <label for="duration">Duration</label>
        <select id="duration" name="duration" required>
            <option value="">Choose duration</option>
            <option value="30">30 minutes</option>
            <option value="60">1 hour</option>
            <option value="90">1 hour 30 minutes</option>
            <option value="120">2 hours</option>
        </select>

        <label for="message">Message (optional)</label>
        <textarea id="message" name="message"
                  placeholder="Write a message..."></textarea>

        <button type="submit">Confirm Booking</button>
    </form>
    <?php endif; ?>

    <a class="back" href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>