<?php
session_start();
include('db_connection.php'); // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Initialize message variable
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $activity_title = $_POST['title'];
    $activity_datetime = $_POST['datetime'];
    $activity_place = $_POST['place'];
    $activity_description = $_POST['description'];

    // Get user_id from session
    $user_id = $_SESSION['user_id'];

    // Insert data into the database
    $query = "INSERT INTO activities (date_time, title, place, description, user_id) 
              VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssssi", $activity_datetime, $activity_title, $activity_place, $activity_description, $user_id);
        
        if ($stmt->execute()) {
            $successMessage = "Activity added successfully!"; // Set success message
        } else {
            $successMessage = "Error: " . $stmt->error; // Set error message
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Activity</title>
    <link rel="stylesheet" href="css/add.css"> <!-- Link to external CSS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> <!-- Moment.js -->
</head>
<body>
    <div class="container">
        <h2>Add Upcoming Activity</h2>
        
        <!-- Display the success message below the title -->
        <?php if ($successMessage): ?>
            <h3 style="color: green;"><?php echo $successMessage; ?></h3>
        <?php endif; ?>

        <form action="add_activity.php" method="post" id="activity-form">
            <div>
                <label for="title">Activity Name:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div>
                <label for="datetime">Activity Date & Time:</label>
                <input type="datetime-local" id="datetime" name="datetime" required>
            </div>
            <div>
                <label for="place">Place:</label>
                <input type="text" id="place" name="place" required>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div>
                <button type="submit">Add Activity</button>
                <button type="button" onclick="window.location.href='upcoming_activities.php';">Back</button>
            </div>
        </form>
    </div>

    <script>
        // Save activity data to localStorage when the form is submitted
        document.getElementById('activity-form').addEventListener('submit', function(event) {
            event.preventDefault();

            // Collect form data
            const activity = {
                title: document.getElementById('title').value,
                datetime: document.getElementById('datetime').value,
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
        document.getElementById('datetime').addEventListener('change', function() {
            const dateTime = moment(this.value).format('YYYY-MM-DDTHH:mm');
            this.value = dateTime; // Update the datetime field with moment.js formatted date
        });
    </script>
</body>
</html>
