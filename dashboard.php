<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

use phpseclib3\Crypt\AES;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Initialize AES with CBC mode
$cipher = new AES('cbc');
$cipher->setKey(ENCRYPTION_KEY);

// Handle form submission to save passwords
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_name = htmlspecialchars(trim($_POST['site_name']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Generate a random IV
    $iv = random_bytes(16); // AES block size is 16 bytes
    $cipher->setIV($iv);

    // Encrypt the password
    $encrypted_password = base64_encode($iv . $cipher->encrypt($password));

    $stmt = $conn->prepare("INSERT INTO passwords (user_id, site_name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $site_name, $encrypted_password);
    $stmt->execute();
    $stmt->close();
}

// Fetch saved passwords
$stmt = $conn->prepare("SELECT site_name, password FROM passwords WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$passwords = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input {
            padding: 8px;
            margin: 5px 0;
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
        }
        button:hover {
            background-color: #45a049;
        }
        .password-list {
            margin-top: 20px;
        }
        .password-item {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .password-item .site-name {
            font-weight: bold;
        }
        .password-item .site-password {
            margin-top: 5px;
            word-break: break-all;
        }
        .logout-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
        <form action="dashboard.php" method="post">
            <label for="site_name">Site Name:</label>
            <input type="text" id="site_name" name="site_name" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Save Password</button>
        </form>

        <div class="password-list">
            <?php foreach ($passwords as $password): ?>
                <div class="password-item">
    <div class="site-name"><?= htmlspecialchars($password['site_name']) ?></div>
    <div class="site-password">
        <?php
        // Decode base64 and extract IV and encrypted password
        $data = base64_decode($password['password']);
        
        if ($data === false || strlen($data) < 16) {
            echo "Decryption error: Invalid data length.";
        } else {
            $iv = substr($data, 0, 16); // Extract the first 16 bytes as IV
            $ciphertext = substr($data, 16); // The rest is the ciphertext

            // Set the IV and decrypt
            $cipher->setIV($iv);
            $decrypted = $cipher->decrypt($ciphertext);
            if ($decrypted === false) {
                echo "Decryption error: Could not decrypt the data.";
            } else {
                echo htmlspecialchars($decrypted);
            }
        }
        ?>
    </div>
</div>

            <?php endforeach; ?>
        </div>

        <a href="logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>
