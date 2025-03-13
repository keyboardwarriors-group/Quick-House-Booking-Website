<?php
session_start();
require 'header.php'; // Include header with session_start()
require 'db.php'; // Include the database connection file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all houses (for admin)
$stmt = $pdo->prepare("SELECT * FROM houses");
$stmt->execute();
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all booking history
$stmt = $pdo->prepare("
    SELECT bookings.*, users.first_name, users.last_name, houses.title AS house_title
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN houses ON bookings.house_id = houses.id
");
$stmt->execute();
$booking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="houses.css">
    <link rel="stylesheet" href="footer.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <h1>Welcome to your Admin <span style="color: red; font-style: italic;">Dash Board</span>, <span class="admin-name"><?= htmlspecialchars($_SESSION['first_name']) ?></span></h1>
        <a href="add_house.php" class="btn-add-house">Add New House</a>
        <a href="admin_reset_password.php" class="btn-reset-password">Reset Password</a>
        <a href="delete_house.php" class="btn-delete-house">Delete House</a>
        <a href="edit_house.php" class="btn-edit-house">Edit House</a>

        <h2>All Houses</h2>
        <div class="houses-grid">
            <?php if (empty($houses)): ?>
                <p>No houses listed yet.</p>
            <?php else: ?>
                <?php foreach ($houses as $house): ?>
                    <div class="house-item">
                        <img src="<?= htmlspecialchars($house['image']) ?>" alt="House Image">
                        <h3><?= htmlspecialchars($house['title']) ?></h3>
                        <p><strong>Location:</strong> <?= htmlspecialchars($house['location']) ?></p>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($house['price']) ?></p>
                        <p><strong>Type:</strong> <?= htmlspecialchars($house['type']) ?></p>
                        <a href="edit_house.php?id=<?= $house['id'] ?>">Edit</a>
                        <a href="delete_house.php?id=<?= $house['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2>Booking History</h2>
        <?php if (empty($booking_history)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <table class="booking-history">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Booked By</th>
                        <th>Booking Date</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking_history as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['house_title']) ?></td>
                            <td><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></td>
                            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                            <td><?= htmlspecialchars($booking['payment_status']) ?></td>
                            <td>
                                <?php if ($booking['user_id'] === $_SESSION['user_id'] && $booking['payment_status'] === 'Pending'): ?>
                                    <a href="payment.php?booking_id=<?= $booking['id'] ?>" class="btn-pay">Pay Now</a>
                                <?php elseif ($booking['payment_status'] === 'Paid'): ?>
                                    <span class="paid">Paid</span>
                                <?php else: ?>
                                    <span class="no-action">No Action Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>