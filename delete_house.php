<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the house ID from the URL
if (!isset($_GET['id'])) {
    die("House ID not provided.");
}
$house_id = $_GET['id'];

// Fetch the house details
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ?");
$stmt->execute([$house_id]);
$house = $stmt->fetch();

// Check if the house exists
if (!$house) {
    die("House not found.");
}

// Check if the logged-in user is the owner or an admin
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
if ($house['user_id'] !== $user_id && $role !== 'admin') {
    die("You do not have permission to delete this house.");
}

// Delete the house
try {
    $stmt = $pdo->prepare("DELETE FROM houses WHERE id = ?");
    $stmt->execute([$house_id]);

    $_SESSION['message'] = "House deleted successfully!";
    header("Location: " . ($role === 'admin' ? 'admin_profile.php' : 'user_profile.php'));
    exit();
} catch (PDOException $e) {
    die("Error deleting house: " . $e->getMessage());
}
?>