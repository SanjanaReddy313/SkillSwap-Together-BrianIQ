<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];

$query = "
    SELECT 
        users.id,
        users.name,
        users.location,
        users.about,
        users.profile_image,
        skills.name AS skill_name,
        user_skills.type
    FROM user_skills
    INNER JOIN users
        ON user_skills.user_id = users.id
    INNER JOIN skills
        ON user_skills.skill_id = skills.id
    WHERE users.id != ?
    ORDER BY users.name
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - Find People</title>


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
            max-width: 1050px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            color: #2563eb;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .page-subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 35px;
        }

        .people {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(280px, 1fr)
            );
            gap: 22px;
        }

        .person-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: 0.2s;
        }

        .person-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .profile-photo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #dbeafe;
            margin-bottom: 15px;
        }

        .default-photo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 45px;
        }

        .person-name {
            color: #2563eb;
            font-size: 22px;
            margin-bottom: 12px;
        }

        .info {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .skill-box {
            margin-top: 18px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .skill-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .skill-type {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            background: #dbeafe;
            color: #2563eb;
            font-size: 13px;
            font-weight: bold;
        }

        .empty {
            background: white;
            padding: 35px;
            border-radius: 18px;
            text-align: center;
            color: #777;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
        }

        .dashboard-link {
            display: block;
            width: fit-content;
            margin: 35px auto 10px;
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .dashboard-link:hover {
            text-decoration: underline;
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

            .page-title {
                font-size: 27px;
            }

            .person-card {
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

    <a href="dashboard.php" class="back">
        Dashboard
    </a>

</div>


<div class="container">


    <h1 class="page-title">
        👥 Find People
    </h1>

    <p class="page-subtitle">
        Discover people to learn from or teach.
    </p>


    <?php if ($result->num_rows == 0): ?>

        <div class="empty">

            <h3>
                No other users with skills found yet.
            </h3>

            <p style="margin-top:10px;">
                More SkillSwap members will appear here
                when they add their skills.
            </p>

        </div>


    <?php else: ?>


        <div class="people">


            <?php while ($person = $result->fetch_assoc()): ?>


                <div class="person-card">


                    <?php if (!empty($person["profile_image"])): ?>

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $person["profile_image"]
                                );
                            ?>"
                            class="profile-photo"
                            alt="Profile Photo"
                        >

                    <?php else: ?>

                        <div class="default-photo">
                            👤
                        </div>

                    <?php endif; ?>


                    <h2 class="person-name">

                        <?php
                        echo htmlspecialchars(
                            $person["name"]
                        );
                        ?>

                    </h2>


                    <p class="info">

                        📍
                        <strong>Location:</strong>

                        <?php
                        echo htmlspecialchars(
                            $person["location"] ?? "Not provided"
                        );
                        ?>

                    </p>


                    <p class="info">

                        <?php
                        echo htmlspecialchars(
                            $person["about"] ?? "No information available."
                        );
                        ?>

                    </p>


                    <div class="skill-box">


                        <div class="skill-name">

                            🛠️
                            <?php
                            echo htmlspecialchars(
                                $person["skill_name"]
                            );
                            ?>

                        </div>


                        <span class="skill-type">

                            <?php
                            if ($person["type"] == "teach") {
                                echo "👨‍🏫 Can Teach";
                            } else {
                                echo "📚 Wants to Learn";
                            }
                            ?>

                        </span>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php endif; ?>


    <a href="dashboard.php"
       class="dashboard-link">

        ← Back to Dashboard

    </a>


</div>


</body>

</html>