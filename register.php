<?php
// We only need to connect to the database to fetch diseases
require 'php/connection/db_connection.php'; 

// Fetch both disease ID and name from the database
$diseases = [];
$sql = "SELECT id, name FROM disease ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $diseases[] = $row; // Store both ID and name
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medic Platform - Sign Up</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <header class="header">
        <h1>Medic Platform</h1>
        <p>Your guide to traditional medicine and well-being.</p>
    </header>

    <main class="container">
        <div class="form-box">
            <form id="registerForm" class="form" action="php/register_handler.php" method="POST">
                <h2>Sign Up</h2>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="tel" name="phone" placeholder="Phone Number">
                <input type="password" name="password" placeholder="Password" required>
                
                <div class="user-type">
                    <label><input type="radio" name="userType" value="patient" checked> Patient</label>
                    <label><input type="radio" name="userType" value="doctor"> Doctor</label>
                </div>
<div id="doctorSpeciality" class="hidden">
    <label for="speciality">Select your specialties:</label>
    <select name="speciality_ids[]" id="speciality" multiple>
        <option value="">Select one or more specialties</option>
        <?php foreach ($diseases as $disease): ?>
            <option value="<?php echo htmlspecialchars($disease['id']); ?>">
                <?php echo htmlspecialchars($disease['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                <button type="submit" class="btn">Sign Up</button>
                <p>Already have an account? <a href="index.html">Sign In</a></p>
            </form>
        </div>
    </main>
    <script src="js/form.js"></script>
</body>
</html>