<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require 'php/connection/db_connection.php'; // Include the database connection file

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Fetch the list of diseases from the database
$diseases = [];
// MODIFICATION: Add 'image_url' field to the SQL query
$sql = "SELECT id, name, description, image_url FROM disease ORDER BY name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $diseases[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health by Plants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/Dashboard.css">
</head>
<body>
    <div class="app-container">
        <?php require 'includes/navbar.php'; ?>

        <main class="content">
            <header class="header">
                <h1>Health by Plants</h1>
                <p>Your guide to traditional medicine and well-being.</p>
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                </div>
            </header>

            <div class="container">
                <div class="disease-cards-container">
                    <?php if (empty($diseases)): ?>
                        <p>No diseases found.</p>
                    <?php else: ?>
                        <?php foreach ($diseases as $disease): ?>
                            <a href="plants.php?disease_id=<?php echo htmlspecialchars($disease['id']); ?>" class="card">
                                <?php if (!empty($disease['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($disease['image_url']); ?>" alt="<?php echo htmlspecialchars($disease['name']); ?>" class="disease-image">
                                <?php endif; ?>
                                <div class="card-content">
                                    <h3><?php echo htmlspecialchars($disease['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($disease['description']); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/navbar.js"></script>
</body>
</html>