<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = '0fp390MsLF//2IXBNkGPtw==51rt9IdJjJfqEluM';
    $baseUrl = "https://api.api-ninjas.com/v1/exercises";
    
    // Get user input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $muscle = isset($_POST['muscle']) ? trim($_POST['muscle']) : '';
    $difficulty = isset($_POST['difficulty']) ? trim($_POST['difficulty']) : '';

    // Build query parameters dynamically
    $queryParams = [];
    if (!empty($name)) $queryParams['name'] = $name;
    if (!empty($type)) $queryParams['type'] = $type;
    if (!empty($muscle)) $queryParams['muscle'] = $muscle;
    if (!empty($difficulty)) $queryParams['difficulty'] = $difficulty;
    $queryString = http_build_query($queryParams);

    // Full URL with query string
    $url = $baseUrl . (!empty($queryString) ? '?' . $queryString : '');

    // Set up API request
    $options = [
        "http" => [
            "header" => "X-Api-Key: $apiKey"
        ]
    ];
    $context = stream_context_create($options);

    try {
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $error = "Unable to fetch data. Please try again.";
        } else {
            $exercises = json_decode($response, true);

            if (empty($exercises)) {
                $error = "No exercises found for the specified parameters.";
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercise Page</title>
    <link rel="stylesheet" href="css/exercise.css">
</head>
<body>
    <div class="container">
        <h1>Suggested Exercises</h1>
        <h4> Filling out everything is optional, not all fields are required, you can skip some of the fields<h4>
        <form method="POST" action="exercise.php">
            <label for="name">Exercise Name (optional):</label>
            <input type="text" name="name" id="name">
            
            <label for="type">Type (optional):</label>
            <select name="type" id="type">
                <option value="">--Select--</option>
                <option value="cardio">Cardio</option>
                <option value="olympic_weightlifting">Olympic Weightlifting</option>
                <option value="plyometrics">Plyometrics</option>
                <option value="powerlifting">Powerlifting</option>
                <option value="strength">Strength</option>
                <option value="stretching">Stretching</option>
                <option value="strongman">Strongman</option>
            </select>
            
            <label for="muscle">Muscle Group (optional):</label>
            <select name="muscle" id="muscle">
                <option value="">--Select--</option>
                <option value="abdominals">Abdominals</option>
                <option value="abductors">Abductors</option>
                <option value="adductors">Adductors</option>
                <option value="biceps">Biceps</option>
                <option value="calves">Calves</option>
                <option value="chest">Chest</option>
                <option value="forearms">Forearms</option>
                <option value="glutes">Glutes</option>
                <option value="hamstrings">Hamstrings</option>
                <option value="lats">Lats</option>
                <option value="lower_back">Lower Back</option>
                <option value="middle_back">Middle Back</option>
                <option value="neck">Neck</option>
                <option value="quadriceps">Quadriceps</option>
                <option value="traps">Traps</option>
                <option value="triceps">Triceps</option>
            </select>
            
            <label for="difficulty">Difficulty (optional):</label>
            <select name="difficulty" id="difficulty">
                <option value="">--Select--</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="expert">Expert</option>
            </select>

            <button type="submit">Search Exercises</button>
        </form>

        <?php if (isset($exercises) && !empty($exercises)): ?>
            <h2>Exercises:</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Equipment</th>
                        <th>Difficulty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exercises as $exercise): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exercise['name']); ?></td>
                            <td><?php echo isset($exercise['category']) ? htmlspecialchars($exercise['category']) : 'Not specified'; ?></td>
                            <td><?php echo isset($exercise['equipment']) ? htmlspecialchars($exercise['equipment']) : 'None'; ?></td>
                            <td><?php echo isset($exercise['difficulty']) ? htmlspecialchars($exercise['difficulty']) : 'Not specified'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

      
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
