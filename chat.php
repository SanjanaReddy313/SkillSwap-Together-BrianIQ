<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];
$selected_user = $_GET["user_id"] ?? null;
$message = "";


/* Send message */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $receiver_id = $_POST["receiver_id"];
    $text = trim($_POST["message"]);

    if ($text != "") {

        $stmt = $conn->prepare(
            "INSERT INTO messages
            (sender_id, receiver_id, message)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iis",
            $user_id,
            $receiver_id,
            $text
        );

        $stmt->execute();

        $stmt->close();

        $selected_user = $receiver_id;
    }
}


/* Get other users */

$stmt = $conn->prepare(
    "SELECT id, name
     FROM users
     WHERE id != ?
     ORDER BY name"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$users = $stmt->get_result();


/* Get selected user's name */

$selected_name = "";

if ($selected_user) {

    $stmt2 = $conn->prepare(
        "SELECT name
         FROM users
         WHERE id = ?"
    );

    $stmt2->bind_param("i", $selected_user);
    $stmt2->execute();

    $result2 = $stmt2->get_result();

    if ($result2->num_rows == 1) {

        $person = $result2->fetch_assoc();

        $selected_name = $person["name"];
    }

    $stmt2->close();
}


/* Get messages */

$messages = null;

if ($selected_user) {

    $stmt3 = $conn->prepare(
        "SELECT
            sender_id,
            receiver_id,
            message,
            created_at
         FROM messages
         WHERE
            (sender_id = ? AND receiver_id = ?)
            OR
            (sender_id = ? AND receiver_id = ?)
         ORDER BY created_at ASC"
    );

    $stmt3->bind_param(
        "iiii",
        $user_id,
        $selected_user,
        $selected_user,
        $user_id
    );

    $stmt3->execute();

    $messages = $stmt3->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - Chat</title>

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
            max-width: 1000px;
            margin: 40px auto;
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

        /* Chat Layout */

        .chat-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            min-height: 550px;
        }

        /* People */

        .people {
            background: #f8fafc;
            border-right: 1px solid #e5e7eb;
            padding: 20px;
        }

        .people-title {
            font-size: 19px;
            font-weight: bold;
            color: #334155;
            margin-bottom: 18px;
        }

        .person-link {
            display: block;
            text-decoration: none;
            color: #334155;
            background: white;
            padding: 13px;
            margin-bottom: 10px;
            border-radius: 11px;
            border: 1px solid #e5e7eb;
            font-weight: bold;
            transition: 0.2s;
        }

        .person-link:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #2563eb;
        }

        .no-people {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Chat Area */

        .chat-area {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: white;
        }

        .chat-header h2 {
            color: #2563eb;
            font-size: 21px;
        }

        .chat-header p {
            color: #64748b;
            font-size: 13px;
            margin-top: 5px;
        }

        /* Messages */

        .messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            min-height: 350px;
            max-height: 430px;
            background: #f8fafc;
        }

        .message-row {
            display: flex;
            margin-bottom: 14px;
        }

        .message-row.you {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 15px;
            border-radius: 15px;
            background: white;
            border: 1px solid #e2e8f0;
            color: #334155;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .message-row.you .message-bubble {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            border-bottom-right-radius: 4px;
        }

        .message-row.other .message-bubble {
            border-bottom-left-radius: 4px;
        }

        .message-sender {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            opacity: 0.75;
        }

        .message-time {
            font-size: 10px;
            margin-top: 5px;
            opacity: 0.65;
        }

        /* Empty Chat */

        .empty-chat {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        .empty-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .empty-chat h3 {
            color: #334155;
            margin-bottom: 8px;
        }

        /* Message Form */

        .message-form {
            padding: 15px;
            border-top: 1px solid #e5e7eb;
            background: white;
        }

        .message-form form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        textarea {
            flex: 1;
            resize: none;
            min-height: 48px;
            max-height: 120px;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
        }

        textarea:focus {
            border-color: #2563eb;
        }

        .send-btn {
            border: none;
            background: #2563eb;
            color: white;
            padding: 13px 20px;
            border-radius: 11px;
            font-weight: bold;
            cursor: pointer;
        }

        .send-btn:hover {
            background: #1d4ed8;
        }

        /* Select User */

        .select-message {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 30px;
            background: #f8fafc;
        }

        .select-icon {
            font-size: 55px;
            margin-bottom: 15px;
        }

        .select-message h2 {
            color: #334155;
            margin-bottom: 8px;
        }

        .select-message p {
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

        @media (max-width: 700px) {

            .header {
                padding: 18px;
            }

            .logo {
                font-size: 21px;
            }

            .container {
                margin-top: 25px;
            }

            .page-title {
                font-size: 27px;
            }

            .chat-container {
                grid-template-columns: 1fr;
            }

            .people {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .message-form form {
                flex-direction: column;
                align-items: stretch;
            }

            .send-btn {
                width: 100%;
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


<div class="container">

    <h1 class="page-title">
        💬 SkillSwap Chat
    </h1>

    <p class="subtitle">
        Connect and communicate with other SkillSwap users.
    </p>


    <div class="chat-container">


        <!-- People List -->

        <div class="people">

            <div class="people-title">
                👥 People
            </div>


            <?php if ($users->num_rows == 0): ?>

                <div class="no-people">
                    No other users are available yet.
                </div>

            <?php else: ?>

                <?php while ($user = $users->fetch_assoc()): ?>

                    <a
                        href="chat.php?user_id=<?php echo $user["id"]; ?>"
                        class="person-link"
                    >

                        👤
                        <?php
                        echo htmlspecialchars($user["name"]);
                        ?>

                    </a>

                <?php endwhile; ?>

            <?php endif; ?>

        </div>


        <!-- Chat Area -->

        <div class="chat-area">


            <?php if ($selected_user && $selected_name != ""): ?>


                <div class="chat-header">

                    <h2>
                        💬 Chat with
                        <?php
                        echo htmlspecialchars($selected_name);
                        ?>
                    </h2>

                    <p>
                        Send a message to start the conversation.
                    </p>

                </div>


                <div class="messages">


                    <?php if ($messages && $messages->num_rows > 0): ?>


                        <?php while ($chat = $messages->fetch_assoc()): ?>

                            <?php
                            $is_you =
                                ($chat["sender_id"] == $user_id);
                            ?>

                            <div
                                class="message-row
                                <?php echo $is_you ? 'you' : 'other'; ?>"
                            >

                                <div class="message-bubble">

                                    <div class="message-sender">

                                        <?php if ($is_you): ?>

                                            You

                                        <?php else: ?>

                                            <?php
                                            echo htmlspecialchars(
                                                $selected_name
                                            );
                                            ?>

                                        <?php endif; ?>

                                    </div>


                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $chat["message"]
                                        )
                                    );
                                    ?>


                                    <div class="message-time">

                                        <?php
                                        echo htmlspecialchars(
                                            $chat["created_at"]
                                        );
                                        ?>

                                    </div>

                                </div>

                            </div>

                        <?php endwhile; ?>


                    <?php else: ?>


                        <div class="empty-chat">

                            <div>

                                <div class="empty-icon">
                                    💬
                                </div>

                                <h3>
                                    No messages yet
                                </h3>

                                <p>
                                    Send the first message to start chatting.
                                </p>

                            </div>

                        </div>


                    <?php endif; ?>


                </div>


                <!-- Message Form -->

                <div class="message-form">

                    <form method="POST">

                        <input
                            type="hidden"
                            name="receiver_id"
                            value="<?php echo $selected_user; ?>"
                        >

                        <textarea
                            name="message"
                            placeholder="Type your message..."
                            required
                        ></textarea>

                        <button
                            type="submit"
                            class="send-btn"
                        >
                            ➤ Send
                        </button>

                    </form>

                </div>


            <?php else: ?>


                <div class="select-message">

                    <div>

                        <div class="select-icon">
                            💬
                        </div>

                        <h2>
                            Select a Person
                        </h2>

                        <p>
                            Choose someone from the People list
                            to start chatting.
                        </p>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>


    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

</div>

</body>

</html>