<?php
include('db_connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

// Get current date
$current_date = date('Y-m-d H:i:s');

// Query to fetch upcoming activities (those with a date_time greater than the current date)
$query = "SELECT * FROM activities WHERE user_id = ? AND date_time > ? ORDER BY date_time ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $_SESSION['user_id'], $current_date);
$stmt->execute();
$result = $stmt->get_result();

// Automatically delete past activities
$delete_query = "DELETE FROM activities WHERE user_id = ? AND date_time < ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param('is', $_SESSION['user_id'], $current_date);
$delete_stmt->execute();

// Fetch all activities to display
$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Activities</title>
    <link rel="stylesheet" href="css/upcoming.css"> <!-- Link to the separate CSS file -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Your Upcoming Activities</h1>

    <!-- Search input to filter activities -->
    <input type="text" id="searchInput" placeholder="Search by name...">
    <button onclick="searchActivity()">Search</button>
    <button onclick="displayAllActivities()">Display All</button>


        <table id="activities-table">
            <tr>
                <th>NO</th> <!-- Renamed ID to NO -->
                <th>Date/Time</th>
                <th>Name</th>
                <th>Place</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </table>

        <!-- "Add New Activity" button -->
        <form action="add_activity.php">
            <button type="submit" class="add-activity-button">Add New Activity</button>
        </form>
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

    <script>
        // Save activities to localStorage
        function saveActivitiesToLocalStorage(activities) {
            localStorage.setItem('activities', JSON.stringify(activities));
        }

        // Fetch activities from localStorage
        function getActivitiesFromLocalStorage() {
            const activities = localStorage.getItem('activities');
            return activities ? JSON.parse(activities) : [];
        }

        // Display activities
        function displayActivities(activities) {
            const table = document.getElementById('activities-table');
            activities.forEach((activity, index) => {
                const activityDate = moment(activity.date_time);
                if (activityDate.isAfter(moment())) { // Check if the activity is in the future
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${activityDate.format('YYYY-MM-DD HH:mm')}</td>
                        <td>${activity.title}</td>
                        <td>${activity.place}</td>
                        <td>${activity.description}</td>
                        <td>
                            <a href="edit_activity.php?id=${activity.activity_id}">Edit</a> | 
                            <a href="delete_activity.php?id=${activity.activity_id}" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</a>
                        </td>
                    `;
                }
            });
        }

        function searchActivity() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const activities = getActivitiesFromLocalStorage();
    
    // Filter activities based on the search query
    const filteredActivities = activities.filter(activity => activity.title.toLowerCase().includes(query));
    
    // Clear the table before displaying filtered results
    const table = document.getElementById('activities-table');
    table.innerHTML = `
        <tr>
            <th>NO</th>
            <th>Date/Time</th>
            <th>Name</th>
            <th>Place</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    `; // Reset the table header

    // Display the filtered activities
    displayActivities(filteredActivities);
}

function displayAllActivities() {
    // Clear the table before displaying all activities
    const table = document.getElementById('activities-table');
    table.innerHTML = `
        <tr>
            <th>NO</th>
            <th>Date/Time</th>
            <th>Name</th>
            <th>Place</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    `; // Reset the table header

    // Get all activities from localStorage
    const activities = getActivitiesFromLocalStorage();

    // Display all activities
    displayActivities(activities);
}


        // Store the fetched activities into localStorage for future reference
        const activities = <?php echo json_encode($activities); ?>;
        saveActivitiesToLocalStorage(activities);

        // Display the activities on page load
        const storedActivities = getActivitiesFromLocalStorage();
        displayActivities(storedActivities);
    </script>
</body>
</html>
