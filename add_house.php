<?php
session_start();
require 'db.php'; // Include your database connection


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $rooms = (int)$_POST['rooms'];
    $price = (float)$_POST['price'];
    $type = trim($_POST['type']);
    $user_id = $_SESSION['user_id'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Directory to store uploaded images
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
        }

        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Insert the new house into the database with the image path
            try {
                $stmt = $pdo->prepare("INSERT INTO houses (title, location, rooms, price, type, user_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $location, $rooms, $price, $type, $user_id, $imagePath]);

                $_SESSION['message'] = "House listed successfully!";
                header("Location: houses.php");
                exit();
            } catch (PDOException $e) {
                die("Error listing house: " . $e->getMessage());
            }
        } else {
            die("Error uploading image.");
        }
    } else {
        die("Please upload an image.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add House</title>
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
    <link rel="stylesheet" href="houses.css">
 
</head>
<body>
    <form action="add_house.php" method="POST" enctype="multipart/form-data">
        <h2>Add New House</h2>
        <input type="text" name="title" placeholder="Title" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="number" name="rooms" placeholder="Number of Rooms" required>
        <input type="number" name="price" placeholder="Price" required>
        <select name="type" required>
            <option value="Apartment">Apartment</option>
            <option value="Villa">Villa</option>
            <option value="Cottage">Cottage</option>
        </select>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Add House</button>
    </form>

</body>
</html>