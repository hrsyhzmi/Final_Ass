<?php
$apiKey = '8af25ddaf967173cbd6f2cc9b8e08cb3';

if(isset($_GET['lat']) && isset($_GET['lon'])) {
    $lat = $_GET['lat'];
    $lon = $_GET['lon'];

    // Fetch weather data from OpenWeatherMap
    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$apiKey}&units=metric";
    $weatherData = json_decode(file_get_contents($weatherUrl), true);

    if ($weatherData['cod'] == 200) {
        // Extract temperature, weather condition, and city name
        $temperature = $weatherData['main']['temp'];
        $weather = $weatherData['weather'][0]['main'];
        $icon = $weatherData['weather'][0]['icon'];
        $cityName = $weatherData['name']; // City name from OpenWeatherMap

        // Weather icons/emojis mapping
        $emoticons = [
            'Clear' => '☀️',
            'Clouds' => '☁️',
            'Rain' => '🌧️',
            'Thunderstorm' => '⛈️',
            'Drizzle' => '🌦️',
            'Snow' => '❄️',
            'Mist' => '🌫️'
        ];
        
        $weatherEmoji = isset($emoticons[$weather]) ? $emoticons[$weather] : '❓';
    } else {
        $error = "Unable to retrieve weather data.";
    }
} else {
    $error = "Location access denied.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather</title>
    <link rel="stylesheet" href="css/weather.css">
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    window.location.href = `weather.php?lat=${position.coords.latitude}&lon=${position.coords.longitude}`;
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</head>
<body onload="getLocation()">
    <div class="container">
        <h1>Current Weather</h1>
        <?php if (isset($error)): ?>
            <p class="message error"><?= $error; ?></p>
        <?php else: ?>
            <div class="weather-info">
                <h2><?= $cityName; ?> (Lat: <?= $lat; ?>, Lon: <?= $lon; ?>)</h2>
                <h3><?= round($temperature); ?>°C <?= $weatherEmoji; ?></h3>
                <p>Condition: <?= $weather; ?></p>
                <img src="http://openweathermap.org/img/wn/<?= $icon; ?>@2x.png" alt="Weather Icon">
            </div>
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
