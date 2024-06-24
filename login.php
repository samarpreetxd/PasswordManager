<?php
session_start();
require 'config.php';

$logFile = 'login_attempts.txt';
$error_message = '';

// Rate limiting settings
$maxAttempts = 5;
$lockoutTime = 15 * 60; // 15 minutes

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

function generateCaptcha($length = 5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha = '';
    $charLength = strlen($characters);
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, $charLength - 1)];
    }
    return $captcha;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rate limiting
    if ($_SESSION['login_attempts'] >= $maxAttempts && (time() - $_SESSION['last_attempt_time']) < $lockoutTime) {
        $error_message = "Too many login attempts. Please try again later.";
    } else {
        // Input sanitization
        $username = htmlspecialchars(trim($_POST['username']));
        $password = htmlspecialchars(trim($_POST['password']));
        $userCaptcha = htmlspecialchars(trim($_POST['captcha']));
        $storedCaptcha = isset($_SESSION['captcha']) ? $_SESSION['captcha'] : '';

        // Verify CAPTCHA
        if (empty($userCaptcha) || $userCaptcha !== $storedCaptcha) {
            $error_message = "Invalid CAPTCHA. Please try again.";
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;

                    // Reset login attempts on successful login
                    $_SESSION['login_attempts'] = 0;

                    // Log successful login
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - IP: " . $_SERVER['REMOTE_ADDR'] . " - Browser: " . $_SERVER['HTTP_USER_AGENT'] . " - Login successful for user: $username\n", FILE_APPEND);

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();

                    // Log failed login attempt
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - IP: " . $_SERVER['REMOTE_ADDR'] . " - Browser: " . $_SERVER['HTTP_USER_AGENT'] . " - Login failed for user: $username (wrong password)\n", FILE_APPEND);
                    $error_message = "Invalid username or password.";
                }
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();

                // Log failed login attempt
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - IP: " . $_SERVER['REMOTE_ADDR'] . " - Browser: " . $_SERVER['HTTP_USER_AGENT'] . " - Login failed for user: $username (user not found)\n", FILE_APPEND);
                $error_message = "Invalid username or password.";
            }

            $stmt->close();
            $conn->close();
        }
    }
}

// Generate new CAPTCHA
$captcha = generateCaptcha(5);
$_SESSION['captcha'] = $captcha;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Roboto', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #ffffff;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 8px;
            width: 320px;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #ff4f4f;
            margin-top: 10px;
            text-align: center; 
        }
        .nav-button {
            color: #4CAF50;
            margin-top: 10px;
            text-decoration: none;
            font-weight: bold;
            display: block;
        }
        .nav-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <label for="captcha">Enter CAPTCHA:</label>
            <input type="text" name="captcha" required>
            <p><strong>CAPTCHA:</strong> <?= $captcha ?></p>
            <button type="submit">Login</button>
        </form>
        <?php if ($error_message): ?>
            <p class="error"><?= $error_message ?></p>
        <?php endif; ?>
        <a href="index.php" class="nav-button">Back to Homepage</a>
    </div>
</body>
</html>
