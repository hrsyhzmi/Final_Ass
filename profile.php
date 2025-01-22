<?php
include('db_connection.php');
session_start();

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Handle uploaded photo (from file system)
if (isset($_POST['upload-btn-file'])) {
    if (!empty($_FILES['profile_image']['name'])) {
        $imageName = $_FILES['profile_image']['name'];
        $imageTmp = $_FILES['profile_image']['tmp_name'];
        $imagePath = 'uploads/' . $imageName;
        
        // Move uploaded file to the desired folder
        if (move_uploaded_file($imageTmp, $imagePath)) {
            // Update profile picture in the database
            $sql = "UPDATE users SET profile_pic = '$imagePath' WHERE user_id = '$user_id'";
            if (mysqli_query($conn, $sql)) {
                header("Location: profile.php");
            } else {
                echo "Failed to update profile picture.";
            }
        } else {
            echo "Failed to upload file.";
        }
    }
}

// Handle uploaded photo (from the camera)
if (isset($_POST['upload-btn'])) {
    if (!empty($_POST['captured_image'])) {
        $imageData = $_POST['captured_image'];
        $imageData = explode(',', $imageData)[1]; // Remove base64 metadata
        $decodedData = base64_decode($imageData);
        $filePath = 'uploads/snapped_photo_' . $user_id . '.png';
        file_put_contents($filePath, $decodedData);

        // Update profile picture in the database
        $sql = "UPDATE users SET profile_pic = '$filePath' WHERE user_id = '$user_id'";
        if (mysqli_query($conn, $sql)) {
            header("Location: profile.php");
        } else {
            echo "Failed to update profile picture.";
        }
    }
}

// Handle user data update
if (isset($_POST['save-btn'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $height = mysqli_real_escape_string($conn, $_POST['height']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Update user details in the database
    $sql = "UPDATE users SET username = '$username', gender = '$gender', weight = '$weight', height = '$height', email = '$email' WHERE user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        echo "Profile updated successfully!";
    } else {
        echo "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<div class="container">
    <h1>Profile</h1>
    
    <!-- Profile Picture -->
    <div class="profile-picture">
        <img src="<?= $user['profile_pic'] ? $user['profile_pic'] : 'default.png' ?>" id="profile-img" alt="Profile Picture">
    </div>

   <!-- Upload Photo and Snap Photo -->
<div class="profile-buttons">
    <!-- Upload photo from file system -->
    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit" name="upload-btn-file">Upload Photo</button>
    </form>

    <!-- Snap photo using the camera -->
    <button id="snap-btn">Snap Photo</button>

    <!-- Upload Snapped Photo -->
    <form action="profile.php" method="POST">
        <input type="hidden" name="captured_image" id="captured-image">
        <button type="submit" name="upload-btn">Upload Snapped Photo</button>
    </form>
</div>


    <!-- Camera Section -->
    <div id="camera-container" style="display: none;">
        <video id="camera" autoplay playsinline></video>
        <canvas id="snapshot" style="display: none;"></canvas>
        <button id="capture-btn">Capture</button>
    </div>

    <!-- Editable User Data Form -->
    <h2>Account Information</h2>
    <form action="profile.php" method="POST">
        <table>
            <tr>
                <td><label for="name">Name:</label></td>
                <td><input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly></td>
            </tr>
            <tr>
                <td><label for="username">Username:</label></td>
                <td><input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required></td>
            </tr>
            <tr>
                <td><label for="email">Email:</label></td>
                <td><input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
            </tr>
            <tr>
                <td><label for="gender">Gender:</label></td>
                <td>
                    <select name="gender" id="gender" required>
                        <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="weight">Weight (kg):</label></td>
                <td><input type="number" id="weight" name="weight" value="<?= htmlspecialchars($user['weight']) ?>" required></td>
            </tr>
            <tr>
                <td><label for="height">Height (cm):</label></td>
                <td><input type="number" id="height" name="height" value="<?= htmlspecialchars($user['height']) ?>" required></td>
            </tr>
        </table>
        <button type="submit" name="save-btn">Save Changes</button>
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
    const video = document.getElementById('camera');
    const cameraContainer = document.getElementById('camera-container');
    const snapshotCanvas = document.getElementById('snapshot');
    const snapButton = document.getElementById('snap-btn');
    const captureButton = document.getElementById('capture-btn');
    const capturedImageInput = document.getElementById('captured-image');

    // Open camera on Snap Photo button click
    snapButton.addEventListener('click', () => {
        cameraContainer.style.display = 'block';
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(error => {
                alert("Unable to access camera: " + error.message);
            });
    });

    // Capture photo
    captureButton.addEventListener('click', () => {
        const context = snapshotCanvas.getContext('2d');
        snapshotCanvas.width = video.videoWidth;
        snapshotCanvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, snapshotCanvas.width, snapshotCanvas.height);

        // Stop camera stream
        const stream = video.srcObject;
        const tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;

        // Convert canvas to base64 and store in hidden input
        const imageData = snapshotCanvas.toDataURL('image/png');
        capturedImageInput.value = imageData;

        alert("Photo captured! Click 'Upload Photo' to save it.");
        cameraContainer.style.display = 'none';
    });
</script>
</body>
</html>
