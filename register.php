<?php
session_start();
require 'db.php'; // Include your database connection

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to check if email already exists in the database
function isEmailUnique($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
    if (!$stmt) {
        die("Prepare failed: " . $pdo->errorInfo()[2]); // Debugging: Check if prepare failed
    }

    $stmt->execute([$email]);
    if ($stmt->errorCode() !== '00000') {
        die("Execute failed: " . $stmt->errorInfo()[2]); // Debugging: Check if execute failed
    }

    return $stmt->fetch() === false; // Returns true if email is unique
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'user'; // Default to 'user' if role is not provided

    // Validation errors array
    $errors = [];

    // Validate first name
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    } elseif (strlen($first_name) > 50) {
        $errors[] = "First name must be less than 50 characters.";
    }

    // Validate last name
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    } elseif (strlen($last_name) > 50) {
        $errors[] = "Last name must be less than 50 characters.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!isValidEmail($email)) {
        $errors[] = "Invalid email format.";
    } elseif (!isEmailUnique($pdo, $email)) {
        $errors[] = "Email already exists.";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into the database
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$first_name, $last_name, $email, $password_hash, $role])) {
            $_SESSION['message'] = "Registration successful!";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    // If there are errors, store them in the session and redirect back to the registration form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
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
</style>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <form id="registerForm" action="register.php" method="POST">
        <a href="index.html">Back</a>
        <h2>Register</h2>

        <!-- Display validation errors -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div style="color: red;">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); // Clear errors after displaying ?>
            </div>
        <?php endif; ?>


        <!-- Form fields -->
        <input type="text" name="first_name" placeholder="First Name" required autocomplete="on" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
        <input type="text" name="last_name" placeholder="Last Name" required autocomplete="on" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
        <input type="email" name="email" placeholder="Email" required autocomplete="on" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <input type="password" name="password" placeholder="Password" required autocomplete="on">
        <input type="hidden" name="role" value="user"> <!-- Default role -->
        
        <button type="submit">Register</button>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
</body>
</html>