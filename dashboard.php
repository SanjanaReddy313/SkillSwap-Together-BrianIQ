<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["user_name"];
$email = $_SESSION["user_email"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - Dashboard</title>

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

        .logout {
            color: white;
            text-decoration: none;
            background: #1d4ed8;
            padding: 10px 18px;
            border-radius: 8px;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .welcome h1 {
            color: #2563eb;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #666;
            margin-top: 6px;
        }

        .section-title {
            margin-bottom: 15px;
            color: #333;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            color: #222;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .icon {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .card h3 {
            color: #2563eb;
            margin-bottom: 7px;
        }

        .card p {
            color: #777;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #777;
        }

        @media (max-width: 600px) {

            .header {
                padding: 18px;
            }

            .container {
                margin-top: 25px;
            }

            .welcome {
                padding: 22px;
            }

        }

    </style>

</head>

<body>

<div class="header">

    <div class="logo">
        🔄 SkillSwap
    </div>

    <a href="logout.php" class="logout">
        Logout
    </a>

</div>


<div class="container">

    <div class="welcome">

        <h1>
            Hello, <?php echo htmlspecialchars($name); ?> 👋
        </h1>

        <p>
            Welcome back to SkillSwap!
        </p>

        <p>
            <?php echo htmlspecialchars($email); ?>
        </p>

    </div>


    <h2 class="section-title">
        What would you like to do?
    </h2>


    <div class="menu">


        <a href="profile.php" class="card">

            <div class="icon">👤</div>

            <h3>My Profile</h3>

            <p>
                View and update your profile.
            </p>

        </a>


        <a href="skills.php" class="card">

            <div class="icon">🛠️</div>

            <h3>My Skills</h3>

            <p>
                Add skills you can teach or want to learn.
            </p>

        </a>


        <a href="explore.php" class="card">

            <div class="icon">🔎</div>

            <h3>Find People</h3>

            <p>
                Explore people and their skills.
            </p>

        </a>


        <a href="connect.php" class="card">

            <div class="icon">🤝</div>

            <h3>Connect</h3>

            <p>
                Send connection requests.
            </p>

        </a>


        <a href="booking.php" class="card">

            <div class="icon">📅</div>

            <h3>Book a Session</h3>

            <p>
                Schedule a learning session.
            </p>

        </a>


        <a href="my_bookings.php" class="card">

            <div class="icon">📋</div>

            <h3>My Bookings</h3>

            <p>
                View your scheduled sessions.
            </p>

        </a>


        <a href="chat.php" class="card">

            <div class="icon">💬</div>

            <h3>Chat</h3>

            <p>
                Chat with your SkillSwap connections.
            </p>

        </a>


        <a href="notifications.php" class="card">

            <div class="icon">🔔</div>

            <h3>Notifications</h3>

            <p>
                View your latest notifications.
            </p>

        </a>


        <a href="ratings.php" class="card">

            <div class="icon">⭐</div>

            <h3>Ratings & Feedback</h3>

            <p>
                Rate your sessions and share feedback.
            </p>

        </a>


    </div>


    <div class="footer">

        SkillSwap © 2026 — Learn • Teach • Connect

    </div>

</div>

</body>

</html>