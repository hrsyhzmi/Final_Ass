<?php
session_start();
require('db_connection.php');

// Variables to store messages
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the new password and repeat password
    $newPassword = $_POST['new_password'];
    $repeatPassword = $_POST['repeat_password'];

    // Validate the new password and repeat password
    if ($newPassword !== $repeatPassword) {
        $message = "Passwords do not match!";
        $message_type = "error"; // Error message
    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "error"; // Error message
    } else {
        // Get the email from session
        $email = $_SESSION['reset_email'];

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database using MySQLi
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashedPassword, $email); // Bind parameters
        $stmt->execute();

        // Check if the query was successful
        if ($stmt->affected_rows > 0) {
            // Clear the reset code from the session
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_email']);

            // Set success message and redirect to login
            $message = "Password successfully updated!";
            $message_type = "success"; // Success message
            header("Location: login.php"); // Redirect to login page
            exit();
        } else {
            $message = "Failed to update password. Please try again.";
            $message_type = "error"; // Error message
        }

        $stmt->close(); // Close the statement
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset.css">
</head>
<body class="reset-page">
<div class="reset-container">
    <h2>Reset Your Password</h2>
    <form method="POST">
        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" required><br>

        <label for="repeat_password">Repeat New Password:</label>
        <input type="password" name="repeat_password" required><br>

        <!-- Message display -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <button type="submit">Reset Password</button>
    </form>
</div>
</body>
</html>
