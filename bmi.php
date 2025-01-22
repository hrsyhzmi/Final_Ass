<?php
session_start();
include('db_connection.php');

// Set the time zone to match your local machine's time zone
date_default_timezone_set('Asia/Kuala_Lumpur');  // Set to your desired time zone

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['calculate_bmi'])) {
    $user_id = $_SESSION['user_id'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $bmi = round($weight / ($height * $height), 2);
    
    // Determine BMI status
    if ($bmi < 18.5) {
        $status = "Underweight";
    } elseif ($bmi >= 18.5 && $bmi < 24.9) {
        $status = "Normal weight";
    } elseif ($bmi >= 25 && $bmi < 29.9) {
        $status = "Overweight";
    } else {
        $status = "Obese";
    }

    $date_time = date("Y-m-d H:i:s");

    // Insert data into the database
    $sql = "INSERT INTO bmi_records (user_id, date_time, weight, height, bmi, status) 
            VALUES ('$user_id', '$date_time', '$weight', '$height', '$bmi', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "Record added successfully";
    } else {
        $message = "Error: " . $conn->error;
    }

    // Set session to show only the latest record
    $_SESSION['show_all'] = false;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM bmi_records WHERE id='$delete_id' AND user_id=" . $_SESSION['user_id']);
    header("Location: bmi.php");
    exit();
}

// Handle display all records
if (isset($_POST['display_all'])) {
    $_SESSION['show_all'] = true;
}

// Determine whether to show all records or just the latest one
$show_all_records = isset($_SESSION['show_all']) && $_SESSION['show_all'];

if ($show_all_records) {
    $result = $conn->query("SELECT * FROM bmi_records WHERE user_id = " . $_SESSION['user_id']);
} else {
    $result = $conn->query("SELECT * FROM bmi_records WHERE user_id = " . $_SESSION['user_id'] . " ORDER BY id DESC LIMIT 1");
}

$records = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
    <link rel="stylesheet" href="css/bmi.css">
</head>
<body>

<div class="container">
    <h1>BMI Calculator</h1>

    <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>

    <form method="POST" action="bmi.php">
        <input type="number" name="weight" id="weight" placeholder="Enter Weight (kg)" required>
        <input type="number" name="height" id="height" placeholder="Enter Height (m)" step="0.01" required>
        <button type="submit" name="calculate_bmi">Calculate BMI</button>
    </form>

    <form method="POST" action="bmi.php">
        <button type="submit" name="display_all">Display All Records</button>
    </form>

    <h2>Results</h2>

    <?php if ($show_all_records): ?>
        <h3>Previous Records</h3>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Weight (kg)</th>
                <th>Height (m)</th>
                <th>BMI</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($records)) : ?>
                <?php foreach ($records as $record) : ?>
                    <tr>
                        <td><?php echo $record['date_time']; ?></td>
                        <td><?php echo $record['weight']; ?></td>
                        <td><?php echo $record['height']; ?></td>
                        <td><?php echo $record['bmi']; ?></td>
                        <td><?php echo $record['status']; ?></td>
                        <td><a href="bmi.php?delete_id=<?php echo $record['id']; ?>" class="delete-btn">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bottom Navigation Bar -->
<div class="bottom-nav">
    <a href="dashboard.php">Dashbaord</a>
        <a href="bmi.php">BMI</a>
        <a href="exercise.php">Suggested Exercise</a>
        <a href="upcoming_activities.php">Upcoming Activity</a>
        <a href="weather.php">Weather</a>
        <a href="profile.php">Profile</a>
    </div>

<script>
    // Check if there is a BMI record in localStorage and display it
    window.onload = function() {
        let bmiRecord = localStorage.getItem('bmi_record');
        if (bmiRecord) {
            bmiRecord = JSON.parse(bmiRecord);
            document.getElementById('bmi_result').innerHTML = `
                Latest BMI: ${bmiRecord.bmi} (${bmiRecord.status})
                Weight: ${bmiRecord.weight} kg, Height: ${bmiRecord.height} m
            `;
        }
    };

    // Save BMI record to localStorage after form submission
    function saveToLocalStorage(bmi, weight, height, status) {
        const bmiRecord = {
            bmi: bmi,
            weight: weight,
            height: height,
            status: status
        };
        localStorage.setItem('bmi_record', JSON.stringify(bmiRecord));
    }

    // Listen to form submission and save the latest record to localStorage
    document.querySelector('form').addEventListener('submit', function(event) {
        const weight = parseFloat(document.getElementById('weight').value);
        const height = parseFloat(document.getElementById('height').value);
        const bmi = weight / (height * height);
        let status = '';

        if (bmi < 18.5) {
            status = "Underweight";
        } else if (bmi >= 18.5 && bmi < 24.9) {
            status = "Normal weight";
        } else if (bmi >= 25 && bmi < 29.9) {
            status = "Overweight";
        } else {
            status = "Obese";
        }

        saveToLocalStorage(bmi, weight, height, status);
    });
</script>

</body>
</html>
