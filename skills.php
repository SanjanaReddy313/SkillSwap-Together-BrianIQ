<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = $_SESSION["user_id"];
$message = "";

/* Add skill */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $skill_id = $_POST["skill_id"];
    $type = $_POST["type"];

    $check = $conn->prepare(
        "SELECT id FROM user_skills
         WHERE user_id = ? AND skill_id = ? AND type = ?"
    );

    $check->bind_param("iis", $user_id, $skill_id, $type);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows > 0) {

        $message = "This skill is already added.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO user_skills (user_id, skill_id, type)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("iis", $user_id, $skill_id, $type);

        if ($stmt->execute()) {
            $message = "Skill added successfully!";
        } else {
            $message = "Something went wrong.";
        }

        $stmt->close();
    }

    $check->close();
}


/* Get all available skills */
$skills = $conn->query(
    "SELECT id, name FROM skills ORDER BY name"
);


/* Get skills added by current user */
$my_skills = $conn->prepare(
    "SELECT skills.name, user_skills.type
     FROM user_skills
     INNER JOIN skills
     ON user_skills.skill_id = skills.id
     WHERE user_skills.user_id = ?
     ORDER BY skills.name"
);

$my_skills->bind_param("i", $user_id);
$my_skills->execute();

$my_skills_result = $my_skills->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>SkillSwap - My Skills</title>

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
            max-width: 750px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 35px;
            border-radius: 22px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .title {
            text-align: center;
            margin-bottom: 10px;
            color: #2563eb;
            font-size: 30px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        .message {
            background: #eff6ff;
            color: #2563eb;
            padding: 13px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
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

        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            background: white;
            outline: none;
        }

        select:focus {
            border-color: #2563eb;
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

        .section-title {
            color: #2563eb;
            margin-bottom: 18px;
            font-size: 22px;
        }

        .skill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .skill-name {
            font-weight: bold;
            color: #333;
        }

        .skill-type {
            padding: 6px 12px;
            border-radius: 20px;
            background: #dbeafe;
            color: #2563eb;
            font-size: 13px;
            font-weight: bold;
        }

        .empty {
            color: #777;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
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

            .skill-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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


    <!-- ADD SKILL -->

    <div class="card">

        <h1 class="title">
            🛠️ My Skills
        </h1>

        <p class="subtitle">
            Add skills you can teach or want to learn.
        </p>


        <?php if ($message != ""): ?>

            <div class="message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label>
                    Select Skill
                </label>

                <select name="skill_id" required>

                    <option value="">
                        -- Select Skill --
                    </option>

                    <?php while ($skill = $skills->fetch_assoc()): ?>

                        <option value="<?php echo $skill["id"]; ?>">

                            <?php
                            echo htmlspecialchars($skill["name"]);
                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="form-group">

                <label>
                    What do you want to do?
                </label>

                <select name="type" required>

                    <option value="">
                        -- Select --
                    </option>

                    <option value="teach">
                        👨‍🏫 I can teach
                    </option>

                    <option value="learn">
                        📚 I want to learn
                    </option>

                </select>

            </div>


            <button type="submit">
                ➕ Add Skill
            </button>

        </form>

    </div>


    <!-- MY ADDED SKILLS -->

    <div class="card">

        <h2 class="section-title">
            ⭐ My Added Skills
        </h2>


        <?php if ($my_skills_result->num_rows > 0): ?>


            <?php while ($my_skill = $my_skills_result->fetch_assoc()): ?>

                <div class="skill-item">

                    <div class="skill-name">

                        🛠️
                        <?php
                        echo htmlspecialchars($my_skill["name"]);
                        ?>

                    </div>


                    <div class="skill-type">

                        <?php if ($my_skill["type"] == "teach"): ?>

                            👨‍🏫 Can Teach

                        <?php else: ?>

                            📚 Wants to Learn

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>


        <?php else: ?>

            <div class="empty">

                You haven't added any skills yet.

            </div>

        <?php endif; ?>


    </div>


    <a href="dashboard.php" class="dashboard-link">
        ← Back to Dashboard
    </a>


</div>

</body>

</html>