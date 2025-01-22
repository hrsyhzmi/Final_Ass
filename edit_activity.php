<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$activity_id = $_GET['id'];

// Fetch the current activity details
$query = "SELECT * FROM activities WHERE activity_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $activity_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: upcoming_activities.php");
    exit;
}

$activity = $result->fetch_assoc();

// Check if the logged-in user owns the activity
if ($activity['user_id'] != $_SESSION['user_id']) {
    header("Location: upcoming_activities.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_time = $_POST['date_time'];
    $title = $_POST['title'];
    $place = $_POST['place'];
    $description = $_POST['description'];

    // Update the activity details
    $query = "UPDATE activities SET date_time = ?, title = ?, place = ?, description = ? WHERE activity_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $date_time, $title, $place, $description, $activity_id);
    $stmt->execute();

    header("Location: upcoming_activities.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Activity</title>
    <link rel="stylesheet" href="css/edit.css"> <!-- Link to external CSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> <!-- Moment.js -->
</head>
<body>
<div class="container">
        <h2>Edit Upcoming Activity</h2>

        <!-- Form to edit activity -->
        <form method="POST" action="edit_activity.php?id=<?php echo $activity_id; ?>" id="edit-activity-form">
            <div>
                <label for="date_time">Date and Time:</label>
                <input type="datetime-local" name="date_time" id="date_time" value="<?php echo $activity['date_time']; ?>" required>
            </div>

            <div>
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($activity['title']); ?>" required>
            </div>

            <div>
                <label for="place">Place:</label>
                <input type="text" name="place" id="place" value="<?php echo htmlspecialchars($activity['place']); ?>" required>
            </div>

            <div>
                <label for="description">Description:</label>
                <textarea name="description" id="description" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
            </div>

            <!-- Button Container -->
            <div class="button-container">
                <button type="submit">Update Activity</button>
                <button type="button" class="back-button" onclick="window.location.href='upcoming_activities.php';">Back to Upcoming Activities</button>
            </div>
        </form>
    </div>

    <script>
        // Save activity data to localStorage when the form is submitted
        document.getElementById('edit-activity-form').addEventListener('submit', function(event) {
            event.preventDefault();

            // Collect form data
            const activity = {
                date_time: document.getElementById('date_time').value,
                title: document.getElementById('title').value,
                place: document.getElementById('place').value,
                description: document.getElementById('description').value,
            };

            // Save to localStorage
            const activities = JSON.parse(localStorage.getItem('activities')) || [];
            activities.push(activity);
            localStorage.setItem('activities', JSON.stringify(activities));

            // Submit the form
            this.submit();
        });

        // Ensure the date is formatted with moment.js before submitting
        document.getElementById('date_time').addEventListener('change', function() {
            const dateTime = moment(this.value).format('YYYY-MM-DDTHH:mm');
            this.value = dateTime; // Update the datetime field with moment.js formatted date
        });
    </script>
</body>
</html>
