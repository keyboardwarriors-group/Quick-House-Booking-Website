<?php
session_start();
require 'db.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user details in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin_profile.php");
        } else {
            header("Location: user_profile.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password!";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        input {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form id="loginForm" action="login.php" method="POST">
        <a href="index.html">Back</a>
        <h2>Login</h2>

        <!-- Display error message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: red;">
                <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); // Clear error after displaying ?>
            </div>
        <?php endif; ?>

        <!-- Form fields -->
        <input type="email" name="email" placeholder="Email" required autocomplete="on">
        <input type="password" name="password" placeholder="Password" required autocomplete="on">
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </form>
</body>
</html>