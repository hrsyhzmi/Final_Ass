<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <h1>ExerciseTracking App</h1>
        <p>Know your BMI, search for suggested exercises, plan your activity, and check the weather—all in one app!</p>
        
        <!-- Image -->
        <img src="pic.png" alt="Dashboard Illustration" class="dashboard-img">
        
        <!-- Navigation Links -->
        <nav>
            <ul>
              
            <li><a href="index.html" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="bottom-nav">
    <a href="dashboard.php">Dashboard</a>
        <a href="bmi.php">BMI</a>
        <a href="exercise.php">Suggested Exercise</a>
        <a href="upcoming_activities.php">Upcoming Activity</a>
        <a href="weather.php">Weather</a>
        <a href="profile.php">Profile</a>
    </div>
</body>
</html>
