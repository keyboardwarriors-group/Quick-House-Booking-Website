<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the current user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users
$stmt = $pdo->query("SELECT id, first_name, last_name, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reset password if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    // Hash the new password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the user's password in the database
    try {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$password_hash, $user_id]);

        $_SESSION['message'] = "Password reset successfully!";
        header("Location: admin_reset_password.php");
        exit();
    } catch (PDOException $e) {
        die("Error resetting password: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset User Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        select, input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_profile.php">Back to Dashboard</a>
        <h1>Reset User Password</h1>

        <!-- Display success message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div style="color: green;">
                <p><?= htmlspecialchars($_SESSION['message']) ?></p>
                <?php unset($_SESSION['message']); // Clear message after displaying ?>
            </div>
        <?php endif; ?>

        <!-- Password Reset Form -->
        <form method="POST">
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id']) ?>">
                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required>

            <button type="submit">Reset Password</button>
        </form>
    </div>

</body>
</html>