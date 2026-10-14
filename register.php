<?php
session_start();
include "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password != $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered.";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Account created successfully!";

            } else {

                $message = "Something went wrong. Please try again.";

            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Account - SkillSwap</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px 20px;
        }

        .register-container {
            width: 100%;
            max-width: 470px;
        }

        .logo {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }

        .logo h1 {
            font-size: 34px;
            margin-bottom: 6px;
        }

        .logo p {
            font-size: 14px;
            opacity: 0.9;
        }

        .register-card {
            background: white;
            padding: 38px;
            border-radius: 22px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.20);
        }

        .register-card h2 {
            text-align: center;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }

        .register-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 5px;
        }

        .register-btn:hover {
            background: #1d4ed8;
        }

        .message {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-text {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .login-text a {
            color: #2563eb;
            font-weight: bold;
            text-decoration: none;
        }

        .login-text a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            color: white;
            font-size: 12px;
            margin-top: 20px;
            opacity: 0.8;
        }

        @media (max-width: 500px) {

            .register-card {
                padding: 28px 22px;
            }

            .logo h1 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="logo">

        <h1>SkillSwap</h1>

        <p>Learn • Teach • Connect</p>

    </div>

    <div class="register-card">

        <h2>Create Account</h2>

        <p class="subtitle">
            Join SkillSwap and start learning together
        </p>

        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >

            </div>

            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >

            </div>

            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

            </div>

            <button type="submit" class="register-btn">
                Create Account
            </button>

        </form>

        <div class="login-text">

            Already have an account?
            <a href="login.php">Login here</a>

        </div>

    </div>

    <div class="footer">
        © 2026 SkillSwap • Learn and grow together
    </div>

</div>

</body>

</html>