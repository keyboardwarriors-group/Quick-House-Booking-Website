<?php
session_start();
require 'vendor/autoload.php'; // Include PHPMailer (install via Composer)
require 'db.php'; // Include your database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email using PHPMailer
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        $mail->isSMTP(); // Use SMTP
        $mail->Host       = 'smtp.gmail.com'; // SMTP server (e.g., Gmail)
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username   = 'kamarasaidu558@gmail.com.com'; // Your email address
        $mail->Password   = 'htcyuindjanvnznw'; // Your email password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('kamarasaidu558@gmail.com', 'Quick House'); // Sender
        $mail->addAddress($to); // Recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject; // Email subject
        $mail->Body    = $body; // Email body (HTML)

        $mail->send(); // Send the email
        return true; // Email sent successfully
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}"); // Log errors
        return false; // Email failed to send
    }
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the house ID is provided
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

// Check if the house is already booked
if ($house['booked']) {
    die("This house is already booked.");
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_date = date('Y-m-d H:i:s');

    // Insert the booking into the database with a "Pending" payment status
    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, house_id, booking_date, payment_status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $house_id, $booking_date]);

        // Get the booking ID
        $booking_id = $pdo->lastInsertId();

        // Update the house status to "booked"
        $stmt = $pdo->prepare("UPDATE houses SET booked = TRUE WHERE id = ?");
        $stmt->execute([$house_id]);

        // Fetch user details for the email notification
        $stmt = $pdo->prepare("SELECT email, first_name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Fetch owner details for the email notification
        $stmt = $pdo->prepare("SELECT email, first_name FROM users WHERE id = ?");
        $stmt->execute([$house['owner_id']]);
        $owner = $stmt->fetch();

        if ($user && $owner) {
            // Compose the email for the user
            $to_user = $user['email'];
            $subject_user = "Booking Confirmation for " . htmlspecialchars($house['title']);
            $body_user = "Hi " . htmlspecialchars($user['first_name']) . ",<br><br>" .
                         "Your booking for the house <strong>" . htmlspecialchars($house['title']) . "</strong> has been confirmed.<br>" .
                         "Location: " . htmlspecialchars($house['location']) . "<br>" .
                         "Price: $" . htmlspecialchars($house['price']) . "<br><br>" .
                         "Thank you for choosing Quick House!<br><br>" .
                         "Best regards,<br>The Quick House Team";

            // Compose the email for the owner
            $to_owner = $owner['email'];
            $subject_owner = "New Booking for " . htmlspecialchars($house['title']);
            $body_owner = "Hi " . htmlspecialchars($owner['first_name']) . ",<br><br>" .
                          "Your house <strong>" . htmlspecialchars($house['title']) . "</strong> has been booked by " . htmlspecialchars($user['first_name']) . ".<br>" .
                          "Location: " . htmlspecialchars($house['location']) . "<br>" .
                          "Price: $" . htmlspecialchars($house['price']) . "<br><br>" .
                          "Please log in to your account to view the booking details.<br><br>" .
                          "Best regards,<br>The Quick House Team";

            // Send the email to the user
            if (sendEmail($to_user, $subject_user, $body_user)) {
                $_SESSION['success_message'] = "Booking confirmed! A confirmation email has been sent to your inbox.";
            } else {
                $_SESSION['error'] = "Booking confirmed, but the confirmation email could not be sent.";
            }

            // Send the email to the owner
            if (!sendEmail($to_owner, $subject_owner, $body_owner)) {
                $_SESSION['error'] = "Booking confirmed, but the owner notification email could not be sent.";
            }
        } else {
            $_SESSION['error'] = "Booking confirmed, but user or owner details could not be retrieved.";
        }

        // Redirect to the payment page with the booking ID
        header("Location: payment.php?booking_id=$booking_id");
        exit();
    } catch (PDOException $e) {
        die("Error booking house: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Book House</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="header.css">
</head>
<body>
    <div class="container">
        <h1>Book House: <?= htmlspecialchars($house['title']) ?></h1>
        <p><strong>Location:</strong> <?= htmlspecialchars($house['location']) ?></p>
        <p><strong>Price:</strong> $<?= htmlspecialchars($house['price']) ?></p>
        <form action="book_house.php?id=<?= $house_id ?>" method="POST">
            <button type="submit" class="btn-book">Confirm Booking</button>
        </form>
    </div>
</body>
</html>