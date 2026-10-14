<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];
$message = "";

/* Accept or reject request */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $connection_id = $_POST["connection_id"];
    $action = $_POST["action"];

    if ($action == "accept") {

        $status = "accepted";

        $stmt = $conn->prepare(
            "UPDATE connections
             SET status = ?
             WHERE id = ? AND receiver_id = ?"
        );

        $stmt->bind_param(
            "sii",
            $status,
            $connection_id,
            $user_id
        );

        if ($stmt->execute()) {
            $message = "Connection request accepted!";
        }

        $stmt->close();

    } elseif ($action == "reject") {

        $status = "rejected";

        $stmt = $conn->prepare(
            "UPDATE connections
             SET status = ?
             WHERE id = ? AND receiver_id = ?"
        );

        $stmt->bind_param(
            "sii",
            $status,
            $connection_id,
            $user_id
        );

        if ($stmt->execute()) {
            $message = "Connection request rejected.";
        }

        $stmt->close();
    }
}

/* Get pending requests */

$stmt = $conn->prepare(
    "SELECT
        connections.id,
        connections.message,
        connections.created_at,
        users.name,
        users.location
     FROM connections
     INNER JOIN users
        ON connections.sender_id = users.id
     WHERE connections.receiver_id = ?
     AND connections.status = 'pending'
     ORDER BY connections.created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$requests = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>SkillSwap - Connection Requests</title>

</head>

<body>

<h1>Connection Requests</h1>

<?php if ($message != ""): ?>

    <p>
        <?php echo htmlspecialchars($message); ?>
    </p>

<?php endif; ?>

<?php if ($requests->num_rows == 0): ?>

    <p>No pending connection requests.</p>

<?php else: ?>

    <?php while ($request = $requests->fetch_assoc()): ?>

        <hr>

        <h2>
            <?php echo htmlspecialchars($request["name"]); ?>
        </h2>

        <p>
            <strong>Location:</strong>
            <?php echo htmlspecialchars($request["location"]); ?>
        </p>

        <p>
            <strong>Message:</strong><br>
            <?php echo htmlspecialchars($request["message"]); ?>
        </p>

        <form method="POST">

            <input
                type="hidden"
                name="connection_id"
                value="<?php echo $request["id"]; ?>"
            >

            <button
                type="submit"
                name="action"
                value="accept"
            >
                Accept
            </button>

            <button
                type="submit"
                name="action"
                value="reject"
            >
                Reject
            </button>

        </form>

    <?php endwhile; ?>

<?php endif; ?>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>

</html>