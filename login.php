<?php
session_start();
include "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, name, email, password FROM users WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];

            header("Location: dashboard.php");
            exit();

        } else {
            $message = "Invalid email or password.";
        }

    } else {
        $message = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SkillSwap</title>

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
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
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

        .login-card {
            background: white;
            padding: 38px;
            border-radius: 22px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.20);
        }

        .login-card h2 {
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
            margin-bottom: 20px;
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

        .login-btn {
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
        }

        .login-btn:hover {
            background: #1d4ed8;
        }

        .message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .signup-text {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
            font-size: 14px;
        }

        .signup-text a {
            color: #2563eb;
            font-weight: bold;
            text-decoration: none;
        }

        .signup-text a:hover {
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

            .login-card {
                padding: 28px 22px;
            }

            .logo h1 {
                font-size: 28px;
            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="logo">
        <h1>SkillSwap</h1>
        <p>Learn • Teach • Connect</p>
    </div>

    <div class="login-card">

        <h2>Welcome Back!</h2>

        <p class="subtitle">
            Login to continue your SkillSwap journey
        </p>

        <?php if ($message != "") { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST">

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
                    placeholder="Enter your password"
                    required
                >

            </div>

            <button type="submit" class="login-btn">
                Login
            </button>

        </form>

        <div class="signup-text">

            Don't have an account?
            <a href="register.php">Create Account</a>

        </div>

    </div>

    <div class="footer">
        © 2026 SkillSwap • Learn and grow together
    </div>

</div>

</body>

</html>