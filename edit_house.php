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
    die("You do not have permission to edit this house.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $rooms = (int)$_POST['rooms'];
    $price = (float)$_POST['price'];
    $type = trim($_POST['type']);

    // Update the house in the database
    try {
        $stmt = $pdo->prepare("UPDATE houses SET title = ?, location = ?, rooms = ?, price = ?, type = ? WHERE id = ?");
        $stmt->execute([$title, $location, $rooms, $price, $type, $house_id]);

        $_SESSION['message'] = "House updated successfully!";
        header("Location: " . ($role === 'admin' ? 'admin_profile.php' : 'user_profile.php'));
        exit();
    } catch (PDOException $e) {
        die("Error updating house: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit House</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <form action="edit_house.php?id=<?= $house_id ?>" method="POST">
        <h2>Edit House</h2>
        <input type="text" name="title" placeholder="Title" value="<?= htmlspecialchars($house['title']) ?>" required>
        <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($house['location']) ?>" required>
        <input type="number" name="rooms" placeholder="Number of Rooms" value="<?= htmlspecialchars($house['rooms']) ?>" required>
        <input type="number" name="price" placeholder="Price" value="<?= htmlspecialchars($house['price']) ?>" required>
        <select name="type" required>
            <option value="Apartment" <?= $house['type'] === 'Apartment' ? 'selected' : '' ?>>Apartment</option>
            <option value="Villa" <?= $house['type'] === 'Villa' ? 'selected' : '' ?>>Villa</option>
            <option value="Cottage" <?= $house['type'] === 'Cottage' ? 'selected' : '' ?>>Cottage</option>
        </select>
        <button type="submit">Update House</button>
    </form>
</body>
</html>