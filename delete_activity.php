<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$activity_id = $_GET['id'];

// Make sure the user owns the activity
$query = "SELECT user_id FROM activities WHERE activity_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $activity_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: upcoming_activities.php");
    exit;
}

$row = $result->fetch_assoc();

if ($row['user_id'] != $_SESSION['user_id']) {
    header("Location: upcoming_activities.php");
    exit;
}

// Delete the activity
$query = "DELETE FROM activities WHERE activity_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $activity_id);
$stmt->execute();

header("Location: upcoming_activities.php");
exit;
?>
