<?php
require 'db.php'; // Include your database connection

// Admin account details
$first_name = '';
$last_name = 'Kamara';
$email = 'Abdulai7396@gmail.com';
$password = 'user1234'; // Plain text password
$role = 'user';

// Hash the password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert admin account into the database
try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $password_hash, $role]);

    echo "Admin account created successfully!";
} catch (PDOException $e) {
    die("Error creating admin account: " . $e->getMessage());
}
?>